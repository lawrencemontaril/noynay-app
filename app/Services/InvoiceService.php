<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Setting;
use App\Notifications\AppointmentCompleted;
use Illuminate\Support\{Arr, Collection};
use App\Enums\InvoiceStatus;
use App\Notifications\{InvoiceCreated, InvoicePaid};
use App\Models\Invoice;

class InvoiceService
{
    protected int $vatPercent;
    protected int $specialDiscountPercent;

    public function __construct()
    {
        $setting = Setting::select(['vat_percent', 'special_discount_percent'])->first();

        $this->vatPercent = $setting->vat_percent;
        $this->specialDiscountPercent = $setting->special_discount_percent;
    }

    /**
     * Create a new invoice
     */
    public function create(array $data): Invoice
    {
        $totals = $this->calculateTotals($data['items'], $data['with_discount'] ?? false);

        $invoice = Invoice::create(array_merge(
            Arr::except($data, ['items']),
            $totals
        ));

        if ($data['items']) {
            $invoice->invoiceItems()->createMany($data['items']);
        }

        $this->notifyInvoiceCreation($invoice);
        $this->notifyPatientOfAppointmentCompletion($invoice->appointment);

        return $invoice;
    }

    /**
     * Update invoice
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        if (isset($data['items'])) {
            $this->syncInvoiceItems($invoice, collect($data['items']));
            $totals = $this->calculateTotals($data['items'], $invoice->with_discount ?? false);
            $invoice->update($totals);
        }

        $invoice->update(Arr::except($data, ['items', 'with_discount']));

        $this->updateInvoiceStatus($invoice);

        return $invoice->load('invoiceItems');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
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

    protected function updateInvoiceStatus(Invoice $invoice)
    {
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

        $invoice->save();
    }

    protected function calculateTotals(array $items, bool $withDiscount = false): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineTotal;
        }

        // Apply special discount first
        $discountAmount = 0;
        if ($withDiscount) {
            $discountAmount = $subtotal * ($this->specialDiscountPercent / 100);
        }

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // Apply VAT
        $vatAmount = $withDiscount ? 0 : $subtotalAfterDiscount * ($this->vatPercent / 100);

        $total = $subtotalAfterDiscount + $vatAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'vat_amount' => round($vatAmount, 2),
            'total' => round($total, 2),
        ];
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

    protected function notifyPatientOfAppointmentCompletion(Appointment $appointment)
    {
        $appointment->update(['status' => AppointmentStatus::COMPLETED]);

        $appointment->patient->user?->notify(new AppointmentCompleted($appointment));
    }
}
