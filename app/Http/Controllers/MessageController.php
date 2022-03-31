<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Events\PusherMessageSend;
use App\Facades\CreateDPWithLetter;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GroupDetailsResource;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageListSectionResource;

class MessageController extends Controller
{
    public function memberMessageList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|in:chat,message',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            // $members = User::whereNotIn('email',[auth()->user()->email])->get();
            // return (MessageMemberListResource::collection($members))->additional(['error' => false, 'message' => null]);
            return (new MessageListSectionResource(auth()->user()))->additional(['error' => false, 'message' => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'filled|exists:conversations,_id',
            'module' => 'required|in:chat,message',
            'to' => 'required_without:conversation_id|email|exists:users,email',
            'body' => 'required|max:200',
            "attachments" => "filled|array",
            "attachments.*" => "filled|mimes:doc,jpg,jpeg,png,docx,csv,pdf,txt,ppt,pptm,pptx,xls,xlsx|max:2048",
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            if(!$request->has('conversation_id'))
            {
                $conversation = new Conversation;
                $conversation->module = $request->module;
                $conversation->type = 'personal';
                $conversation->created_by = auth()->id();
                $conversation->save();
                
                $user = User::where('email', $request->to)->first();
                $conversation->members()->attach([auth()->id(),$user->id]);
                
                $request->merge(['conversation_id' => $conversation->id]);
            }else{
                $conversation = Conversation::find($request->conversation_id);
            }
            
            $files = $this->addFileAttachments($request->attachments, 'chat/attachments/');
            
            $conversation->messages()->create([
                'body' => $request->body,
                'attachments' => $files,
                'read_by' => [],
            ]);
            
            $conversation->last_message_at = new DateTime();
            $conversation->save();
            
            
            $recentMessage = $conversation->messages()->orderBy('created_at','desc')->first();
            $payLoad = new MessageResource($recentMessage);
            broadcast(new PusherMessageSend($payLoad,$conversation->id))->toOthers();
            
            $this->setResponse(false, 'Message Sent Successfully.');
            return response()->json($this->_response, 200);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function createGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => "required|unique:conversations,name|regex:/^[\pL\pN\s\-\_\,\?\&\.\(\)\#\@']+$/u|max:50",
            'module' => 'required|in:chat,message',
            'members' => 'required',
            'members.*' => 'filled|email|exists:users,email',
            'logo' => 'filled|mimes:jpg,bmp,png,jpeg,svg|max:100000',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            
            $conversation = new Conversation;
            $conversation->type = 'group';
            $conversation->module = $request->module;
            $conversation->name = $request->name;
            $conversation->last_message_at = null;
            
            //If request has logo then set it, else generate logo from group name
            if($request->has('logo')){
                $logo = $this->uploadFile($request->logo,'group/logo/');
                if($logo != false){
                    $conversation->logo = $logo;
                }
            }else{
                $imageName = 'group/logo/' . getUniqueStamp() . '.png';
                $path = 'public/' . $imageName;
                $img = CreateDPWithLetter::create($request->name);
                Storage::put($path, $img->encode());
                $conversation->logo = $imageName;
            }
                        
            $conversation->created_by = auth()->id();
            $conversation->save();
            
            $conversation->members()->attach([auth()->id()]);
            foreach($request->members as $email){
                $user = User::where('email', $email)->first();
                if($user){
                    $conversation->members()->attach($user->id);
                }
            }
            
            $this->setResponse(false, 'Group Created successfully.');
            return response()->json($this->_response, 200);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function getMessageHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $conversation = Conversation::find($request->conversation_id);
            // Cache::flush();
            // if(!Cache::has('conversations')){
            //     // Cache::forever('conversations',Conversation::find($request->conversation_id));
            //     Cache::forever('conversations',Conversation::find($request->conversation_id)->with("messages")->first());
            // }
            
            // $conversation = Cache::get('conversations');
            return (new ConversationResource($conversation))->additional(['error' => false, 'message' => null]);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function markAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $conversation = Conversation::find($request->conversation_id);
            
            $message = $conversation->othersMessages()->push('read_by',auth()->id());
            // $message->push('read_by',auth()->id());
            
            $this->setResponse(false, 'Marked as Read Successfully.');
            return response()->json($this->_response, 200);            
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function addMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'members' => 'required|array',
            'members.*' => 'required|exists:users,email',
            'conversation_id' => 'required|exists:conversations,_id'
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);

            $users = User::whereIn('email', $request->members)->get()->pluck('id')->toArray();
            $conversation->members()->attach($users);

            $this->setResponse(false, 'Member Added Successfully.');
            return response()->json($this->_response, 200);   

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function removeMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'members' => 'required|array',
            'members.*' => 'required|exists:users,email',
            'conversation_id' => 'required|exists:conversations,_id'
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);

            $users = User::whereIn('email', $request->members)->get()->pluck('id')->toArray();
            $conversation->members()->detach($users);

            $this->setResponse(false, 'Member removed Successfully.');
            return response()->json($this->_response, 200);   

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function editGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "conversation_id" => 'required|exists:conversations,_id',
            "name" => "required|regex:/^[\pL\pN\s\-\_\,\?\&\.\(\)\#\@']+$/u|max:100",
            // 'members' => 'required',
            'members.*' => 'filled|email|exists:users,email',
            'logo' => 'filled|mimes:jpg,bmp,png,jpeg,svg|max:100000',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);
            $conversation->name = $request->name;

            //If request has logo then set it, else generate logo from group name
            if($request->has('logo')){
                $logo = $this->uploadFile($request->logo,'group/logo/');
                if($logo != false){
                    $conversation->logo = $logo;
                }
            }else{
                if($conversation->isDirty('name')){
                    $imageName = 'group/logo/' . getUniqueStamp() . '.png';
                    $path = 'public/' . $imageName;
                    $img = CreateDPWithLetter::create($request->name);
                    Storage::put($path, $img->encode());
                    $conversation->logo = $imageName;
                }
            }
            
            if($request->has('members')){
                $members = $request->members;
                $members[] = $conversation->owner->email;
                $conversation->sync($members,$conversation->members()->pluck('email')->toArray());
            }

            $conversation->save();

            $this->setResponse(false, "Group Updated Successfully.");
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function leaveGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);
            $conversation->members()->detach(auth()->id());

            $this->setResponse(false, "Left From Group Successfully.");
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function removeGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);
            
            if($conversation->delete())
            {
                $this->setResponse(false, "Group deleted Successfully.");
                return response()->json($this->_response, 200);
            }
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function clearMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);
            $conversation->messages->each->clean();

            $this->setResponse(false, "Message History Cleared.");
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function deleteConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::find($request->conversation_id);
            $conversation->messages->each->clean();

            if($conversation->delete())
            {
                $this->setResponse(false, "Conversation deleted Successfully.");
                return response()->json($this->_response, 200);
            }

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function groupDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,_id,type,group',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $conversation = Conversation::where('type','group')->find($request->conversation_id);
            
            return (new GroupDetailsResource($conversation))->additional(['error' => false, 'message' => null]);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	public function directMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|email|exists:users,email',
            'message' => 'required',
            "attachments" => "filled|array",
            "attachments.*" => "filled|mimes:doc,jpg,jpeg,png,docx,csv,pdf,txt,ppt,pptm,pptx,xls,xlsx|max:2048",
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
			$to_user_id = User::where(['email'=>$request->to])->first()->_id;
			$conversation_exist = Conversation::whereIn('member_ids', array(auth()->user()->_id))->whereIn('member_ids', array($to_user_id))->where(['type'=>'personal'])->count();
			
            if($conversation_exist)
            {
				$conversation = Conversation::whereIn('member_ids', array(auth()->user()->_id))->whereIn('member_ids', array($to_user_id))->where(['type'=>'personal'])->first();
		
            }else{
                $conversation = new Conversation;
                $conversation->module = 'message';
                $conversation->type = 'personal';
                $conversation->created_by = auth()->id();
                $conversation->save();
                
                $user = User::where('email', $request->to)->first();
                $conversation->members()->attach([auth()->id(),$user->id]);
                
                $request->merge(['conversation_id' => $conversation->id]);
            }
            
            $files = $this->addFileAttachments($request->attachments, 'chat/attachments/');
            
            $conversation->messages()->create([
                'body' => $request->message,
                'attachments' => $files,
                'read_by' => [],
            ]);
            
            $conversation->last_message_at = new DateTime();
            $conversation->save();
            
            
            $recentMessage = $conversation->messages()->orderBy('created_at','desc')->first();
            $payLoad = new MessageResource($recentMessage);
            broadcast(new PusherMessageSend($payLoad,$conversation->id))->toOthers();
            
            $this->setResponse(false, 'Message Sent Successfully.');
            return response()->json($this->_response, 200);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
