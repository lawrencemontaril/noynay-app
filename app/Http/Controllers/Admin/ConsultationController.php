<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Consultation::class);

        $consultations = Consultation::with(['appointment.patient.user'])
            ->searchPatient($request->input('q'))
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('consultations.id', $request->input('id'))
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/consultations/ConsultationsIndex', [
            'consultations' => $consultations->toResourceCollection(),
            'filters' => $request->only(['q'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultationRequest $request)
    {
        $consultation = Consultation::create($request->validated());

        return redirect()
            ->route('admin.patients.appointments.consultations', ['patient' => $consultation->appointment->patient_id, 'appointment' => $consultation->appointment_id])
            ->with('success', 'Consultation created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsultationRequest $request, Consultation $consultation)
    {
        $consultation->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Consultation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        Gate::authorize('delete', $consultation);

        $consultation->delete();

        return redirect()
            ->back()
            ->with('success', 'Consultation deleted successfully.');
    }
}
