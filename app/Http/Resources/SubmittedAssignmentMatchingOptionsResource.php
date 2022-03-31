<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubmittedAssignmentMatchingOptionsResource extends JsonResource
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
            // 'id' => $this->_id,
            // 'type' => $this->type,
            'name' => ($this->QuestionOption->name)?$this->QuestionOption->name:null, 
            'matching_key' => $this->matching_key,
            'key' => $this->key,
        ];
    }
}
