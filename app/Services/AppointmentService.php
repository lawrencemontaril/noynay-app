<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\LaboratoryResult;
use App\Models\User;
use App\Notifications\AppointmentApproved;
use App\Notifications\AppointmentCreated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

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
     * Get an appointment by ID (with services).
     */
    public function find(int $id): Appointment
    {
        return Appointment::with(['patient'])->findOrFail($id);
    }

    /**
     * List all appointments (with patients and services).
     */
    public function all(): Collection
    {
        return Appointment::with(['patient'])->latest()->get();
    }

    /**
     * Update an appointment (including its services).
     */
    public function update(int $id, array $data): Appointment
    {
        $appointment = $this->find($id);

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

        if ($appointment->status === 'approved' && in_array($appointment->type ?? '', ['pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis'])) {
            LaboratoryResult::create([
                'appointment_id' => $appointment->id,
                'description' => null,
                'type' => $appointment->type,
                'status' => 'pending',
                'results_file_path' => null,
            ]);
        }

        $this->notifyCashiersOfAppointmentApprovals($appointment);
        $this->notifyPatientOfAppointmentCompletion($appointment);

        return $appointment;
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(int $id): bool
    {
        $appointment = $this->find($id);

        return $appointment->update(['status' => 'cancelled']);
    }

    /**
     * Delete an appointment and its services.
     */
    public function delete(int $id): bool
    {
        $appointment = $this->find($id);

        return $appointment->delete();
    }

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
     * Notify admins of appointment creation.
     */
    protected function notifyAdminsOfAppointmentCreation(Appointment $appointment)
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new AppointmentCreated($appointment));
    }

    /**
     * Notify cashiers of appointment approvals.
     */
    protected function notifyCashiersOfAppointmentApprovals(Appointment $appointment)
    {
        if ($appointment->wasChanged('status') && $appointment->status === 'approved') {
            $cashiers = User::role('cashier')->get();

            Notification::send($cashiers, new AppointmentApproved($appointment));
        }
    }

    /**
     * Notify patient of appointment completion.
     */
    protected function notifyPatientOfAppointmentCompletion(Appointment $appointment)
    {
        if ($appointment->wasChanged('status') && $appointment->status === 'completed') {
            Notification::send($appointment->patient?->user, new AppointmentApproved($appointment));
        }
    }
}
