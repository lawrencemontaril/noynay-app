<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class StoreUserRequest extends FormRequest
{
    /*
    | -----------------------------------------------------------
    |  Determine if the user is authorized to make this request.
    | -----------------------------------------------------------
    */
    public function authorize(): bool
    {
        return Gate::allows('create', User::class);
    }

    /*
    | -----------------------------------------------------
    |  Get the validation rules that apply to the request.
    | -----------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::in(['admin', 'system_admin', 'doctor', 'laboratory_staff', 'cashier', 'patient']),
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
            'is_active' => [
                'required',
                'boolean',
            ]
        ];
    }
}
