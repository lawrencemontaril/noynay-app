<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PatientResource extends PaginatedJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array{address: mixed, age: AgeResource, birthdate: DateResource, civil_status: mixed, company_address: mixed, company_name: mixed, contact_number: mixed, created_at: DateTimeResource, first_name: mixed, gender: mixed, guardian_contact_number: mixed, guardian_name: mixed, id: mixed, is_employed: mixed, last_name: mixed, middle_name: mixed, occupation: mixed, referrer_contact_number: mixed, referrer_name: mixed, updated_at: DateTimeResource, user: UserResource, user_id: mixed}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'gender' => $this->gender,
            'civil_status' => $this->civil_status,
            'birthdate' => DateResource::make($this->birthdate),
            'age' => AgeResource::make($this->birthdate),
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'created_at' => DateTimeResource::make($this->created_at),
            'updated_at' => DateTimeResource::make($this->updated_at),

            'user' => UserResource::make($this->whenLoaded('user')),
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'consultations' => ConsultationResource::collection($this->whenLoaded('consultations')),
            'laboratoryResults' => LaboratoryResultResource::collection($this->whenLoaded('laboratoryResults')),
        ];
    }
}
