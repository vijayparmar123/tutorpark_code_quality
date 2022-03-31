<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnswersResource extends JsonResource
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
            'answer' => $this->answer,
            'created_by' => $this->user ? $this->user->first_name." ".$this->user->last_name  : null,
            'image' => ($this->resource instanceof User) ?  url('storage/images/user/'.strtolower($this->user->details->gender).".jpg") : url('storage/images/user/'.strtolower($this->user->details->gender).".jpg"),
            'created_at' => getDateF($this->created_at),
            'created_time' => getTimeF($this->created_at),
            'best_answer' => $this->best_answer ? "Yes"  : "No",
			'library' => ($this->library)?new LibraryResource($this->library):null,
        ];
    }
}
