<?php

namespace App\Http\Requests;

use App\Enums\LaboratoryResultType;
use App\Models\LaboratoryResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreLaboratoryResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('laboratory_results:create', LaboratoryResult::class);
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
            ],
            'description' => [
                'required',
                'string'
            ],
            'type' => [
                'required',
                'string',
                Rule::enum(LaboratoryResultType::class),
            ],
            'results_file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:12288',
            ],
        ];
    }
}
