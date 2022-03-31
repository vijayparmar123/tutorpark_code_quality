<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmittedAssignmentSectionResource extends JsonResource
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
            'obtained_mark' => $this->obtained_mark,
            // 'section_detail' => $this->AssignmentSection,
            'section_detail' => ($this->AssignmentSection)?new AssignmentSectionDetailResource($this->AssignmentSection):null,
            'questions' => ($this->studentAssignmentSectionQuestion)?SubmittedAssignmentQuestionResource::collection($this->studentAssignmentSectionQuestion):null
            // 'questions' => $this->studentAssignmentSectionQuestion
        ];
    }
}
