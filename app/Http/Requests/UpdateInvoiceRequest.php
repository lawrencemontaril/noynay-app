<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->invoice);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_id' => [
                'sometimes',
                'integer',
                Rule::exists('appointments', 'id'),
                Rule::unique('invoices', 'appointment_id')->ignore($this->invoice),
            ],
            'items' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'items.*.id' => [
                'nullable',
                'integer',
                Rule::exists('invoice_items', 'id')
                    ->where('invoice_id', $this->invoice->id),
            ],
            'items.*.description' => [
                'required',
                'string',
                'max:255',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}
