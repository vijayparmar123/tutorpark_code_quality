<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
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
            'id'=> $this->_id,
            'type' => ($this->type)?$this->type:null,
            'type_label' => ($this->type)?ucwords(str_replace('_', ' ', $this->type)):null,
            'school_name' => $this->school_name,
            'registration_no' => $this->registration_no,
            'pincode' => $this->pincode,
            'city' => $this->city,
            'phone' => $this->phone,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'principal' => $this->principal,
            'vice_principal' => $this->vice_principal,
            'incharge' => $this->incharge,
            'working_start_date' => $this->working_start_date,
            'working_end_date' => $this->working_end_date,
            'image' => $this->image ?  url('storage/' . $this->image) : null,
            'attachment' => $this->attachment ?  url('storage/' . $this->attachment) : null,
            'is_verified' => $this->is_verified,
            'verified_by' => ($this->verifiedBy)?new UserpluckResource($this->verifiedBy):null,
            'created_by' => ($this->createdBy)?new UserpluckResource($this->createdBy):null,
        ];

        
    }
}
