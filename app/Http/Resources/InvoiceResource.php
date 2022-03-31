<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->invoicable_type == 'App\Models\User')
        {
            $invoice_for = 'user';
            $invoicable = ($this->invoicable)?new UserpluckResource($this->invoicable):null;
        }
        
        if($this->invoicable_type == 'App\Models\School')
        {
            $invoice_for = 'school';
            $invoicable = ($this->invoicable)?new SchoolResource($this->invoicable):null;
        }
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'invoice_for' => $invoice_for,
            'invoicable' => $invoicable,
            'date' => date('d-m-Y',strtotime($this->date)),
            'payment_method' => ($this->payment_method)?$this->payment_method:null,
            'payment_status' => ($this->payment_status)?$this->payment_status:null,
            'transaction_id' => ($this->transaction_id)?$this->transaction_id:null,
            'amount' => ($this->amount)?$this->amount:null,
            'mode_of_tuition' => ($this->mode_of_tuition)?$this->mode_of_tuition:null,
            'course_tuition_name' => ($this->course_tuition_name)?$this->course_tuition_name:null,
            'session_taken' => ($this->session_taken)?$this->session_taken:null,
            'purpose' => ($this->purpose)?$this->purpose:null,
        ];
    }
}
