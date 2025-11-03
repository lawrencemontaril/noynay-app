<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        $patient = $user->patient;

        $lastAppointment = $patient->appointments()->latest('scheduled_at')->first();

        $totalUnpaidAmount = $patient->invoices()
            ->with(['invoiceItems', 'payments'])
            ->get()
            ->sum(fn ($invoice) => $invoice->balance);

        $totalPaidAmount = $patient->invoices()
            ->with(['invoiceItems', 'payments'])
            ->get()
            ->sum(fn ($invoice) => $invoice->total_paid);

        return Inertia::render('user/Dashboard', [
            'last_appointment' => $lastAppointment?->toResource(),
            'total_unpaid_amount' => $totalUnpaidAmount,
            'total_paid_amount' => $totalPaidAmount,
        ]);
    }
}
