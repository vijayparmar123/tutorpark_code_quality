<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionListResource;
use App\Http\Resources\TuitionSessionResource;
use App\Http\Resources\TuitionAddStudentListResource;
use Exception;
use Illuminate\Http\Request;
use App\Models\Tuition;
use App\Models\Sessions;
use App\Models\User;
use App\Models\TutorTimeTable;
use App\Http\Resources\TutionsResource;
use App\Http\Resources\UserpluckResource;
use App\Http\Resources\EditAttendanceResource;
use App\Jobs\AddEarningJob;
use App\Jobs\PostPayment;
use App\Jobs\AddEarningTransactionJob;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TutionsController extends Controller
{
    /**
    * Tuitions Record List.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        try {
            if(auth()->user()->getRole() == 'student'){
                $tuitionsData = Tuition::whereNotIn('student_ids',[auth()->user()->details->id])->Orderby('created_at', 'desc')->get();
            }else{
                $tuitionsData = Tuition::Orderby('created_at', 'desc')->get();
            }
            return TutionsResource::collection($tuitionsData)->additional([ "error" => false, "message" => "Tuition list retrived successfully"]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    /**
    * Add New Data insert Tutions.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required', 'exists:classes,_id'],
            'subject_id' => ['required', 'exists:subjects,_id'],
            'title' => 'required:max:100',
            'description' => 'required|max:500',
            'mode_of_teaching' => 'required',
            'schedule_id' => 'required',
            'cost' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'image' => 'filled|image|mimes:jpg,jpeg,png|max:2048',
            'demo_video' => 'nullable|mimes:mp4,mov,wmv,mkv,avi|max:100040',
            'library_id' => 'filled|exists:libraries,_id',
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $request->request->add(['tutor_id' => auth()->user()->id]);
            
            $tutionsStore = Tuition::create($request->except(['image', 'demo_video']));
            
            if ($request->has('demo_video')) {
                
                $videoUrl = $this->uploadFile($request->demo_video, 'video/tution');
                if ($videoUrl != false) {
                    $tutionsStore->demo_video = $videoUrl;
                }
            }
            
            if($request->has('image')){
                $image = $this->uploadFile($request->image,'images/tution/');
                if($image != false){
                    $tutionsStore->image = $image;
                }
            }
            
            if($tutionsStore->save()) {
                if(!empty($request->schedule_id)){
                    foreach($request->schedule_id as $timetable_id){
                        
                        $TutorTimeData = TutorTimeTable::find($timetable_id);
                        $now= Carbon::parse($request->start_date);
                        $end= Carbon::parse($request->end_date); //insert date
                        $day= ucfirst($TutorTimeData->day);
                        $daysArray = getDateFromDay($now, $end, $day);

                        $scheduleStartTime = date("g:i",strtotime($TutorTimeData->start_time));
                        $scheduleEndTime = date("g:i",strtotime($TutorTimeData->end_time));

                        
                        
                        foreach($daysArray as $date){
                            $sessionDataSave = [
                                "start_time" => Carbon::createFromFormat('d-m-Y H:i',($date ." ". $scheduleStartTime))->format('d-m-Y g:i a'),
                                "end_time" => Carbon::createFromFormat('d-m-Y H:i',($date ." ". $scheduleEndTime))->format('d-m-Y g:i a'),
                                // "tutor_id" => $TutorTimeData->user_id,
                                "tuition_id" => $tutionsStore->id,
                                "date" => Carbon::createFromFormat('d-m-Y H:i',($date ." ". $scheduleStartTime))->format('d-m-Y g:i a'),
                                "day" => $TutorTimeData->day,
                                "is_completed" => false,
                                "attendance_taken" => false,
                                "completed_at" => null,
                                "meeting_id" => substr(md5(mt_rand()), 0, 32),
                            ];
                            
                            $sessionData[] = Sessions::create($sessionDataSave);
                        }
                    }
                }
                
                
                //$this->_response['data'] = $noteBookStore;
                $this->setResponse(false, 'Tuition created successfully.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    /*Update Tuition Data */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tuitions,_id',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required', 'exists:classes,_id'],
            'subject_id' => ['required', 'exists:subjects,_id'],
            'title' => 'required:max:100',
            'description' => 'max:500',
            'mode_of_teaching' => 'required',
            'schedule_id' => 'required',
            'cost' => 'required|numeric|min:4',
            'image' => 'filled|image|mimes:jpg,jpeg,png|max:2048',
            'demo_video' => 'filled|mimes:mp4,mov,wmv,mkv,avi|max:100040|',
            
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $request->request->add(['tutor_id' => auth()->user()->id]);
            $tutionsUpdate = Tuition::find($request->id);
            
            $tutionsUpdate->update($request->except(['image', 'demo_video']));
            
            
            // /* Video Upload Code  */
            if ($request->has('demo_video')) {
                $mediaName = time() . '.' . $request->demo_video->extension();
                $videoName = 'video/tution/' . $mediaName;
                $request->demo_video->storeAs('public', $videoName);
                $tutionsUpdate->update(['demo_video' => $videoName]);
            }
            
            if($request->has('image')){
                $picName = 'images/tution/' . getUniqueStamp() . '.' .$request->image->extension();
                $request->image->storeAs('public', $picName);
                $tutionsUpdate->update(['image' => $picName]);
            }
            if($tutionsUpdate){
                $this->_response['data'] = $tutionsUpdate;
                $this->setResponse(false,'Tuition updated successfully.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    /**
    * Tutions Record List For tutor Viewtution.
    *
    * @return \Illuminate\Http\Response
    */
    public function tutorViewtution(Request $request)
    {
        
        try {
            $tutionsData = Tuition::where('tutor_id',auth()->user()->id)->Orderby('created_at', 'desc')->get();
            return TutionsResource::collection($tutionsData)->additional([ "error" => false, "message" => "Tutor data retrived successfully"]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    /**
    * Tutions Record List For student Viewtution.
    *
    * @return \Illuminate\Http\Response
    */
    public function studentViewtution(Request $request)
    {
        try {
            $tutionsData = Tuition::where('student_ids',auth()->user()->id)->Orderby('created_at', 'desc')->get();
            return TutionsResource::collection($tutionsData)->additional([ "error" => false, "message" => "Student list of the tuition retrived successfully"]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    /**
    * Delete Record.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tuitions,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $tutionsDelete =  Tuition::find($request->id);
            if ($tutionsDelete) {
                $tutionsDelete->delete();
                $this->_response['data'] = $tutionsDelete;
                $this->setResponse(false, 'Tuition deleted successfully.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tuition_id' => 'required|exists:tuitions,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{

            $tuition = Tuition::find($request->tuition_id);
            if( ! $tuition->students->contains(auth()->user()->details))
            {
                $tuition->students()->attach(auth()->user()->details);
                
                $transaction = [
                    'paid_to' => $tuition->tutor_id,
                    'paid_from' => auth()->id(),
                    'date' => Carbon::now(),
                    'payment_mode' => ($request->has('razorpay_order_id'))?'Razorpay':'cash',
                    'transaction_id' => (string) Str::uuid(),
                    'amount' => $tuition->cost,
                    'payment_status' => 'paid',
                    // 'model' => get_class($tuition),
                    // 'model_id' => $tuition->id,
                    "mode_of_teaching" => ucwords(str_replace('_',' ',$tuition->mode_of_teaching))
                ];

                /** add payment transaction and commission & final amount **/
                dispatch(new AddEarningTransactionJob($tuition, $transaction));

                if($request->has('razorpay_order_id'))
                {
                    // Online payment entry
                    $payment = [
                        'user_id' => auth()->user()->_id,
                        'razorpay_order_id' => $request->razorpay_order_id,
                        'razorpay_payment_id' => $request->razorpay_payment_id,
                        'razorpay_signature' => $request->razorpay_signature,
                        'created_by' => auth()->user()->id
                    ];
                    
                    /** Post payment **/
                    dispatch(new PostPayment($tuition,$payment));
                }
                
                //Add student in network
                $tuitionTutor = User::find($tuition->tutor_id);
                $tuitionTutor->addAsFriend(auth()->user());

                $this->setResponse(false, 'Subscribed Successfully.');
            }else{
                $this->setResponse(false, 'Already Subscribed.');
            }
            
            return response()->json($this->_response, 200);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tuition_id' => 'required|exists:tuitions,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{

            $tuition = Tuition::find($request->tuition_id);
            if($tuition->students->contains(auth()->user()->details))
            {
                $tuition->students()->detach(auth()->user()->details);
                $this->setResponse(false, 'Unsubscribed Successfully.');
            }else{
                $this->setResponse(false, 'You are not subscribed to this tution yet.');
            }
            
            return response()->json($this->_response, 200);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function sessions(Request $request, $type)
    {
        $request->merge(["type" => $type]);
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,completed,upcoming',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        $sessions = new Collection(); 

        try{
            if(auth()->user()->getRole() == 'student'){
                $tuitions = auth()->user()->details->subscribed_tuitions;
            }else{
                $tuitions = auth()->user()->my_tuitions;
            }
            
            switch ($type) {
                case 'all':
                    foreach($tuitions as $tuition){
                        $sessions = $sessions->merge($tuition->sessions);
                    }
                break;
                    
                case 'completed':
                    foreach($tuitions as $tuition){
                         $sessions =  $sessions->merge($tuition->sessions()->where('is_completed',true)->Orderby('date', 'desc')->get());
                    }
                break;
                    
                case 'upcoming':
                    foreach($tuitions as $tuition){
                         $sessions =  $sessions->merge($tuition->sessions()->where('is_completed',false)->get());
                    }
                break;
            }
            return SessionListResource::collection($sessions)->additional([ "error" => false, "message" => "Session list retrived successfully"]);

                
            } catch(\Exception $e) {
                $this->setResponse(true, $e->getMessage());
                return response()->json($this->_response, 500);
            }
    }

    public function mySubscribedTuitions()
    {
        try{
            $tuitions = auth()->user()->details->subscribed_tuitions;
            return TutionsResource::collection($tuitions)->additional([ "error" => false, "message" => "Your subscribed tuition list."]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function bulkSubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tuition_id' => 'required|exists:tuitions,_id',
            'emails' => 'required|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $student_details = array();
            $tuitionData = Tuition::find($request->tuition_id);
            $studentsData = User::whereIn('email',$request->emails)->get();

            foreach($studentsData as $result){
                $tuitionData->students()->attach($result->details);

                $transaction = [
                    'paid_to' => $tuitionData->tutor_id,
                    'paid_from' => auth()->id(),
                    'date' => Carbon::now(),
                    'payment_mode' => 'cash',
                    'transaction_id' => (string) Str::uuid(),
                    'amount' => $tuitionData->cost,
                    'tp_commission' => null,
                    'final_amount' => null,
                    'payment_status' => 'paid',
                    'model' => get_class($tuitionData),
                    'model_id' => $tuitionData->id,
                    "mode_of_teaching" => $tuitionData->mode_of_teaching
                ];

                //Add student in network
                $tuitionTutor = User::find($tuitionData->tutor_id);
                $tuitionTutor->addAsFriend($result);

                /** add payment transaction **/
                dispatch(new AddEarningTransactionJob($tuitionData, $transaction));
            }
            
            if($tuitionData){
                $this->setResponse(false,'Assigned student to tuition successfully.');
                return response()->json($this->_response);
            }

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function getSubscribedStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tuition_id' => 'required|exists:tuitions,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $studentsData = array();
            $tuition = Tuition::find($request->tuition_id);
            $students = $tuition->students;
            
            foreach($students as $result){
                $studentsData[] = $result->user;
            }
            // return TuitionAddStudentListResource::collection($studentsData)->additional([ "error" => false, "message" => "Subscribed students list."]);
            return UserpluckResource::collection($studentsData)->additional([ "error" => false, "message" => "Subscribed students list."]);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function enableStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tuition_id' => 'required|exists:tuitions,_id',
            'enable_students' => 'required|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $tuition = Tuition::find($request->tuition_id);
            $tuition->update($request->all());
            
            if($tuition){
                //$this->_response['data'] = $tuition;
                $this->setResponse(false,'Student Enabled.');
                return response()->json($this->_response);
            }

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function disableStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tuition_id' => 'required|exists:tuitions,_id',
            'disable_students' => 'required|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $tuition = Tuition::find($request->tuition_id);
            $tuition->update($request->all());
            
            if($tuition){
                //$this->_response['data'] = $tuition;
                $this->setResponse(false,'Student Disable.');
                return response()->json($this->_response);
            }

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function completeSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sessions,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            
            // $update = Sessions::where('id', $request->id)->update(['is_completed' => true, 'completed_at'=>Carbon::now()]);
            $update = Sessions::find($request->id);
            $update->is_completed = true;
            $update->completed_at = Carbon::now();
            $update->save();

            $this->setResponse(false, 'Session completed successfully.');
            return response()->json($this->_response); 

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	public function sessionAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,_id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|exists:users,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            
            // $update = Sessions::where('id', $request->id)->update(['is_completed' => true, 'completed_at'=>Carbon::now()]);
            $session = Sessions::find($request->session_id);
			
			if($session->attendance_taken == true)
			{
				// delete already taken attendance
				$session->attendance()->each(function($attendance) {
					$attendance->delete(); 
				});
			}
			
			// Tuition of session
			$tuition_id = $session->tuition->_id;
			$tuition = Tuition::find($tuition_id);
            $subscribedStudents = $tuition->students->pluck('user_id')->toArray();
			
			$absentStudent = array_diff($subscribedStudents,$request->student_ids);
			
			foreach($request->student_ids as $student_id)
			{
				$present =  $session->attendance()->create([
					'student_id' => $student_id,
					'status' => 'present'
				]);
			}
			
			foreach($absentStudent as $student_id)
			{
				$present =  $session->attendance()->create([
					'student_id' => $student_id,
					'status' => 'absent'
				]);
			}
						
            $session->attendance_taken = true;
            $session->save();

            $this->setResponse(false, 'Attendance taken successfully.');
            return response()->json($this->_response); 

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	public function sessionStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,_id'
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            
            $session = Sessions::find($request->session_id);
			if($session->attendance()->count())
			{
				$session = Sessions::find($request->session_id);
				return EditAttendanceResource::collection($session->attendance)->additional([ "error" => false, "message" => "Edit attendance data list."]); 
			}else{
				$tuition_id = $session->tuition->_id;
			
				$studentsData = array();
				$tuition = Tuition::find($tuition_id);
				$students = $tuition->students;
				
				foreach($students as $result){
					$studentsData[] = $result->user;
				}
				// return TuitionAddStudentListResource::collection($studentsData)->additional([ "error" => false, "message" => "Subscribed students list."]);
				return UserpluckResource::collection($studentsData)->additional([ "error" => false, "message" => "Subscribed students list."]);
			}
			
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	// public function editAttendance(Request $request)
    // {
        // $validator = Validator::make($request->all(), [
            // 'session_id' => 'required|exists:sessions,_id'
        // ]);
        
        // if ($validator->fails()) {
            // $this->setResponse(true, $validator->errors()->all());
            // return response()->json($this->_response, 400);
        // }

        // try{
            
            // $session = Sessions::find($request->session_id);
			// return EditAttendanceResource::collection($session->attendance)->additional([ "error" => false, "message" => "Edit attendance data list."]); 

        // } catch(\Exception $e) {
            // $this->setResponse(true, $e->getMessage());
            // return response()->json($this->_response, 500);
        // }
    // }
}


   