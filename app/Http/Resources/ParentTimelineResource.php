<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParentTimelineResource extends JsonResource
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
            'audiance'=> $this->audiance, 
            'description'=> $this->description,
            'creator'=> $this->creator->full_name,
            'class' => $this->creator->details->preferredClasses->isNotEmpty()?$this->creator->details->preferredClasses->first()->name:null,
            'status'=> $this->status,
            'total_like'=> $this->like_count,
            'total_dislike'=> $this->dislike_count,
            'total_abuse'=> $this->abuse_count,
            'datetime'=> getDateTime($this->datetime),
            'image'=> $this->image ? url('storage/' . $this->image) : null,
            'video'=> $this->video ? url('storage/' . $this->video) : null,
            'likedBy'=> ($this->likeBy)?UserpluckResource::collection($this->likeBy):NULL,
            'dislikedBy'=> ($this->dislikeBy)?UserpluckResource::collection($this->dislikeBy):NULL,
            'abuseBy'=> ($this->abuseBy)?UserpluckResource::collection($this->abuseBy):NULL,
            'favouriteBy'=> ($this->favouriteBy)?UserpluckResource::collection($this->favouriteBy):NULL,
            'total_comments'=> ($this->comments)?$this->comments()->count():0,
            'comments'=> ($this->comments)?CommentResource::collection($this->comments):NULL, 
        ];
    }
}
