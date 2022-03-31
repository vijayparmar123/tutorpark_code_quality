<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class LibraryResource extends JsonResource
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
            'id'=>$this->id,
            'name'=>$this->name,
            'syllabus_id'=> $this->syllabus->_id,
            'syllabus_name'=> $this->syllabus->name,
            'class_id'=>($this->class)?$this->class->_id:null,
            'class_name'=>($this->class)?$this->class->name:null,
            'subject_id'=>$this->subject->_id,
            'subject_name'=>$this->subject->name,
            'description'=>$this->description,
            'image'=> $this->image ? url('storage/' . $this->image) : null,
            'attachment'=> $this->attachment ? url('storage/' . $this->attachment) : null,
            'creator_mobile_number'=> $this->creator->details->phone,
            'comments'=> ($this->comments)?CommentResource::collection($this->comments):null,
        ];
    }
}
