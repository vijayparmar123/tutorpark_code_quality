<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $urlPrefix = function($file){
            return url(Storage::url($file));
        };

        if($this->created_at->isToday()){
            $createdAt = "Today {$this->created_at->format('h:i A')}";
        }elseif($this->created_at->isYesterday()){
            $createdAt = "Yesterday {$this->created_at->format('h:i A')}";
        }else{
            $createdAt = $this->created_at->format('M,d Y h:i A');
        }

        $readBy = !empty($this->read_by) ? User::whereIn('_id',$this->read_by)->get()->pluck('email') : null;

        return [
            "id" => $this->id,
            "body" => $this->body,
            "attachments" => $this->attachments ? array_map($urlPrefix, $this->attachments) : null,
            "author" => $this->author->name,
            "author_email" => $this->author->email,
            "author_name" => $this->author->full_name,
            "author_image" => url('storage/'.$this->author->image),
            "created_at" => $createdAt,
            "iam_author" => auth()->id() === $this->author->id,
            "read_by" => $readBy
        ];
    }
}
