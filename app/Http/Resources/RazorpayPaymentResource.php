<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TutorTimeTable;

class RazorpayPaymentResource extends JsonResource
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
            'id' => $this->id,
            'razorpay_order_id' => $this->razorpay_order_id,
            'razorpay_payment_id' => $this->razorpay_payment_id,
            'razorpay_signature' => $this->razorpay_signature,
            'payment_by' => ($this->paymentBy)?new UserpluckResource($this->paymentBy):null,
        ];
    }
}
