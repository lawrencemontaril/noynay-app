<?php

namespace App\Http\Controllers\Admin;

use App\Filters\AppointmentFilter;
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

        $appointments = (new AppointmentFilter($request))
            ->apply(Appointment::with(['patient']))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/appointments/AppointmentsIndex', [
            'appointments' => $appointments->toResourceCollection(),
            'filters' => $request->only(['q', 'status', 'type'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request, AppointmentService $appointmentService)
    {
        $appointmentService->create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        $appointment->load(['patient.user', 'doctor']);

        return Inertia::render('admin/appointments/AppointmentsEdit', [
            'appointment' => $appointment->toResource()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment, AppointmentService $appointmentService)
    {
        $appointmentService->update($appointment->id, $request->validated());

        return redirect()
            ->back()
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
            ->back()
            ->with('success', 'Appointment deleted successfully');
    }
}
