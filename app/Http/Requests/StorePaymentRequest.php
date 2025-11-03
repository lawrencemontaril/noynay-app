<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Payment;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Payment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => [
                'required',
                'integer',
                Rule::exists('invoices', 'id')
            ],
            'amount' => [
                'required',
                'numeric'
            ]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $invoice = Invoice::find($this->invoice_id);

            if (! $invoice) {
                return; // 'invoice_id' rule already covers this
            }

            if ($this->amount > $invoice->balance) {
                $formattedBalance = number_format($invoice->balance, 2);
                $validator->errors()->add(
                    'amount',
                    "The amount may not be greater than the invoice balance of ₱{$formattedBalance}."
                );
            }
        });
    }
}
