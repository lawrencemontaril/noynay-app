<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patient = auth()->user()->patient;

        $invoices = $patient->invoices()
            ->with(['invoiceItems', 'payments'])
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) =>
                $q->where('invoices.status', $request->input('status'))
            )
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('invoices.id', $request->input('id'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('user/invoices/InvoicesIndex', [
            'patient' => $patient->toResource(),
            'invoices' => $invoices->toResourceCollection(),
            'filters' => $request->only(['status'])
        ]);
    }
}
