<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageListSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $personalList = Conversation::whereIn('member_ids',[auth()->id()])->where(['type' => 'personal', 'module'=>$request->module])->orderBy('last_message_at','desc')->get();
        $groupList = Conversation::whereIn('member_ids',[auth()->id()])->where(['type' => 'group', 'module'=>$request->module])->orderBy('last_message_at','desc')->get();

        return [
            'personal_Lists' => $personalList->isNotEmpty() ? PersonalMessageListResource::collection($personalList) : null,
            'group_list' => $groupList->isNotEmpty() ? GroupMessageListResource::collection($groupList) : null,
        ];
    }
}
