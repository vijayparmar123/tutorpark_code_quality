<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'detailed_feedback' => $this->detailed_feedback,
            'given_by' => $this->user ? $this->user->first_name." ".$this->user->last_name  : null,
            'given_by_image' => ($this->resource instanceof User) ?  url('storage/images/user/'.strtolower($this->user->details->gender).".jpg") : url('storage/images/user/'.strtolower($this->user->details->gender).".jpg"),
            'date' => $this->date,
            'date' => $this->date ? date("d-m-Y",strtotime($this->date)) : null,
            'time' => $this->date ? date("h:i A",strtotime($this->date)) : null,
            'feedback_for' => $this->feedback_for,
            'feedback_reference_id' => $this->feedback_reference_id,
            'total_ratings' => $this->total_ratings,
            'feedback_for_id' => $this->user ? $this->user->first_name." ".$this->user->last_name  : null,
            'feedback_for_id_image' => ($this->resource instanceof User) ?  url('storage/images/user/'.strtolower($this->user->details->gender).".jpg") : url('storage/images/user/'.strtolower($this->user->details->gender).".jpg"),
        ];
    }
}
