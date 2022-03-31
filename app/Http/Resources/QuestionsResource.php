<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $best_answer = "No";
        if(!empty($this->answers)){
            foreach($this->answers as $value){
                if(isset($value['best_answer'])){
                    $best_answer = "Yes";
                }
            }
        }
        
        return [
            'id' => $this->id,
            'subject' => $this->subject ? $this->subject->name : null,
            'syllabus' => ($this->syllabus) ? ($this->syllabus->name) : ($this->division ? $this->division->class->syllabus->name : null),
            'division' => $this->division ? $this->division->name : null,
            'class' => ($this->class) ? ($this->class->name) : ($this->division ? $this->division->class->class->name : null),
            'school_class' => ($this->division) ? $this->division->class->name : null,
           // 'topic' => $this->topic ? $this->topic->name : null,
            'topic' => $this->topic_name ? $this->topic_name : null,
            'created_by' => $this->user ? $this->user->full_name : null,
            'created_by_email' => $this->user ? $this->user->email  : null,
            'image' => ($this->resource instanceof User) ?  url('storage/images/user/'.strtolower($this->user->details->gender).".jpg") : url('storage/images/user/'.strtolower($this->user->details->gender).".jpg"),
            'question' => $this->question,
            'created_at' => getDateF($this->created_at),
            'created_time' => getTimeF($this->created_at),
            'like' => $this->like ? $this->like : 0,
            'dislike' => $this->dislike ? $this->dislike : 0,
			'my_student_question' => $this->my_student_question,
            'best_answer' => $best_answer,
            'answers' => !empty($this->answers) ? AnswersResource::collection($this->answers) : $this->answers,
        ];
    }
}
