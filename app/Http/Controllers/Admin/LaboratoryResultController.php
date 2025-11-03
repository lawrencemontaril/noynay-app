<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaboratoryResultRequest;
use App\Http\Requests\UpdateLaboratoryResultRequest;
use App\Models\LaboratoryResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LaboratoryResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', LaboratoryResult::class);

        $laboratoryResults = LaboratoryResult::with(['appointment.patient.user'])
            ->searchPatient($request->input('q'))
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) =>
                $q->where('status', $request->input('status'))
            )
            ->when($request->filled('type') && $request->input('type') !== 'all', fn ($q) =>
                $q->where('type', $request->input('type'))
            )
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('laboratory_results.id', $request->input('id'))
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/laboratory_results/LaboratoryResultsIndex', [
            'laboratory_results' => $laboratoryResults->toResourceCollection(),
            'filters' => $request->only(['q', 'status', 'type'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLaboratoryResultRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('results_file')) {
            $path = $request->file('results_file')->store('laboratory_results', 'public');
            $data['results_file_path'] = $path;
        }

        LaboratoryResult::create([
            'appointment_id' => $data['appointment_id'],
            'description' => $data['description'],
            'type' => $data['type'],
            'results_file_path' => $data['results_file_path'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Laboratory Result created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLaboratoryResultRequest $request, LaboratoryResult $laboratoryResult)
    {
        $data = $request->validated();

        if ($request->hasFile('results_file')) {
            // Delete the old file if it exists
            if ($laboratoryResult->results_file_path && \Storage::disk('public')->exists($laboratoryResult->results_file_path)) {
                \Storage::disk('public')->delete($laboratoryResult->results_file_path);
            }

            $path = $request->file('results_file')->store('laboratory_results', 'public');
            $data['results_file_path'] = $path;
        }

        $laboratoryResult->update([
            'appointment_id' => $data['appointment_id'],
            'description' => $data['description'],
            'type' => $data['type'],
            'results_file_path' => $data['results_file_path'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Laboratory Result updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaboratoryResult $laboratoryResult)
    {
        Gate::authorize('delete', $laboratoryResult);

        if ($laboratoryResult->results_file_path && \Storage::disk('public')->exists($laboratoryResult->results_file_path)) {
            \Storage::disk('public')->delete($laboratoryResult->results_file_path);
        }

        $laboratoryResult->delete();

        return redirect()
            ->back()
            ->with('success', 'Laboratory Result deleted successfully.');
    }
}
