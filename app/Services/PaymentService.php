<?php

namespace App\Services;

use App\Models\Payment;
use App\Notifications\InvoicePaid;

class PaymentService
{
    /**
     * Create a payment
     */
    public function create(array $data): Payment
    {
        $payment = Payment::create($data);

        $invoice = $payment->invoice;

        if ($invoice->balance <= 0) {
            $invoice->status = 'paid';
            $invoice->save();
            $invoice->appointment?->patient?->user->notify(new InvoicePaid($invoice));
        } elseif ($invoice->balance > 0 && $invoice->total_paid > 0) {
            $invoice->status = 'partially_paid';
            $invoice->save();
        } else {
            $invoice->status = 'unpaid';
            $invoice->save();
        }

        return $payment;
    }
}
