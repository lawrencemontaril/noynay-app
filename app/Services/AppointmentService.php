<?php

namespace App\Services;

use App\Models\Setting;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentRejected;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Enums\{AppointmentStatus, AppointmentType, LaboratoryResultStatus, LaboratoryResultType};
use App\Models\{Appointment, LaboratoryResult, User};
use App\Notifications\{AppointmentApproved, AppointmentCompleted, AppointmentCreated, AppointmentRescheduled, ConsultationRequest, LaboratoryResultRequest};
use Carbon\Carbon;

class AppointmentService
{
    protected const DEFAULT_MAX_APPOINTMENTS_PER_SLOT = 1;

    protected int $maxAppointmentsPerSlot;

    public function __construct()
    {
        $setting = Setting::select('max_appointments_per_slot')->first();

        $this->maxAppointmentsPerSlot = $setting->max_appointments_per_slot ?? self::DEFAULT_MAX_APPOINTMENTS_PER_SLOT;
    }

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
                'scheduled_at' => 'The selected date and time has reached the maximum number of appointments ('.$this->maxAppointmentsPerSlot.').',
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
                    'scheduled_at' => 'The selected date and time has reached the maximum number of appointments ('.$this->maxAppointmentsPerSlot.').',
                ]);
            }

            $appointment->scheduled_at = $scheduledAt;
        }

        $appointment->update([
            'complaints' => $data['complaints'] ?? $appointment->complaints,
            'type' => $data['type'] ?? $appointment->type,
            'status' => $data['status'] ?? $appointment->status,
        ]);

        return $appointment;
    }

    public function approve(Appointment $appointment)
    {
        $appointment->update(['status' => AppointmentStatus::APPROVED]);

        if ($appointment->type->isLab()) {
            $appointment->laboratoryResults()->create([
                'description' => null,
                'type' => LaboratoryResultType::from($appointment->type->value),
                'status' => LaboratoryResultStatus::PENDING,
                'results_file_path' => null,
            ]);
        }

        $this->notifyStaffsOfAppointmentApprovals($appointment);
        $this->notifyPatientOfAppointmentApprovals($appointment);
    }

    public function reject(Appointment $appointment)
    {
        $appointment->update(['status' => AppointmentStatus::REJECTED]);

        $this->notifyPatientOfAppointmentRejection($appointment);
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
                'scheduled_at' => 'The selected date and time has reached the maximum number of appointments ('.$this->maxAppointmentsPerSlot.').',
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
     * Cancel an appointment (triggered by user).
     */
    public function cancel(Appointment $appointment)
    {
        $appointment->update(['status' => AppointmentStatus::CANCELLED]);

        $this->notifyAdminsOfAppointmentCancellations($appointment);
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
            ->whereIn('status', [
                AppointmentStatus::APPROVED,
                AppointmentStatus::COMPLETED,
                AppointmentStatus::PENDING,
            ])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->count();

        return $count >= $this->maxAppointmentsPerSlot;
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
            ->whereNotIn('status', [
                AppointmentStatus::REJECTED,
                AppointmentStatus::COMPLETED,
                AppointmentStatus::CANCELLED,
            ])
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
     * Notify admins of appointment cancellations.
     */
    protected function notifyAdminsOfAppointmentCancellations(Appointment $appointment)
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new AppointmentCancelled($appointment));
    }

    /**
     * Notify laboratory staff and doctor of appointment approvals.
     */
    protected function notifyStaffsOfAppointmentApprovals(Appointment $appointment)
    {
        if ($appointment->type->isLab()) {
            $labStaffs = User::role('laboratory_staff')->get();

            Notification::send($labStaffs, new LaboratoryResultRequest($appointment));
        } else {
            $doctors = User::role('doctor')->get();

            Notification::send($doctors, new ConsultationRequest($appointment));
        }
    }

    /**
     * Notify patient of appointment approvals.
     */
    protected function notifyPatientOfAppointmentApprovals(Appointment $appointment)
    {
        $appointment->patient->user?->notify(new AppointmentApproved($appointment));
    }

    /**
     * Notify patient of appointment rejections.
     */
    protected function notifyPatientOfAppointmentRejection(Appointment $appointment)
    {
        $appointment->patient->user?->notify(new AppointmentRejected($appointment));
    }
}
