<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClassDivisionBasicDetailsResource extends JsonResource
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
            'is_disabled' => (auth()->user()->isDisabledInDivision($this->_id))?true:false,
            'name' => ($this->division)?$this->division->name:null,
            'standard' => ($this->class)?(($this->class->class)?$this->class->class->name:null):null,
            'class' => ($this->class)?new SchoolClassResource($this->class):null, 
            'students' => ($this->students)?DivisionStudentResource::collection($this->students):null, 
            'subjectLeaders' => ($this->subjectLeaders)?DivisionSubjectLeadersResource::collection($this->subjectLeaders):null, 
        ];
    }
}
