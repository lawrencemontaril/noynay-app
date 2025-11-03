<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        $patient = auth()->user()->patient;

        $consultations = $patient
            ->consultations()
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('consultations.id', $request->input('id'))
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('user/consultations/ConsultationsIndex', [
            'patient' => $patient->toResource(),
            'consultations' => $consultations->toResourceCollection(),
            'filters' => $request->only(['q'])
        ]);
    }
}
