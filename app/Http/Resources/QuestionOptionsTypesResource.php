<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionsTypesResource extends JsonResource
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
            'left_option' => QuestionOptionsResource::collection($this->options()->where(['type' => 'left'])->get()),
            'right_option' => QuestionOptionsResource::collection($this->options()->where(['type' => 'right'])->get()->shuffle()),
            ];
    }
}
