<?php

namespace App\Http\Controllers;
use App\Models\Timeline;
use App\Models\Library;
use App\Models\Event;
use App\Models\SchoolDiary;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TimelineResource;
use App\Jobs\PostComment;
use App\Jobs\PostToTimeline;
use App\Jobs\DisableTimeline;
use Carbon\Carbon;
use DB;

class TimelineController extends Controller
{
    /**
     * Disply timeline Record of others users
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $timelineData = Timeline::where("created_by","!=",auth()->user()->id)->Orderby('created_at', 'desc')->get();
            
            return TimelineResource::collection($timelineData)->additional([ "error" => false, "message" => 'Here is other users Timeline data']);
        } catch (Exception $e) {
        $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Disply timeline Record of others users
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        try {
            $timelineData = Timeline::Orderby('created_at', 'desc')->get();
            
            return TimelineResource::collection($timelineData)->additional([ "error" => false, "message" => 'Here is all Timeline data']);
        } catch (Exception $e) {
        $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
 
    /**
     * Disply timeline Record of others users
     *
     * @return \Illuminate\Http\Response
     */
    public function myTimeline()
    {
        try {
            $timelineData = Timeline::where(["created_by"=>auth()->user()->id])->Orderby('created_at', 'desc')->get();
            
            return TimelineResource::collection($timelineData)->additional([ "error" => false, "message" => 'Here is my Timeline data']);
        } catch (Exception $e) {
        $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'audiance' => 'required',
            'description' => 'required',
            'image' => 'nullable|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'video' => 'nullable|mimes:mp4,mov,wmv,mkv,avi|max:100000'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
       
        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $storeEvent = Timeline::create($request->except(['image','video']));

            if($request->has('image')){
                $image = $this->uploadFile($request->image,'images/timeline/');
                if($image != false){
                    $storeEvent->image = $image;
                }
            }

            if($request->has('video')){
                $video = $this->uploadFile($request->video,'videos/timeline/');
                if($video != false){
                    $storeEvent->video = $video;
                }
            }

            if($storeEvent->save()) {
                $this->setResponse(false, 'Timeline posted successfully.');
                return response()->json($this->_response); 
            } 

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Disply timeline single record
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timelineData = Timeline::find($request->id);
           
            return (new TimelineResource($timelineData))->additional([ "error" => false, "message" => 'Here is specific Timeline data']);
        } catch (Exception $e) {
        $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Library.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:timelines,_id',
            'audiance' => 'required',
            'description' => 'required',
            'image' => 'nullable|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'video' => 'nullable|mimes:mp4,mov,wmv,mkv,avi|max:100000'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $timelineUpdate = Timeline::find($request->id);
            $timelineUpdate->update($request->except(['image','video']));

            if($request->has('image')){
                $image = $this->uploadFile($request->image,'images/timeline/');
                if($image != false){
                    $timelineUpdate->image = $image;
                }
            }

            if($request->has('video')){
                $video = $this->uploadFile($request->video,'videos/timeline/');
                if($video != false){
                    $timelineUpdate->video = $video;
                }
            }

            if($timelineUpdate->update()) {
                $this->_response['data'] = $timelineUpdate;
                $this->setResponse(false, 'Timeline data updated successfully.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Delete Library
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->id);
            if($timeline->delete()) {
                $this->setResponse(false, 'Timeline deleted from successfully.');
                return response()->json($this->_response);
            } 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Like timeline
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function like(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->id);

            if($timeline->likeBy->contains(auth()->id()))
            {
                $timeline->likeBy()->detach(auth()->id());
                Timeline::where('_id', $request->id)
                ->decrement('like_count');
            }else{
                if($timeline->dislikeBy->contains(auth()->id()))
                {
                    $timeline->dislikeBy()->detach(auth()->id());
                    Timeline::where('_id', $request->id)
                    ->decrement('dislike_count');
                }

                $timeline->likeBy()->attach(auth()->id());
                Timeline::where('_id', $request->id)
                ->increment('like_count');
            }

            $this->setResponse(false, 'Timeline like status updated successfully.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Make timeline favourite
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function favourite(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->id);

            if($timeline->favouriteBy->contains(auth()->id()))
            {
                $timeline->favouriteBy()->detach(auth()->id());
            }else{
                $timeline->favouriteBy()->attach(auth()->id());
            }

            $this->setResponse(false, 'Timeline favourite status updated successfully.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    
    /**
     * Dislike timeline
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function dislike(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->id);

            if($timeline->dislikeBy->contains(auth()->id()))
            {
                $timeline->dislikeBy()->detach(auth()->id());
                Timeline::where('_id', $request->id)
                ->decrement('dislike_count');
            }else{
                if($timeline->likeBy->contains(auth()->id()))
                {
                    $timeline->likeBy()->detach(auth()->id());
                    Timeline::where('_id', $request->id)
                    ->decrement('like_count');
                }

                $timeline->dislikeBy()->attach(auth()->id());
                Timeline::where('_id', $request->id)
                ->increment('dislike_count');
            }

            $this->setResponse(false, 'Timeline dislike status updated successfully.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    /**
     * Abuse timeline
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function abuseTimeline(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->id);

            if($timeline->abuseBy->contains(auth()->id()))
            {
                $timeline->abuseBy()->detach(auth()->id());
                Timeline::where('_id', $request->id)
                ->decrement('abuse_count');
            }else{
                if($timeline->likeBy->contains(auth()->id()))
                {
                    $timeline->likeBy()->detach(auth()->id());
                    Timeline::where('_id', $request->id)
                    ->decrement('like_count');
                }
                
                $timeline->abuseBy()->attach(auth()->id());
                Timeline::where('_id', $request->id)
                ->increment('abuse_count');
                /** Post comment **/
                dispatch(new DisableTimeline($timeline));
            }

            $this->setResponse(false, 'Timeline abuse status updated successfully.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Comments for specific timeline
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:timelines,_id',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->id);
            
            $comment = [
                'body' => $request->comment,
                'datetime' => Carbon::now(),
                'commented_by' => auth()->user()->id
            ];
            
            /** Post comment **/
            dispatch(new PostComment($timeline,$comment));

            $this->setResponse(false, 'Comment posted successfully.');
            return response()->json($this->_response, 200);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/**
     * Repost timeline
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function repost(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'timeline_id' => 'required|exists:timelines,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $timeline =  Timeline::find($request->timeline_id);
            
            $storeEvent = Timeline::create([
				'audiance' => $timeline->audiance,
				'description' => $timeline->description,
				'image' => $timeline->image,
				'video' => $timeline->video,
				'is_reposted' => true,
				'reposted_id' => $request->timeline_id,
				'created_by' => auth()->user()->_id,
			]);

            $this->setResponse(false, 'Timeline posted successfully.');
            return response()->json($this->_response, 200);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/**
     * Post event and library to timeline
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function postToTimeline(Request $request)
    {		
		$rules = [
            'type' => 'required|in:library,event,schoolDiary'
        ];
		
		if ($request->type == "library") {
            $rules['type_id'] = 'required|exists:libraries,_id';
        }
		
		if ($request->type == "event") {
            $rules['type_id'] = 'required|exists:events,_id';
        }

        if ($request->type == "schoolDiary") {
            $rules['type_id'] = 'required|exists:school_diaries,_id';
        }
		
		$validator = Validator::make($request->all(), $rules);
		
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
			switch ($request->type) {
			  case "library":
				$library = Library::find($request->type_id);
				
				$data = [
					'audiance' => "public",
					'description' => ($library->description)?$library->description:null,
					'image' => ($library->image)?$library->image:null,
					'created_by' => auth()->user()->id
				];
				
				/** Post To Timeline **/
				dispatch(new PostToTimeline($library,$data));
			  break;
			  case "event":
				$event = Event::find($request->type_id);
				
				$data = [
					'audiance' => "public",
					'description' => ($event->description)?$event->description:null,
					'image' => ($event->image)?$event->image:null,
					'created_by' => auth()->user()->id
				];
				
				/** Post To Timeline **/
				dispatch(new PostToTimeline($event,$data));
			  break;
              case "schoolDiary":
				$diary = SchoolDiary::find($request->type_id);
				
				$data = [
					'audiance' => "public",
					'description' => $diary->division->class->class_name.' division '.$diary->division->name.' '.$diary->date.' date`s diary',
					'image' => ($diary->image)?$diary->image:null,
					'created_by' => auth()->user()->id
				];
				
				/** Post To Timeline **/
				dispatch(new PostToTimeline($diary,$data));
			  break;
			}

            $this->setResponse(false, 'Posted to timeline successfully.');
            return response()->json($this->_response, 200);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
