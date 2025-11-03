<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    /*
    | -----------------------------------------------------------
    |  Determine if the user is authorized to make this request.
    | -----------------------------------------------------------
    */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->patient);
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
                Rule::unique('patients', 'user_id')->ignore($this->patient),
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
                Rule::in(['male', 'female'])
            ],
            'civil_status' => [
                'required',
                'string',
                Rule::in(['single', 'married', 'widowed', 'divorced', 'separated'])
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
            'user_id.prohibited_unless' => 'Associated user cannot be edited once set.',
            'occupation.required_if' => 'The occupation field is required when the patient is employed.',
            'company_name.required_if' => 'The company name field is required when the patient is employed.',
            'company_address.required_if' => 'The company address field is required when the patient is employed.',
        ];
    }
}
