<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
		$linked_item = null;
		$linked_type = null;
		if($this->linkable_type == "App\Models\Library")
		{
			$linked_type = "library";
			$linked_item = new LibraryResource($this->linkable);
		}elseif($this->linkable_type == "App\Models\Event")
		{
			$linked_type = "event";
			$linked_item = new EventResource($this->linkable);
		}elseif($this->linkable_type == "App\Models\SchoolDiary")
		{
			$linked_type = "SchoolDiary";
			$linked_item = new MyDiaryResource($this->linkable);
		}else{
			$linked_item = null;
			$linked_type = null;
		}
		
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
			'is_reposted'=>($this->is_reposted)?$this->is_reposted:false, 
			'reposted_timeline'=>($this->repostedTimeline)?new ParentTimelineResource($this->repostedTimeline):null, 
			'reposted_timeline'=>($this->repostedTimeline)?new ParentTimelineResource($this->repostedTimeline):null, 
			'link_type'=>$linked_type, 
			'linked_item'=>$linked_item, 
        ];
    }
}
