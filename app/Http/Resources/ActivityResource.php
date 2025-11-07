<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'event' => $this->event,
            'properties' => $this->properties,
            'causer' => UserResource::make($this->whenLoaded('causer')),
            'created_at' => DateTimeResource::make($this->created_at),
        ];
    }
}
