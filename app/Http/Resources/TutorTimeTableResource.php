<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TutorTimeTableResource extends JsonResource
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
            'start_date'=>$this->start_date,
            'end_date'=>$this->end_date,
            'week_day'=>$this->week_day,
            'times'=>$this->times,
            'teaching_mode'=>$this->teaching_mode,
            'tutor_id'=>$this->tutor_id
        ];
    }
}
