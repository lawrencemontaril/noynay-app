<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AppointmentResource extends PaginatedJsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'complaints' => $this->complaints,
            'type' => $this->type,
            'status' => $this->status,
            'is_reschedulable' => $this->is_reschedulable,
            'is_cancellable' => $this->is_cancellable,
            'is_operatable' => $this->is_operatable,
            'scheduled_at' => DateTimeResource::make($this->scheduled_at),
            'created_at' => DateTimeResource::make($this->created_at),
            'updated_at' => DateTimeResource::make($this->updated_at),

            'patient' => PatientResource::make($this->whenLoaded('patient')),
            'invoice' => InvoiceResource::make($this->whenLoaded('invoice')),
            'consultations' => ConsultationResource::collection($this->whenLoaded('consultations')),
            'laboratory_results' => LaboratoryResultResource::collection($this->whenLoaded('laboratoryResults'))
        ];
    }
}
