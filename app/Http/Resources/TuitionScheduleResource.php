<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TuitionScheduleResource extends JsonResource
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
            'day' => $this->day ? $this->day : null,
            'date' => $this->start_time ? date("d-m-Y",strtotime($this->start_time)) : null,
            'start_time' => $this->start_time ? date("h:i A",strtotime($this->start_time)) : null,
            'end_time' => $this->end_time ? date("h:i A",strtotime($this->end_time)) : null,
        ];
    }
}
