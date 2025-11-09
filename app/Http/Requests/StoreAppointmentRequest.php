<?php

namespace App\Http\Requests;

use App\Enums\AppointmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Appointment;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Appointment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'complaints' => [
                'nullable',
                'string'
            ],
            'scheduled_at' => [
                'required',
                'date',
            ],
            'type' => [
                'required',
                'string',
                Rule::enum(AppointmentType::class),
            ],
            'status' => [
                'prohibited',
            ],
        ];
    }
}
