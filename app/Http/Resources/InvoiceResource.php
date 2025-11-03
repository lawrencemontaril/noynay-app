<?php

namespace App\Http\Resources;

use App;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends PaginatedJsonResource
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
            'total' => $this->total,
            'total_paid' => $this->total_paid,
            'balance' => $this->balance,
            'status' => $this->status,
            'created_at' => DateTimeResource::make($this->created_at),
            'updated_at' => DateTimeResource::make($this->updated_at),

            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
            'invoice_items' => InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
