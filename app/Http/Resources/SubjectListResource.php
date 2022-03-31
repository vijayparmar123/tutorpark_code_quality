<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubjectListResource extends JsonResource
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
            'id'=> $this->id,
            'name'=> $this->name,
            'classes'=> !empty($this->classes) ? ClassesResource::collection($this->classes) : $this->classes,
            'description'=> $this->description,
            'active'=> $this->active,
            'created_at'=> date("d-m-Y",strtotime($this->created_at)),
        ];
    }
}
