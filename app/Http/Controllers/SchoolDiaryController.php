<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use DateTime;
use App\Models\User;
use App\Models\SchoolDiary;
use App\Models\SchoolDiarySubject;
use App\Models\ClassDivision;
use App\Models\Conversation;
use App\Http\Resources\MessageResource;
use App\Events\PusherMessageSend;
use App\Models\DivisionSubjectTeacher;
use App\Http\Resources\SchoolDiaryResource;
use App\Http\Resources\MyDiaryResource;
use App\Http\Resources\DivisionSubjectResource;
use App\Http\Resources\ClassDivisionBasicDetailsResource;

class SchoolDiaryController extends Controller
{
    /**
     * Division list.
     *
     * @return \Illuminate\Http\Response
     */
    public function divisionSubject(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        $role = auth()->user()->getRole();
        try {
            
            $division = ClassDivision::find($request->division_id);

            switch ($role) {
                case 'school-student':
                    return DivisionSubjectResource::collection($division->subjects)->additional([ "error" => false, "message" => 'Here is all subjects of division']);
                break;
                case 'school-tutor':
                    return DivisionSubjectResource::collection($division->subjects()->where(['teacher_id'=>auth()->user()->id])->get())->additional([ "error" => false, "message" => 'Here is all subjects of division']);
                break;
                case 'school-admin':
                    return DivisionSubjectResource::collection($division->subjects)->additional([ "error" => false, "message" => 'Here is all subjects of division']);
                break;
            }
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Division list.
     *
     * @return \Illuminate\Http\Response
     */
    public function myDivisionBasicDetails()
    {
        $role = auth()->user()->getRole();
        try {
            switch ($role) {
                case 'school-student':
                    $divisionIds = auth()->user()->getDivisionIds();
                    $session = ClassDivision::whereIn('_id', $divisionIds)->Orderby('created_at', 'desc')->get();
                break;
                case 'school-tutor':
                    $divisionIds = auth()->user()->getDivisionIds();
                    $session = ClassDivision::whereIn('_id', $divisionIds)->Orderby('created_at', 'desc')->get();
                break;
                case 'school-admin':
                    $session = ClassDivision::Orderby('created_at', 'desc')->get();
                break;
            }

            return ClassDivisionBasicDetailsResource::collection($session)->additional([ "error" => false, "message" => 'Here is all division data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add Diary.
     *
     * @return \Illuminate\Http\Response
     */
    public function addDiary(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'class_work' => 'required',
            'class_work_attachment' => 'filled|mimes:jpg,bmp,png,jpeg,svg,pdf,doc,csv,xlsx,xls,docx,ppt,odt,ods,odp|max:100000',
            'home_work' => 'required',
            'home_work_attachment' => 'filled|mimes:jpg,bmp,png,jpeg,svg,pdf,doc,csv,xlsx,xls,docx,ppt,odt,ods,odp|max:100000',
            'tomorrow_topics' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {

            $request->request->add(['created_by' => auth()->user()->id,'user_id' => auth()->user()->id, 'date' => date('Y-m-d')]);
            $class = SchoolDiary::create($request->except(['class_work_attachment','home_work_attachment']));

            $details = $class->details()->create([
                'subject_id' => $request->subject_id,
                'class_work' => $request->class_work,
                'home_work' => $request->home_work,
                'tomorrow_topics' => $request->tomorrow_topics,
            ]);

            if ($request->has('class_work_attachment')) {
                $class_work_attachment = $this->uploadFile($request->class_work_attachment, 'schooldiary/attachment');
                if ($class_work_attachment != false) {
                    $details->class_work_attachment = $class_work_attachment;
                }
            }

            if ($request->has('home_work_attachment')) {
                $home_work_attachment = $this->uploadFile($request->home_work_attachment, 'schooldiary/attachment');
                if ($home_work_attachment != false) {
                    $details->home_work_attachment = $home_work_attachment;
                }
            }

            $details->save();

            $this->setResponse(false, 'Diary created successfully.');
            return response()->json($this->_response); 

        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Diary.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDiary(request $request)
    {
        $validator = Validator::make($request->all(), [
            'diary_id' => 'required|exists:school_diaries,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'class_work' => 'required',
            'class_work_attachment' => 'filled|mimes:jpg,bmp,png,jpeg,svg,pdf,doc,csv,xlsx,xls,docx,ppt,odt,ods,odp|max:100000',
            'home_work' => 'required',
            'home_work_attachment' => 'filled|mimes:jpg,bmp,png,jpeg,svg,pdf,doc,csv,xlsx,xls,docx,ppt,odt,ods,odp|max:100000',
            'tomorrow_topics' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            
            $diaryDetail = SchoolDiarySubject::where(['school_diary_id' => $request->diary_id, 'subject_id' => $request->subject_id])->first();
            $diaryDetail->class_work = $request->class_work;
            $diaryDetail->home_work = $request->home_work;
            $diaryDetail->tomorrow_topics = $request->tomorrow_topics;

            if ($request->has('class_work_attachment')) {
                $class_work_attachment = $this->uploadFile($request->class_work_attachment, 'schooldiary/attachment');
                if ($class_work_attachment != false) {
                    $diaryDetail->class_work_attachment = $class_work_attachment;
                }
            }

            if ($request->has('home_work_attachment')) {
                $home_work_attachment = $this->uploadFile($request->home_work_attachment, 'schooldiary/attachment');
                if ($home_work_attachment != false) {
                    $diaryDetail->home_work_attachment = $home_work_attachment;
                }
            }

            $diaryDetail->save();

            $this->setResponse(false, 'Diary updated successfully.');
            return response()->json($this->_response); 

        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * My Diary - Date wise and all.
     *
     * @return \Illuminate\Http\Response
     */
    public function myDiary(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'date' => 'filled|date|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $date = $request->has('date')?$request->get('date'):null;
            $role = auth()->user()->getRole();

            switch ($role) {
                case 'school-student':
                    $diary = SchoolDiary::where(['division_id' => $request->division_id,'created_by' => auth()->user()->id])->when($date, function($query, $date){
                        return $query->where(['date' => $date]);
                    })->Orderby('created_at', 'desc')->get();
                break;
                case 'school-tutor':
                    $diary = SchoolDiary::where(['division_id' => $request->division_id,'created_by' => auth()->user()->id])->when($date, function($query, $date){
                        return $query->where(['date' => $date]);
                    })->Orderby('created_at', 'desc')->get();
                break;
                case 'school-admin':
                    $diary = SchoolDiary::where(['division_id' => $request->division_id])->when($date, function($query, $date){
                        return $query->where(['date' => $date]);
                    })->Orderby('created_at', 'desc')->get();
                break;
            }
            
            return MyDiaryResource::collection($diary)->additional([ "error" => false, "message" => 'Here is my diary data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * School Diary - Date wise and all.
     *
     * @return \Illuminate\Http\Response
     */
    public function schoolDiary(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'date' => 'filled|date|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $date = $request->has('date')?$request->get('date'):null;
            $diary = SchoolDiary::where(['division_id' => $request->division_id])->when($date, function($query, $date){
                return $query->where(['date' => $date]);
            })->groupBy('division_id','date')->Orderby('created_at', 'desc')->get();
            
            return SchoolDiaryResource::collection($diary)->additional([ "error" => false, "message" => 'Here is school diary data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Share diary in message
     *
     * @return \Illuminate\Http\Response
     */
    public function shareInMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'diary_id' => 'required|exists:school_diaries,_id',
            'to' => 'required|array',
            'to.*' => 'required|email|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $diary = SchoolDiary::find($request->diary_id);
            if($diary->details)
            {
                // Class work
                $message = '<h5>Date of Diary : '.date('d-m-Y',strtotime($diary->date)).'</h5>';
                $message .= 'Class Work : '.$diary->details->class_work.'<br>';
                if($diary->details->class_work_attachment)
                {
                    $class_work_attachment_url = ($diary->details->class_work_attachment) ?  url('storage/' . $diary->details->class_work_attachment):'#';
                    $message .= '<a href='.$class_work_attachment_url.' target="_blank">View Class Work Attachment</a><br>';
                }

                //Home work
                $message .= 'Home Work : '.$diary->details->home_work.'<br>';
                if($diary->details->home_work_attachment)
                {
                    $home_work_attachment_url = ($diary->details->home_work_attachment) ?  url('storage/' . $diary->details->home_work_attachment):'#';
                    $message .= '<a href='.$home_work_attachment_url.' target="_blank">View Home Work Attachment</a><br>';
                }

                // Tomorrow topics
                $message .= 'Tomorrow Topics : '.$diary->details->tomorrow_topics.'<br>';
            
            }else{
                $message = 'null';
            }
            
            foreach($request->to as $email)
            {
                $to_user_id = User::where(['email'=>$email])->first()->_id;
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
                    
                    $user = User::where('email', $email)->first();
                    $conversation->members()->attach([auth()->id(),$user->id]);
                    
                    $request->merge(['conversation_id' => $conversation->id]);
                }
                
                $files = $this->addFileAttachments($request->attachments, 'chat/attachments/');
                
                $conversation->messages()->create([
                    'body' => $message,
                    'attachments' => $files,
                    'read_by' => [],
                ]);
                
                $conversation->last_message_at = new DateTime();
                $conversation->save();
                
                
                $recentMessage = $conversation->messages()->orderBy('created_at','desc')->first();
                $payLoad = new MessageResource($recentMessage);
                broadcast(new PusherMessageSend($payLoad,$conversation->id))->toOthers();
            }
            $this->setResponse(false, 'Message Sent Successfully.');
            return response()->json($this->_response, 200);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
