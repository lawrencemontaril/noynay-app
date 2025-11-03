<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Patient;
use Inertia\Inertia;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Patient::class);

        $patients = Patient::with(['user'])
            ->search(request('q'))
            ->when(request()->filled('gender') && request('gender') !== 'all', fn ($q) =>
                $q->where('gender', request('gender'))
            )
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('patients.id', $request->input('id'))
            )
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/patients/PatientsIndex', [
            'patients' => $patients->toResourceCollection(),
            'filters' => $request->only(['q', 'gender', 'is_employed'])
        ]);
    }

    /**
     * API: Patient search endpoint.
     */
    public function search()
    {
        Gate::authorize('viewAny', Patient::class);

        $patients = Patient::search(request('q'))
            ->limit(30)
            ->get();

        return $patients->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request)
    {
        Patient::create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        $patient->load(['user']);

        return Inertia::render('admin/patients/PatientsShow', [
            'patient' => $patient->toResource(),
        ]);
    }

    /**
     * Display the patient's appointment history.
     */
    public function appointments(Patient $patient)
    {
        Gate::authorize('viewAppointments', $patient);

        $appointments = $patient->appointments()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/patients/PatientsAppointments', [
            'patient' => $patient->toResource(),
            'appointments' => $appointments->toResourceCollection(),
        ]);
    }

    /**
     * Display the patient's appointment history.
     */
    public function appointmentDetail(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewAppointments', $patient);

        return Inertia::render('admin/patients/PatientsAppointmentDetail', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
        ]);
    }

    /**
     * Display the patient's appointment history.
     */
    public function consultations(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewConsultations', $patient);

        $appointment->load(['invoice']);

        return Inertia::render('admin/patients/PatientsConsultations', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'consultations' => $appointment?->consultations?->toResourceCollection(),
        ]);
    }

    public function laboratoryResults(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewLaboratoryResults', $patient);

        $appointment->load(['invoice']);

        return Inertia::render('admin/patients/PatientsLaboratoryResults', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'laboratory_results' => $appointment?->laboratoryResults?->toResourceCollection(),
        ]);
    }

    /**
     * Display the patient's invoice history.
     */
    public function invoice(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewInvoices', $patient);

        return Inertia::render('admin/patients/PatientsInvoice', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'invoice' => $appointment?->invoice?->toResource(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        $patient->delete();

        return back()
            ->with('success', 'Patient deleted successfully.');
    }
}
