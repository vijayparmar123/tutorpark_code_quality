<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $messages = $this->messages()->whereNotIn('deleted_by', [auth()->id()])->get();
        
        return [
            "conversation_id" => $this->id,
            'created_at' => $this->created_at->diffForhumans(null,0,1),
            "author" => $this->author->name,
            "members" => $this->members ? $this->members->pluck('email') : null,
            "messages" => $messages ? MessageResource::collection($messages) : null,
            "type" => $this->type,
            'image' => $this->logo ? url('storage/'.$this->logo) : null,
            'name' => $this->name,
            'total_members' => $this->members->isNotEmpty()?$this->members->count(). ' Members':null,
        ];
    }
}
