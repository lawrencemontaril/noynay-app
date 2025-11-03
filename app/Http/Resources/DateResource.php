<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array{date_time: mixed, human: mixed}
     */
    public function toArray(Request $request): array
    {
        return [
            'human' => $this->timezone('Asia/Manila')->diffForHumans(),
            'date_time' => $this->timezone('Asia/Manila')->toDateString(),
            'formatted_date' => $this->timezone('Asia/Manila')->format('F j, Y'),
        ];
    }
}
