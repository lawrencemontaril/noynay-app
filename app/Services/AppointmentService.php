<?php

namespace App\Services;

use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Enums\{AppointmentStatus, AppointmentType};
use App\Models\{Appointment, LaboratoryResult, User};
use App\Notifications\{AppointmentApproved, AppointmentCompleted, AppointmentCreated, AppointmentRescheduled, ConsultationRequest, LaboratoryResultRequest};
use Carbon\Carbon;

class AppointmentService
{
    const MAX_APPOINTMENTS_PER_SLOT = 5;

    /**
     * Create a new appointment with multiple services.
     */
    public function create(array $data): Appointment
    {
        $scheduledAt = Carbon::parse($data['scheduled_at']);

        // Check if patient already has an unsettled appointment
        if ($this->hasUnsettledAppointment($data['patient_id'] ?? null)) {
            throw ValidationException::withMessages([
                'patient_id' => 'This patient already has an unsettled appointment.',
            ]);
        }

        // Check if slot is already full
        if ($this->isFull($scheduledAt)) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'The selected date and time has reached the maximum number of appointments ('.static::MAX_APPOINTMENTS_PER_SLOT.').',
            ]);
        }

        // Create the main appointment
        $appointment = Appointment::create([
            'patient_id' => $data['patient_id'] ?? null,
            'complaints' => $data['complaints'] ?? null,
            'type' => $data['type'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'scheduled_at' => $scheduledAt,
        ]);

        $this->notifyAdminsOfAppointmentCreation($appointment);

        return $appointment;
    }

    /**
     * Update an appointment
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        if (isset($data['scheduled_at'])) {
            $scheduledAt = Carbon::parse($data['scheduled_at']);

            // Ensure new datetime isn’t already full (excluding this one)
            if ($this->isFull($scheduledAt, $appointment->id)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'The selected date and time has reached the maximum number of appointments ('.static::MAX_APPOINTMENTS_PER_SLOT.').',
                ]);
            }

            $appointment->scheduled_at = $scheduledAt;
        }

        $appointment->update([
            'complaints' => $data['complaints'] ?? $appointment->complaints,
            'type' => $data['type'] ?? $appointment->type,
            'status' => $data['status'] ?? $appointment->status,
        ]);

        if ($appointment->status === AppointmentStatus::APPROVED && in_array($appointment->type ?? '', [AppointmentType::PREGNANCY_TEST, AppointmentType::PAPSMEAR, AppointmentType::CBC, AppointmentType::URINALYSIS, AppointmentType::FECALYSIS])) {
            LaboratoryResult::create([
                'appointment_id' => $appointment->id,
                'description' => null,
                'type' => $appointment->type->value,
                'status' => 'pending',
                'results_file_path' => null,
            ]);
        }

        $this->notifyStaffsOfAppointmentApprovals($appointment);
        $this->notifyPatientOfAppointmentCompletion($appointment);
        $this->notifyPatientOfAppointmentApprovals($appointment);

        return $appointment;
    }

    /**
     * Reschedule an appointment (triggered by user).
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        $scheduledAt = Carbon::parse($data['scheduled_at']);

        // Ensure new datetime isn’t already full (excluding this one)
        if ($this->isFull($scheduledAt, $appointment->id)) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'The selected date and time has reached the maximum number of appointments ('.static::MAX_APPOINTMENTS_PER_SLOT.').',
            ]);
        }

        $appointment->update([
            'complaints' => $data['complaints'] ?? $appointment->complaints,
            'scheduled_at' => $scheduledAt
        ]);

        $this->notifyAdminsOfAppointmentReschedules($appointment);

        return $appointment;
    }

    /**
     * Cancel an appointment (triggered by user)
     */
    public function cancel(Appointment $appointment): bool
    {
        return $appointment->update(['status' => 'cancelled']);
    }

    /*
    |--------------------------------------------------------------------------
    | Checks and conditions
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a datetime slot already has 5 or more appointments.
     */
    protected function isFull(Carbon $scheduledAt, ?int $ignoreId = null): bool
    {
        $count = Appointment::where('scheduled_at', $scheduledAt)
            ->whereIn('status', ['approved', 'completed', 'pending'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->count();

        return $count >= self::MAX_APPOINTMENTS_PER_SLOT;
    }

    /**
     * Check if patient has an unsettled appointment.
     */
    public function hasUnsettledAppointment(?int $patientId): bool
    {
        if (! $patientId) {
            return false;
        }

        return Appointment::where('patient_id', $patientId)
            ->whereNotIn('status', ['rejected', 'completed', 'cancelled'])
            ->exists();
    }

    /**
     * Check if the appointment has any consultation or laboratory result
     */
    public function hasBeenServiced(Appointment $appointment): bool
    {
        return $appointment->consultations()->exists() ||
            $appointment->laboratoryResults()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    /**
     * Notify admins of appointment creation.
     */
    protected function notifyAdminsOfAppointmentCreation(Appointment $appointment)
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new AppointmentCreated($appointment));
    }

    /**
     * Notify admins of appointment reschedules.
     */
    protected function notifyAdminsOfAppointmentReschedules(Appointment $appointment)
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new AppointmentRescheduled($appointment));
    }

    /**
     * Notify cashiers of appointment approvals.
     */
    protected function notifyStaffsOfAppointmentApprovals(Appointment $appointment)
    {
        if ($appointment->wasChanged('status') && $appointment->status === 'approved') {
            // $cashiers = User::role('cashier')->get();

            // Notification::send($cashiers, new AppointmentApproved($appointment));

            $labTypes = ['pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis'];

            if (in_array($appointment->type, $labTypes)) {
                // Send to laboratory staff
                $labStaff = User::role('laboratory_staff')->get();
                foreach ($labStaff as $user) {
                    $user->notify(new LaboratoryResultRequest($appointment));
                }
            } else {
                // Send to doctors
                $doctors = User::role('doctor')->get();
                foreach ($doctors as $user) {
                    $user->notify(new ConsultationRequest($appointment));
                }
            }
        }
    }

    /**
     * Notify patient of appointment completion.
     */
    protected function notifyPatientOfAppointmentCompletion(Appointment $appointment)
    {
        if ($appointment->wasChanged('status') && $appointment->status === 'completed') {
            Notification::send($appointment->patient?->user, new AppointmentCompleted($appointment));
        }
    }

    /**
     * Notify patient of appointment approvals.
     */
    protected function notifyPatientOfAppointmentApprovals(Appointment $appointment)
    {
        if ($appointment->wasChanged('status') && $appointment->status === 'approved') {
            Notification::send($appointment->patient?->user, new AppointmentApproved($appointment));
        }
    }
}
