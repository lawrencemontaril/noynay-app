<?php

namespace App\Services;

use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\ConsultationRequest;
use App\Notifications\InvoiceCreated;
use App\Notifications\InvoicePaid;
use App\Notifications\LaboratoryResultRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Get an invoice by ID (with services).
     */
    public function find(int $id): Invoice
    {
        return Invoice::findOrFail($id);
    }

    /**
     * Create a new invoice
     */
    public function create(array $data): Invoice
    {
        $invoice = Invoice::create(Arr::except($data, ['items']));

        if ($data['items']) {
            $invoice->invoiceItems()->createMany($data['items']);
        }

        // Mark appointment as completed
        if ($invoice->appointment()->exists()) {
            $appointment = $invoice->appointment;
            $appointment->status = 'completed';
            $appointment->save();
        }

        $this->notifyInvoiceCreation($invoice);

        return $invoice;
    }

    /**
     * Update invoice
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        // Replace invoice items if provided
        if ($items = data_get($data, 'items')) {
            $invoice->invoiceItems()->delete();
            $invoice->invoiceItems()->createMany($items);
        }

        // Update invoice fields except items
        $invoice->update(Arr::except($data, ['items']));

        return $invoice;
    }

    protected function notifyInvoiceCreation(Invoice $invoice)
    {
        $user = $invoice->appointment?->patient?->user;

        if ($user) {
            $user->notify(new InvoiceCreated($invoice));
        }
    }
}
