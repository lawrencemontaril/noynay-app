<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends PaginatedJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'type' => $this->type,
            'chief_complaints' => $this->chief_complaints,
            'assessment' => $this->assessment,
            'plan' => $this->plan,
            'systolic' => $this->systolic,
            'diastolic' => $this->diastolic,
            'heart_rate' => $this->heart_rate,
            'respiratory_rate' => $this->respiratory_rate,
            'weight_kg' => $this->weight_kg,
            'height_cm' => $this->height_cm,
            'bmi' => $this->bmi,
            'temperature_c' => $this->temperature_c,
            'oxygen_saturation' => $this->oxygen_saturation,
            'created_at' => DateTimeResource::make($this->created_at),
            'updated_at' => DateTimeResource::make($this->updated_at),

            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
        ];
    }
}
