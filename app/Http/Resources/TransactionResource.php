<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $purpose = "Subscribed to ". substr($this->model, strrpos($this->model, "\\") + 1);
        return [
            "date" => $this->date->format('Y-m-d H:i'),
            "from_name" => $this->from_user->full_name,
            "id_link" => "12345678",
            "from_email" => $this->from_user->email,
            "teaching_mode" => $this->mode_of_teaching,
            "payment_mode" => $this->payment_mode,
            "transaction_id" => $this->transaction_id,
            "amount" => $this->amount,
            "tp_commission" => $this->tp_commission,
            "final_amount" => $this->final_amount,
            "purpose" => $purpose,
            "status" => $this->payment_status,
        ];
    }
}
