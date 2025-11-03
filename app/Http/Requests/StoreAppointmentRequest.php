<?php

namespace App\Http\Requests;

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
                Rule::in([
                    'consultation',
                    'family_planning_counseling', 'natural_methods',
                    'chelation_therapy', 'magnetic_resonance_analysis', 'multifunctional_high_potential_therapeutic_services', 'weight_loss_management', 'psychosocial_and_spiritual_counseling',
                    'pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis',
                    'pre_natal_and_post_natal', 'normal_spontaneous_delivery', 'immunization', 'ear_pearcing', 'nebulization', 'foley_catheter_insertion', 'surgical_wound_dressing', 'cord_dressing', 'suture_removal', 'issuance_of_bc_newborn_screening',
                    'general_opd_consultation', 'medical_opd_consultation', 'minor_surgical_procedures', 'issuance_of_medical_certificate', 'pedia_adult_vaccination_services'
                ]),
            ],
            'status' => [
                'prohibited',
            ],
        ];
    }
}
