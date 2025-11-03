<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array{days: float, formatted_long: mixed, formatted_short: mixed, months: float, years: float}
     */
    public function toArray(Request $request): array
    {
        $years = floor($this->diffInYears());
        $months = floor($this->copy()->addYears($years)->diffInMonths());
        $days = floor($this->copy()->addYears($years)->addMonths($months)->diffInDays());

        return [
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'formatted_short' => Carbon::diffFromDate($this->resource, true, true, 2),
            'formatted_long' => Carbon::diffFromDate($this->resource, true, false, 2),
        ];
    }
}
