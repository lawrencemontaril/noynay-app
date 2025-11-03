<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $invoices = Invoice::with(['appointment.patient', 'invoiceItems', 'payments'])
            ->searchPatient($request->input('q'))
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) =>
                $q->where('status', $request->input('status'))
            )
            ->when($request->filled('id') && $request->input('id') !== '', fn ($q) =>
                $q->where('invoices.id', $request->input('id'))
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/invoices/InvoicesIndex', [
            'invoices' => $invoices->toResourceCollection(),
            'filters' => $request->only(['q', 'status'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request, InvoiceService $invoiceService)
    {
        $invoice = $invoiceService->create($request->validated());

        return redirect()
            ->route('admin.patients.appointments.invoice', ['patient' => $invoice->appointment->patient, 'appointment' => $invoice->appointment])
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice, InvoiceService $invoiceService)
    {
        $invoiceService->update($invoice, $request->validated());

        return redirect()
            ->back()
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        $invoice->delete();

        return redirect()
            ->back()
            ->with('success', 'Invoice deleted successfully.');
    }
}
