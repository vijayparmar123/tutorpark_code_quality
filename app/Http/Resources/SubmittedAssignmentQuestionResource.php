<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmittedAssignmentQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $student_answer = '';
        
        if($this->studentAssignmentSectionQuestionAnswer()->exists())
        {
            $student_answer = ($this->studentAssignmentSectionQuestionAnswer)?new SubmittedAssignmentQuestionAnswerResource($this->studentAssignmentSectionQuestionAnswer):null;
        }elseif($this->studentAssignmentComprehensiveQuestion()->exists())
        {
            $student_answer = ($this->studentAssignmentComprehensiveQuestion)?SubmittedAssignmentComprehensiveQuestionResource::collection($this->studentAssignmentComprehensiveQuestion):null;
        }elseif($this->studentAssignmentSectionQuestionOptions()->exists()){
            $student_answer = ($this->studentAssignmentSectionQuestionOptions)?new SubmittedAssignmentQuestionOptionTypeResource($this):null;
        }
        else{
            $student_answer = ($this->studentAssignmentSectionQuestionAnswer()->exists())?new SubmittedAssignmentQuestionAnswerResource($this->studentAssignmentSectionQuestionAnswer):null;
        }
        
        return [
            'section_question_id' => $this->_id,
            'obtained_mark' => $this->obtained_mark,
            'question_detail' => ($this->AssignmentQuestion->question)?new QuestionBankResource($this->AssignmentQuestion->question):null,
            'student_answer' => $student_answer
        ];
    }
}
