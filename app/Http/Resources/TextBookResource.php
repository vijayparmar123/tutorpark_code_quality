<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TextBookResource extends JsonResource
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
            'subject' => $this->subject ? $this->subject->name : null,
            'syllabus' => $this->syllabus ? $this->syllabus->name : null,
            'class' => $this->class ? $this->class->name : null,
            'subject_id' => $this->subject ? $this->subject->id : null,
            'syllabus_id' => $this->syllabus ? $this->syllabus->id : null,
            'class_id' => $this->class ? $this->class->id : null,
            'book_name' => $this->book_name,
            'description' => $this->description,
            'resource_type' => $this->resource_type,
            'external_link' => $this->external_link,
            'attachment' => $this->attachment ? url('storage/' . $this->attachment) : null,
            'image' => $this->image ? url('storage/' . $this->image) : null,
        ];
    }
}
