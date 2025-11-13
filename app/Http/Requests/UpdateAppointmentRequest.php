<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Enums\{AppointmentStatus, AppointmentType};

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->appointment);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'complaints' => [
                'sometimes',
                'nullable',
                'string'
            ],
            'scheduled_at' => [
                'sometimes',
                'date',
            ],
            'type' => [
                'sometimes',
                'string',
                Rule::enum(AppointmentType::class),
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::enum(AppointmentStatus::class)
            ],
        ];
    }
}
