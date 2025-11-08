<?php

namespace App\Services;

use App\Models\Invoice;
use App\Notifications\InvoiceCreated;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class InvoiceService
{
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
        $invoice->update(Arr::except($data, ['items']));

        if (isset($data['items'])) {
            $this->syncInvoiceItems($invoice, collect($data['items']));
        }

        return $invoice->load('invoiceItems');
    }

    protected function syncInvoiceItems(Invoice $invoice, Collection $items): void
    {
        $existingIds = $invoice->invoiceItems()->pluck('id')->all();

        $toUpdate = $items->whereNotNull('id')->keyBy('id');
        $toCreate = $items->whereNull('id');

        // Update
        foreach ($toUpdate as $id => $attributes) {
            $invoice->invoiceItems()->where('id', $id)->update($attributes);
        }

        // Delete removed items
        $idsToKeep = $toUpdate->keys()->all();
        $idsToDelete = array_diff($existingIds, $idsToKeep);
        if ($idsToDelete) {
            $invoice->invoiceItems()->whereIn('id', $idsToDelete)->delete();
        }

        // Create new ones
        if ($toCreate->isNotEmpty()) {
            $invoice->invoiceItems()->createMany($toCreate->all());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    /**
     * Notify user of invoice creation
     */
    protected function notifyInvoiceCreation(Invoice $invoice)
    {
        $user = $invoice->appointment?->patient?->user;

        if ($user) {
            $user->notify(new InvoiceCreated($invoice));
        }
    }
}
