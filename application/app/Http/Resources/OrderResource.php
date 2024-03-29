<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'date' => $this->date,
            'delivered_by' => $this->delivered_by,
            'payment_type' => $this->payment_type,
            'payment_reference' => $this->payment_reference,
            'points_gained' => $this->points_gained,
            'points_used_to_pay' => $this->points_used_to_pay,
            'total_paid' => $this->total_paid,
            'total_price' => $this->total_price,
        ];
    }
}
