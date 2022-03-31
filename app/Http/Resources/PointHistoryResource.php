<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'date' => getDateF($this->created_at),
            'source_of_point' => $this->source_of_point,
            'points' => $this->points,
            'transfer_from' => ($this->transferBy)?new UserpluckResource($this->transferBy):null,
            'transfer_to' => ($this->transferTo)?new UserpluckResource($this->transferTo):null,
            'payment_type' => ($this->payment_mode)?$this->payment_mode:null,
            'amount' => ($this->points)?$this->points / 10:0,
            'payment' => ($this->payment)?new RazorpayPaymentResource($this->payment()->first()):null,
        ];
    }
}
