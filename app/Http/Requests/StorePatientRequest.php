<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Enums\{PatientCivilStatus, PatientGender};
use App\Models\Patient;

class StorePatientRequest extends FormRequest
{
    /*
    | -----------------------------------------------------------
    |  Determine if the user is authorized to make this request.
    | -----------------------------------------------------------
    */
    public function authorize(): bool
    {
        return Gate::allows('create', Patient::class);
    }

    /*
    | -----------------------------------------------------
    |  Get the validation rules that apply to the request.
    | -----------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::unique('patients', 'user_id'),
            ],
            'first_name' => [
                'required',
                'string',
                'max:80'
            ],
            'last_name' => [
                'required',
                'string',
                'max:80'
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:80'
            ],
            'gender' => [
                'required',
                'string',
                Rule::enum(PatientGender::class)
            ],
            'civil_status' => [
                'required',
                'string',
                Rule::in(PatientCivilStatus::class)
            ],
            'birthdate' => [
                'required',
                'date',
                'after_or_equal:1900-1-1',
                'before_or_equal:today'
            ],
            'contact_number' => [
                'required',
                'string',
                'max:24'
            ],
            'address' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'occupation.required_if' => 'The occupation field is required when the patient is employed.',
            'company_name.required_if' => 'The company name field is required when the patient is employed.',
            'company_address.required_if' => 'The company address field is required when the patient is employed.',
        ];
    }
}
