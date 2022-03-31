<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonalMessageListResource extends JsonResource
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
        $sender = $this->members()->whereNotIn('_id',[auth()->id()])->first();
        $lastMessage = $this->messages()->whereNotIn('deleted_by',[auth()->id()])->whereNotIn('read_by',[auth()->id()])->orderBy('created_at','desc')->first();

        return [
            'conversation_id' => $this->id,
            'name' => $sender ? $sender->full_name : null,
            'email' => $sender ? $sender->email : null,
            'image' => $sender ? url('storage/images/user/'.strtolower($sender->details->gender).".jpg") : null,
            'unread_messages' => $unReadMessagesCount,
            'last_message_at' => $this->last_message_at->diffForhumans(null,0,1),
            'last_message' => ($lastMessage) ? $lastMessage->body : null,
        ];
    }
}
