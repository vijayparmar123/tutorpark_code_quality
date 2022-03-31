<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddFriendListResource extends JsonResource
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
            "name" => $this->full_name,
            "email" => $this->email,
            'tp_id' => $this->details->tp_id,
            "contact_no" => $this->details->phone,
        ];
    }
}
