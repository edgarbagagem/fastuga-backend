<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemProductResource extends JsonResource
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
            'order_id' => $this->order_id,
            'order_local_number' => $this->order_local_number,
            'product_id' => $this->product_id,
            'description' => $this->description,
            'name' =>$this->name,
            'type'=> $this->type,
            'photo_url'=> $this->photo_url,
            'status'=>$this->status,
            'preparation_by'=>$this->preparation_by

        ];
    }
}
