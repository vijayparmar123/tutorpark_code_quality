<?php

namespace App\Http\Resources;

use Illuminate\Support\Collection;
use App\Models\FriendRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendRequestListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $received = FriendRequest::where('to', $this->email)->where('is_invited','<>',true)->orderBy('created_at','desc')->get();
        $sent = FriendRequest::where('from', $this->email)->where('is_invited','<>',true)->orderBy('created_at','desc')->get();

        $sent->map(function($sent){
            $sent['type'] = 'sent';
            return $sent;
        });
        
        $received->map(function($received){
            $received['type'] = 'received';
            return $received;
        });

        $sentRequest = $sent->count() > 0 ? FriendCardResource::listCollection($sent,'sender') : collect();
        $receivedRequest = $received->count() > 0 ? FriendCardResource::listCollection($received, 'receiver') : collect();

        if($sentRequest->isEmpty() || $receivedRequest->isEmpty()){
            $allRequests = $sentRequest->isEmpty() ? $receivedRequest : $sentRequest;
        }else{
            $allRequests = array_merge($sentRequest->collection->toArray(),$receivedRequest->collection->toArray());
        }
        $allRequests = Collection::make($allRequests);
        return $allRequests->sortByDesc('created_at');
    }

    // public function old()
    // {
    //     $receiver = FriendRequest::where('to', $this->email)->get();
    //     $sender = FriendRequest::where('from', $this->email)->get();
        
    //     return [
    //         "received" => $receiver->count() > 0 ? FriendCardResource::listCollection($receiver, 'receiver') : null,
    //         "sent" => $sender->count() > 0 ? FriendCardResource::listCollection($sender,'sender') : null
    //     ];
    // }
}
