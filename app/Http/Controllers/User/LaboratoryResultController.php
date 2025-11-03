<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryResult;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaboratoryResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patient = $request->user()->patient;

        $laboratoryResults = $patient->laboratoryResults()
            ->when($request->filled('type') && $request->input('type') !== 'all', fn ($q) =>
                $q->where('type', $request->input('type'))
            )
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) =>
                $q->where('status', $request->input('status'))
            )
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('laboratory_results.id', $request->input('id'))
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('user/laboratory_results/LaboratoryResultsIndex', [
            'patient' => $patient->toResource(),
            'laboratory_results' => $laboratoryResults->toResourceCollection(),
            'filters' => $request->only(['type', 'status']),
        ]);
    }
}
