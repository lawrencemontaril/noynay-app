<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Models\Payment;
use App\Notifications\InvoicePaid;

class PaymentObserver
{
    /**
     * Handle the Payment "saving" event.
     */
    public function saving(Payment $payment): void
    {
        $invoice = $payment->invoice;

        if (! $invoice->invoiceItems()->exists()) {
            $invoice->status = InvoiceStatus::UNPAID;
        } elseif ($invoice->balance <= 0) {
            $invoice->status = InvoiceStatus::PAID;
            $invoice->appointment->patient->user?->notify(new InvoicePaid($invoice));
        } elseif ($invoice->total_paid > 0) {
            $invoice->status = InvoiceStatus::PARTIALLY_PAID;
        } else {
            $invoice->status = InvoiceStatus::UNPAID;
        }
    }
}
