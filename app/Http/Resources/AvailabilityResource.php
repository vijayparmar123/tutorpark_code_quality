<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
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
            'day' => $this->day,
            'start_time' => $this->start_time->format("H:i"),
            'end_time' => $this->end_time->format("H:i"),
            //'mode_of_teaching'=>$this->mode,
        ];
    }
}
