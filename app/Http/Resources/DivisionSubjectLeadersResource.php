<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DivisionSubjectLeadersResource extends JsonResource
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
            'subject_id' => ($this->subject)?$this->subject->_id:null,
            'subject_name' => ($this->subject)?$this->subject->name:null,
            'leader_id' => ($this->leader)?$this->leader->_id:null,
            'leader_name' => ($this->leader)?$this->leader->full_name:null,
            'status' => $this->status
        ];
    }
}
