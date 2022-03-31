<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title' => $this->title,
            'topic' => $this->topic,
            'description' => $this->description,
            'mode' => $this->mode,
            'price' => $this->price,
            'target_audience' => $this->target_audience,
            'from_date' => date("d-m-Y",strtotime($this->from_date)),
            'to_date'=> date("d-m-Y",strtotime($this->to_date)),
            'from_time'=> date("g:i a",strtotime($this->from_time)),
            'to_time'=> date("g:i a",strtotime($this->to_time)),
            'calendar_from_date' => $this->from_date,
            'calendar_to_date' => $this->to_date,
            'image'=> $this->image ? url('storage/' . $this->image) : null,
            'speaker_name'=> $this->speaker->full_name,
            'speaker_email'=> $this->speaker->email,
            'speaker_mobile_number'=> $this->speaker->details->phone,
            'attendees'=> ($this->attendees)?$this->attendees->pluck('full_name'):NULL,
            'favouriteUsers'=> ($this->favouriteUsers_id)?$this->favouriteUsers_id:NULL,
            'transactions'=> $this->transactions,
			'library' => ($this->library)?new LibraryResource($this->library):null,
        ];
    }
}
