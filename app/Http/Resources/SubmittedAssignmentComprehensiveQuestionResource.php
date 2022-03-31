<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmittedAssignmentComprehensiveQuestionResource extends JsonResource
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
            'id' => $this->_id,
            'comprehensive_question' => ($this->comprehensiveQuestion)?new comprehensiveQuestionsResource($this->comprehensiveQuestion):null,
        ];
    }
}
