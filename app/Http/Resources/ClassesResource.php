<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassesResource extends JsonResource
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
            'name' => $this->name,
            'syllabus_name' => $this->syllabus ? new SyllabusResource($this->syllabus) : null,
            'level_name' => $this->level ? new LevelResource($this->level) : null,
            'created_by' => $this->user ? $this->user->full_name  : null,
            'status'=> $this->status ? $this->status : null,
            'description'=> $this->description ? $this->description : null,
            'created_at'=> date("d-m-Y",strtotime($this->created_at))
        ];
    }
}
