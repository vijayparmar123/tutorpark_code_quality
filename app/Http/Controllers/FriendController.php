<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use App\Models\RejectedFriendRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\FriendCardResource;
use App\Http\Resources\AddFriendListResource;
use App\Http\Resources\FriendRequestListResource;

class FriendController extends Controller
{
    
    public function requestSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|email|exists:users,email',
            'message' => 'present|max:200',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            //Check if rejected
            $isRejected = RejectedFriendRequest::where(['rejected_by' => $request->to, 'rejected_to' => auth()->user()->email])->count();
            
            if($isRejected)
            {
                $this->setResponse(false, "User has blocked you for 6 months to sending further requests.");
                return response()->json($this->_response, 200);
            }

            $to = User::where('email',$request->to)->first();
            // if(auth()->user()->isBlockedMe()->where('user_id',$to->id)->exists())
            // {
            //     $this->setResponse(false, "User has blocked you from sending further requests.");
            //     return response()->json($this->_response, 200);
            // }

            $requestAlreadySent = FriendRequest::where("from" , auth()->user()->email)->where("to" , $request->to)->exists();

            if( ! $requestAlreadySent && ! auth()->user()->isFriend($to->id)) {
                FriendRequest::create([
                    "from" => auth()->user()->email,
                    "to" => $request->to,
                    "message" => $request->message,
                    "is_invited" => false,
                ]);
                $this->setResponse(false, "Request sent successfully.");

                if(auth()->user()->hasRole('student'))
                {
                    // Avail point for sending friend request
                    $pointData = [
                        'comment' => 'received points for sending friend request',
                        'transaction_type' => 'received',
                        'source_of_point' => 'friend_request'
                    ];
                    
                    auth()->user()->availPoints($pointData);
                }
            }else{
                $this->setResponse(false, "Request already pending or {$to->first_name} is a friend");
            }

            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function requestList()
    {
        try{
            return (new FriendRequestListResource(auth()->user()))->additional(["error" => false, "message" => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function accept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:friend_requests,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $request = FriendRequest::find($request->request_id);
            if (!$request->receiver->isFriend($request->sender->id))
            {
                $request->receiver->addAsFriend($request->sender);
            }
            $request->delete();

            $this->setResponse(false, 'Friend added successfully.');
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function reject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:friend_requests,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $request = FriendRequest::find($request->request_id);

            //Add in rejected request
            RejectedFriendRequest::create([
                'rejected_date' => date("Y-m-d H:i:s"),
                'rejected_to' => $request->from,
                'rejected_by' => $request->to,
                'created_by' => $request->to,
            ]);

            $request->delete();
            
            $this->setResponse(false, "Rejected successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function list()
    {
        try{
            return FriendCardResource::collection(auth()->user()->friends())->additional(["error" => false, "message" => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function block(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:friend_requests,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{

            $request = FriendRequest::find($request->request_id);
            if( ! $request->receiver->blockUser()->where('blocked_user',$request->sender->id)->exists() )
            {
                $request->receiver->blockUser()->create([
                    "blocked_user" => $request->sender->id,
                    "is_spam" => false,
                ]);
            }
            $request->delete();
            
            $this->setResponse(false, "Blocked successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }

    }

    public function unfriend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $endUser = User::where('email', $request->email)->first();
            auth()->user()->unfriend($endUser);

            $this->setResponse(false, "Removed from friend successfully.");
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function markRequestAsSpam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:friend_requests,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{

            $request = FriendRequest::find($request->request_id);
            if( ! $request->receiver->blockUser()->where('blocked_user',$request->sender->id)->exists() )
            {
                $request->receiver->blockUser()->create([
                    "blocked_user" => $request->sender->id,
                    "is_spam" => true,
                ]);
            }
            $request->delete();
            
            $this->setResponse(false, "Marked as Spam successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function nonFriendList()
    {
        try{

            $requestAlreadySent = FriendRequest::where("from" , auth()->user()->email)->pluck("to")->toArray();
            $friends = auth()->user()->friends()->pluck("email")->toArray();
            $exceptEmails = array_unique(array_merge($requestAlreadySent,$friends));

            // $users = User::role(['student','tutor'])->whereNotIn('email',$exceptEmails)->get();
            $users = User::whereNotIn('email',$exceptEmails)->get();
            return AddFriendListResource::collection($users)->additional([ "error" => false, "message" => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
