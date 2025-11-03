<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaboratoryResultResource extends PaginatedJsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'results_file_path' => $this->results_file_path,
            'results_file_url' => $this->results_file_url,
            'created_at' => DateTimeResource::make($this->created_at),
            'updated_at' => DateTimeResource::make($this->updated_at),

            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
        ];
    }
}
