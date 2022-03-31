<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SessionsResource extends JsonResource
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
            'start_date' => $this->start_date ? $this->start_date : null,
            'end_date' => $this->end_date ? $this->end_date : null,
            'date' => $this->date ? $this->date : null,
            'day' => $this->day ? $this->day : null,
        ];
    }
}
