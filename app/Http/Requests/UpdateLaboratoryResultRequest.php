<?php

namespace App\Http\Requests;

use App\Enums\LaboratoryResultType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateLaboratoryResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('laboratory_results:create', $this->laboratoryResult);
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
            'results_file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:12228'
            ],
            'type' => [
                'required',
                'string',
                Rule::enum(LaboratoryResultType::class),
            ],
        ];
    }
}
