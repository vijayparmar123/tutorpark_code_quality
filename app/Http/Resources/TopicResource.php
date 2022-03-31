<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
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
            'name' => $this->name,
            'key_points' => $this->key_points,
            'assignment_id'=> $this->assignment_id,
            'external_urls'=> $this->external_urls,
            'subject_name'=> $this->subject,
            'description'=> $this->description,
            
        ];
    }
}
