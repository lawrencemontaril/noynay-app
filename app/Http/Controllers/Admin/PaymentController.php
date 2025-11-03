<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request, PaymentService $paymentService)
    {
        $paymentService->create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Payment created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        Gate::authorize('delete', $payment);

        $payment->delete();

        return redirect()
            ->back()
            ->with('success', 'Payment deleted successfully.');
    }
}
