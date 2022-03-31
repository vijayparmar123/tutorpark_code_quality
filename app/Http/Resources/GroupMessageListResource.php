<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupMessageListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $query = $this->messages()->whereNotIn('created_by',[auth()->id()])->whereNotIn('read_by',[auth()->id()]);
        $unReadMessagesCount = $query->count();
        $lastMessage = $this->messages()->whereNotIn('deleted_by',[auth()->id()])->whereNotIn('read_by',[auth()->id()])->orderBy('created_at','desc')->first();
        
        return [
            'conversation_id' => $this->id,
            'name' => $this->name,
            'image' => $this->logo ? url('storage/'.$this->logo) : null,
            'unread_messages' => $unReadMessagesCount,
            'last_message_at' => $this->last_message_at ?  $this->last_message_at->diffForhumans(null,0,1) : null,
            'last_message' => ($lastMessage) ? $lastMessage->body : null,
            'total_members' => $this->members->isNotEmpty()?$this->members->count(). ' Members':null,
            'members' => $this->members->isNotEmpty()?UserpluckResource::collection($this->members):null,
            'author' => new UserpluckResource($this->author),
        ];
    }
}
