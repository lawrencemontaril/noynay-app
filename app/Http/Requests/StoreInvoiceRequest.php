<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Invoice;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Invoice::class);
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
                'required',
                'integer',
                Rule::exists('appointments', 'id'),
                Rule::unique('invoices', 'appointment_id'),
            ],
            'items' => [
                'required',
                'array',
                'min:1',
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
