<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DivisionAttendanceResource extends JsonResource
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
            'id' => ($this->student)?$this->student->_id:null,
            'name' => ($this->student)?$this->student->full_name:null,
            'email' => ($this->student)?$this->student->email:null,
            'tp_id' => ($this->student->details)?$this->student->details->tp_id:null,
            'date' => date('d-m-Y',strtotime($this->date)),
            'attendance_status' => $this->status
        ];
    }

    public function with($request)
    {
        return [
            'total' => $this->count(),
        ];
    }
}
