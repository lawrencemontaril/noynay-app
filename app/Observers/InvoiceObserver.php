<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Notifications\InvoicePaid;

class InvoiceObserver
{
    /**
     * Handle the Invoice "saving" event.
     */
    public function saving(Invoice $invoice): void
    {
        if (! $invoice->invoiceItems()->exists()) {
            $invoice->status = 'unpaid';
        } elseif ($invoice->balance <= 0) {
            $invoice->status = 'paid';
            optional($invoice->appointment?->patient?->user)?->notify(new InvoicePaid($invoice));
        } elseif ($invoice->total_paid > 0) {
            $invoice->status = 'partially_paid';
        } else {
            $invoice->status = 'unpaid';
        }
    }
}
