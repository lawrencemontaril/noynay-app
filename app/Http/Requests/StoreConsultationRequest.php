<?php

namespace App\Http\Requests;

use App\Models\Consultation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Consultation::class);
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
            'type' => [
                'required',
                Rule::in([
                    'consultation',
                    'family_planning_counseling', 'natural_methods',
                    'chelation_therapy', 'magnetic_resonance_analysis', 'multifunctional_high_potential_therapeutic_services', 'weight_loss_management', 'psychosocial_and_spiritual_counseling',
                    'pre_natal_and_post_natal', 'normal_spontaneous_delivery', 'immunization', 'ear_pearcing', 'nebulization', 'foley_catheter_insertion', 'surgical_wound_dressing', 'cord_dressing', 'suture_removal', 'issuance_of_bc_newborn_screening',
                    'general_opd_consultation', 'medical_opd_consultation', 'minor_surgical_procedures', 'issuance_of_medical_certificate', 'pedia_adult_vaccination_services'
                ]),
            ],
            'chief_complaints' => [
                'required',
                'string'
            ],
            'assessment' => [
                'required',
                'string',
            ],
            'plan' => [
                'required',
                'string'
            ],
            'systolic' => [
                'nullable',
                'integer',
                'min:70',
                'max:250'
            ],
            'diastolic' => [
                'nullable',
                'integer',
                'min:40',
                'max:150'
            ],
            'heart_rate' => [
                'nullable',
                'integer',
                'min:30',
                'max:220'
            ],
            'respiratory_rate' => [
                'nullable',
                'integer',
                'min:8',
                'max:40'
            ],
            'weight_kg' => [
                'nullable',
                'numeric',
                'max:999'
            ],
            'height_cm' => [
                'nullable',
                'numeric',
                'max:300'
            ],
            'temperature_c' => [
                'nullable',
                'numeric',
                'max:50'
            ],
            'oxygen_saturation' => [
                'nullable',
                'integer',
                'min:70',
                'max:100'
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! is_null($this->systolic) && ! is_null($this->diastolic)) {
                if ($this->systolic <= $this->diastolic) {
                    $validator->errors()->add('systolic', 'Systolic must be greater than diastolic.');
                }
            }
        });
    }

    protected function passedValidation()
    {
        if ($this->weight_kg && $this->height_cm) {
            $heightM = $this->height_cm / 100;
            $bmi = round($this->weight_kg / ($heightM * $heightM), 1);

            $this->merge([
                'bmi' => $bmi,
            ]);
        }
    }
}
