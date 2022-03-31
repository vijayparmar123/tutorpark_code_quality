<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionQuestionResource extends JsonResource
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
            'section_question_id' => $this->id,
            'mark' => $this->mark,
            'question' => ($this->question)?new QuestionBankResource($this->question):null,
        ];
    }
}
