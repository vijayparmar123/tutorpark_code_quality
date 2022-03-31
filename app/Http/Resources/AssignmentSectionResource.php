<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentSectionResource extends JsonResource
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
            'assignment_section_id' => $this->_id,
            'section_id' => $this->section_id,
            'section_name' => $this->section->name,
            'description' => $this->description,
            'type' => ($this->type)?new QuestionTypeResource($this->type):null,
            'total_marks' => $this->total_marks,
            'questions' => ($this->questions)?SectionQuestionResource::collection($this->questions):null
        ];
    }
}
