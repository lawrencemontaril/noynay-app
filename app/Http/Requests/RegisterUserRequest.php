<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\{Rule, Rules};
use App\Enums\{PatientCivilStatus, PatientGender};

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'email' => [
                'required',
                'string',
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
            ],
            'gender' => [
                'required',
                'string',
                Rule::enum(PatientGender::class)
            ],
            'civil_status' => [
                'required',
                'string',
                Rule::enum(PatientCivilStatus::class)
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
}
