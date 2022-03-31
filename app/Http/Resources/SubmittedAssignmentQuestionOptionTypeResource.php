<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmittedAssignmentQuestionOptionTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // dd($this->studentAssignmentSectionQuestionOptions);
        return [
            'left_option' => SubmittedAssignmentQuestionOptionResource::collection($this->studentAssignmentSectionQuestionOptions()->where(['type' => 'left'])->get()),
            'right_option' => SubmittedAssignmentQuestionOptionResource::collection($this->studentAssignmentSectionQuestionOptions()->where(['type' => 'right'])->get()),
            ];
    }
}
