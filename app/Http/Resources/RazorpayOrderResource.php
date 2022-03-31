<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class RazorpayOrderResource extends JsonResource
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
            'order_id'=>$this->id,
            'entity'=>$this->entity,
            'amount'=> $this->amount,
            'amount_paid'=> $this->amount_paid,
            'amount_due'=>$this->amount_due,
            'currency'=>$this->currency,
            'receipt'=>$this->receipt,
            'offer_id'=>$this->offer_id,
            'status'=>$this->status,
            'attempts'=> $this->attempts,
            'notes'=> $this->notes,
            'created_at'=> $this->created_at,
        ];
    }
}
