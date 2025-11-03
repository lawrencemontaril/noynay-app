<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class DateTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array{date_time: mixed, human: mixed, formatted_date: mixed}
     */
    public function toArray(Request $request): array
    {
        return [
            'human' => $this->timezone('Asia/Manila')->diffForHumans(),
            'date_time' => $this->timezone('Asia/Manila')->toDateTimeString(),
            'formatted_date' => $this->timezone('Asia/Manila')->format('F j, Y, g:i A'),
        ];
    }
}
