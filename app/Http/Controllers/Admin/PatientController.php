<?php

namespace App\Http\Controllers\Admin;

use App\Models\Procedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use App\Models\{Appointment, Consultation, Invoice, LaboratoryResult, Patient};
use App\Filters\PatientFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Requests\{StorePatientRequest, UpdatePatientRequest};
use Inertia\Inertia;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Patient::class);

        $patients = (new PatientFilter($request))
            ->apply(Patient::with(['user']))
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/patients/PatientsIndex', [
            'patients' => $patients->toResourceCollection(),
            'filters' => $request->only(['q', 'gender', 'is_employed', 'archived'])
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
            'activities' => Inertia::optional(
                fn () => ActivityResource::collection($patient->activities()->with('causer')->latest()->get())
            )
        ]);
    }

    /**
     * Display the patient's appointment history.
     */
    public function appointments(Patient $patient)
    {
        Gate::authorize('viewAny', Appointment::class);

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
     * Display the patient's appointment detail.
     */
    public function appointmentDetail(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return Inertia::render('admin/patients/PatientsAppointmentDetail', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'activities' => Inertia::optional(
                fn () => ActivityResource::collection($appointment->activities()->with('causer')->latest()->get())
            )
        ]);
    }

    /**
     * Display the patient's appointment history.
     */
    public function consultations(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewAny', Consultation::class);

        $appointment->load(['invoice']);

        return Inertia::render('admin/patients/PatientsConsultations', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'consultations' => $appointment?->consultations?->toResourceCollection(),
        ]);
    }

    /**
     * Display the patient's consultation detail.
     */
    public function consultationDetail(Patient $patient, Appointment $appointment, Consultation $consultation)
    {
        Gate::authorize('view', $consultation);

        return Inertia::render('admin/patients/PatientsConsultationDetail', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'consultation' => $consultation->toResource(),
            'activities' => Inertia::optional(
                fn () => ActivityResource::collection($consultation->activities()->with('causer')->latest()->get())
            )
        ]);
    }

    public function laboratoryResults(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewAny', LaboratoryResult::class);

        $appointment->load(['invoice']);

        return Inertia::render('admin/patients/PatientsLaboratoryResults', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'laboratory_results' => $appointment?->laboratoryResults?->toResourceCollection(),
        ]);
    }

    /**
     * Display the patient's laboratory result detail.
     */
    public function laboratoryResultDetail(Patient $patient, Appointment $appointment, LaboratoryResult $laboratoryResult)
    {
        Gate::authorize('view', $laboratoryResult);

        return Inertia::render('admin/patients/PatientsLaboratoryResultDetail', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'laboratory_result' => $laboratoryResult->toResource(),
            'activities' => Inertia::optional(
                fn () => ActivityResource::collection($laboratoryResult->activities()->with('causer')->latest()->get())
            )
        ]);
    }

    /**
     * Display the patient's procedures
     */
    public function procedures(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewAny', Procedure::class);

        return Inertia::render('admin/patients/PatientsProcedures', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'invoice' => $appointment->invoice?->toResource(),
            'procedures' => $appointment->procedures->toResourceCollection(),
        ]);
    }

    /**
     * Display the patient's invoice history.
     */
    public function invoice(Patient $patient, Appointment $appointment)
    {
        Gate::authorize('viewInvoice', $patient);

        $appointment->load(['invoice.invoiceItems']);

        return Inertia::render('admin/patients/PatientsInvoice', [
            'patient' => $patient->toResource(),
            'appointment' => $appointment->toResource(),
            'invoice' => $appointment->invoice?->toResource(),
            'activities' => Inertia::optional(
                fn () => ActivityResource::collection($appointment->invoice?->activities()->with('causer')->latest()->get())
            )
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
     * Soft delete (archive) the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        $patient->delete();

        return redirect()
            ->back()
            ->with('info', 'The patient has been archived.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Patient $patient)
    {
        Gate::authorize('restore', $patient);

        $patient->restore();

        return redirect()
            ->back()
            ->with('success', 'Patient restored successfully.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function forceDestroy(Patient $patient)
    {
        Gate::authorize('forceDelete', $patient);

        $patient->forceDelete();

        return redirect()
            ->back()
            ->with('success', 'Patient permanent deletion successful.');
    }
}
