<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserpluckResource extends JsonResource
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
            'id'=>$this->id,
            'name' => $this->full_name,
            'email' => $this->email,
            'tp_id' => ($this->details)?$this->details->tp_id:null,
            'profile' => ($this->details) ? url('storage/images/user/'.strtolower($this->details->gender).".jpg") : null,
        ];
    }
}
