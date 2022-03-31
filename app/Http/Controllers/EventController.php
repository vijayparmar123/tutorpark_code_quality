<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\AddEarningTransactionJob;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Http\Resources\EventResource;
use App\Mail\EventNotification;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:list,upcoming,history'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $eventData = Event::time($request->type)->get();
            // $eventData = Event::where('from_date','>=',Carbon::now())->Orderby('from_date', 'asc')->get();
            return EventResource::collection($eventData)->additional([ "error" => false, "message" => 'Here is all Events data']);
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
            'title' => 'required',
            'topic' => 'required',
            'description' => 'required',
            'mode' => 'required',
            'price' => 'required|numeric',
            'target_audience' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'from_time' => 'required',
            'to_time' => 'required',
            'image'=>'required|mimes:jpg,bmp,png,jpeg,svg|max:100000',
			'library_id' => 'filled|exists:libraries,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
		
        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $storeEvent = Event::create($request->except(['image']));

            if($request->has('image')){
                $image = $this->uploadFile($request->image,'images/event/');
                if($image != false){
                    $storeEvent->image = $image;
                }
            }

            if($storeEvent->save()) {
                $this->setResponse(false, 'Event added successfully.');
                return response()->json($this->_response); 
            } 

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:events,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $eventData =  Event::find($request->id);
            return (new EventResource($eventData))->additional(["error" => false, "message" => 'Specific single Event data']);
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:events,_id',
            'title' => 'required',
            'title' => 'required',
            'topic' => 'required',
            'description' => 'required',
            'mode' => 'required',
            'price' => 'required|numeric|gt:0',
            'target_audience' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'from_time' => 'required',
            'to_time' => 'required',
            'image'=>'required|mimes:jpg,bmp,png,jpeg,svg|max:100000',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $eventUpdate = Event::find($request->id);
            $eventUpdate->update($request->except(['image']));

            if ($request->has('image')) {

                $eventImage = $this->uploadFile($request->image, 'images/event/');
                if ($eventImage != false) {
                    $eventUpdate->image = $eventImage;
                }
            }

            if($eventUpdate->update()) {
                $this->_response['data'] = $eventUpdate;
                $this->setResponse(false, 'Event data updated successfully.');
                return response()->json($this->_response);
            } 

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:events,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $eventData =  Event::find($request->id);

            if($eventData->delete()) {
                $this->setResponse(false, 'Event data deleted successfully.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Find events created by specific user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function myEvents(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'created_by' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $eventData =  Event::where(['created_by'=>$request->created_by])->Orderby('created_at', 'desc')->get();
            return EventResource::collection($eventData)->additional([ "error" => false, "message" => 'Here is my Events data']);
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Upcoming Events from today to next 3 days
     *
     */
    // public function upComingEvents(Request $request)
    // {
    //     try {
    //         $date = Carbon::now();
    //         $nextDate = Carbon::now()->addDays(2)->endOfDay();
            
    //         $eventData =  Event::where('from_date','>=',$date)->where('from_date','<=',$nextDate)->Orderby('from_date', 'asc')->get();
    //         return EventResource::collection($eventData)->additional([ "error" => false, "message" => 'Here is all upcoming events data']);
            
    //     } catch (\Exception $e) {
    //         $this->setResponse(true, $e->getMessage());
    //         return response()->json($this->_response, 500);
    //     }
    // }

    /**
     * Events history from now to previous time
     *
     */
    // public function eventHistory(Request $request)
    // {
    //     try {

    //         $eventData =  Event::where('from_date','<',Carbon::now())->Orderby('from_date', 'asc')->get();
    //         return EventResource::collection($eventData)->additional([ "error" => false, "message" => 'Here is all past events data']);
            
    //     } catch (\Exception $e) {
    //         $this->setResponse(true, $e->getMessage());
    //         return response()->json($this->_response, 500);
    //     }
    // }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function attendEvent(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:events,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $event = Event::find($request->id);
            
            if($event->attendees->contains(auth()->id()))
            {
                $this->setResponse(false, "You have already attended this event.");
                return response()->json($this->_response, 200);
            }

            $event->attendees()->attach([auth()->id()]);
            $transaction = [
                'paid_to' => $event->created_by,
                'paid_from' => auth()->id(),
                'date' => Carbon::now(),
                'payment_mode' => 'cash',
                'transaction_id' => (string) Str::uuid(),
                'amount' => $event->price,
                'payment_status' => 'paid',
            ];

            /** add payment transaction and commission & final amount **/
            dispatch(new AddEarningTransactionJob($event,$transaction));

            $this->setResponse(false, 'Event attended successfully.');
            return response()->json($this->_response, 200); 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function makeEventFavourite(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:events,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $event = Event::find($request->id);
            
            if($event->favouriteUsers->contains(auth()->id()))
            {
                $this->setResponse(false, "You have already save this event to calendar.");
                return response()->json($this->_response, 200);
            }

            $event->favouriteUsers()->attach([auth()->id()]);
            
            $this->setResponse(false, 'Event saved to calendar successfully.');
            return response()->json($this->_response, 200); 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/**
     * Send event notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendInvitation(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'event_id' => 'required|exists:events,_id',
            'emails' => 'required|array',
            'emails.*' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $event = Event::find($request->event_id);
            
            $mailData = [
                'event_name' => $event->title,
                'schedule_time' => date("d-m-Y g:i a",strtotime($event->from_date)),
                'host_email' => $event->speaker->email,
                'host_name' => $event->speaker->full_name,
                'host_mobile' => $event->speaker->details->phone
            ];
            
            foreach($request->emails as $recipient)
            {
                Mail::to($recipient)->queue(new EventNotification($mailData));
            } 
            
            $this->setResponse(false, 'Invitation sent successfully.');
            return response()->json($this->_response, 200); 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
