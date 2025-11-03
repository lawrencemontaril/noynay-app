<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Appointment;
use Inertia\Inertia;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Appointment::class);

        $appointments = auth()->user()->patient
            ->appointments()
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) =>
                $q->where('status', $request->input('status'))
            )
            ->when($request->filled('service') && $request->input('service') !== 'all', fn ($q) =>
                $q->where('service', $request->input('service'))
            )
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('appointments.id', $request->input('id'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('user/appointments/AppointmentsIndex', [
            'appointments' => $appointments->toResourceCollection(),
            'filters' => $request->only(['status', 'service'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return Inertia::render('user/appointments/AppointmentsShow', [
            'appointment' => $appointment->toResource(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(AppointmentService $appointmentService)
    {
        Gate::authorize('create', Appointment::class);

        if ($appointmentService->hasUnsettledAppointment(auth()->user()->patient->id)) {
            return redirect()
                ->route('appointments.index')
                ->with('error', 'You cannot create new appointment. You have an unsettled appointment.');
        }

        return Inertia::render('user/appointments/AppointmentsCreate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request, AppointmentService $appointmentService)
    {
        $appointmentService->create($request->validated());

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment, AppointmentService $appointmentService)
    {
        $appointmentService->update($appointment->id, $request->validated());

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        Gate::authorize('delete', $appointment);

        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment deleted successfully');
    }
}
