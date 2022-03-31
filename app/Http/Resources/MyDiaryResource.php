<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MyDiaryResource extends JsonResource
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
            'division_id' => $this->division_id,
            'division_name' => ($this->division)?$this->division->name:null,
            'class_name' => ($this->division)?$this->division->class->class_name:null,
            'date' => date('d-m-Y',strtotime($this->date)),
            'creator_id' => $this->created_by,
            'creator_name' => ($this->createdBy)?$this->createdBy->full_name:null,
            'details' => ($this->details)?new DiaryDetailsResource($this->details):null,
        ];
    }
}
