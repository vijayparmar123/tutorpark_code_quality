<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QuestionBankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $options = null;
		if($this->type)
		{
			if($this->type->tag == 'match_following')
			{
				$options = ($this->options)?new QuestionOptionsTypesResource($this):null;
			}elseif($this->type->tag == 'mcq')
			{
				$options = ($this->options)?QuestionOptionsResource::collection($this->options):null;
			}else{
				$options = ($this->options)?QuestionOptionsResource::collection($this->options):null;
			}
		}
        return [
            'id' => $this->_id,
            'question' => $this->question,
            'class' => ($this->class)?$this->class->name:null,
            'subject' => ($this->subject)?$this->subject->name:null,
            'syllabus' => ($this->syllabus)?$this->syllabus->name:null,
            'type' => ($this->type()->first())?new QuestionTypeResource($this->type()->first()):null,
            'options' => $options,
            'answer' => ($this->answer)?new QuestionAnswerResource($this->answer):null,
            'comprehensiveQuestions' => ($this->comprehensiveQuestions)?comprehensiveQuestionsResource::collection($this->comprehensiveQuestions):null,
        ];
    }
}
