<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Patient;
use Inertia\Inertia;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;

class PatientController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        return Inertia::render('user/patients/PatientsShow', [
            'patient' => $patient->toResource(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        Gate::authorize('update', $patient);

        $patient->load('user');

        return Inertia::render('user/patients/PatientsEdit', [
            'patient' => $patient->toResource(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return redirect()
            ->route('patients.index')
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
