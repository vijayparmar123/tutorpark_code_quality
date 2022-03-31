<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
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
            'class_name' => $this->class_name,
            'syllabus' => ($this->syllabus)?$this->syllabus->name:null,
            'standard' => ($this->class)?$this->class->name:null,
            'description' => ($this->description)?$this->description:null,
            'image' => ($this->image) ?  url('storage/' . $this->image) : url('storage/images/class/class.jpg'),
            'created_by' => ($this->createdBy)?$this->createdBy->full_name:null,
            // 'divisions' => ($this->divisions)?ClassDivisionResource::collection($this->divisions):null,
        ];
    }
}
