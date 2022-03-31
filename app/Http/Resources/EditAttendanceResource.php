<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EditAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
		// dd($this->student);
        return [
            "id" =>  $this->student->_id,
            "name"=> $this->student->full_name,
            "email"=> $this->student->email,
            "tp_id"=> $this->student->details->tp_id,
            "status"=> $this->status,
        ];
    }
}
