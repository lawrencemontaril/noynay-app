<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcedureResource extends JsonResource
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
            'description' => $this->description,
            'quantity' => $this->quantity,
            'created_at' => DateTimeResource::make($this->created_at),
            'updated_at' => DateTimeResource::make($this->updated_at),

            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
        ];
    }
}
