<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subject;
use App\Models\Division;
use App\Models\SchoolClass;
use App\Models\ClassDivision;
use App\Models\DivisionStudent;
use App\Models\DivisionSchedule;
use App\Models\DivisionSession;
use App\Models\DivisionAttendance;
use App\Models\DivisionSubjectTeacher;
use App\Http\Resources\DropdownResource;
use App\Http\Resources\DivisionResource;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\ClassDivisionResource;
use App\Http\Resources\DivisionSessionResource;
use App\Http\Resources\SubjectDropdownResource;
use App\Http\Resources\DivisionAttendanceResource;
use App\Http\Resources\EditDivisionScheduleResource;
use App\Http\Resources\DivisionAttendanceDetailsResource;
use App\Http\Resources\GetUserToAddDivisionResource;
use App\Http\Resources\UserpluckResource;
use Illuminate\Support\Facades\Validator;
use App\Jobs\AssignSchoolFriends;
use App\Jobs\GenerateDivisionSession;
use App\Jobs\DisableEnableTutorSchedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notification;
use App\Models\DivisionSubjectLeader;

class SchoolClassController extends Controller
{
    /**
     * Assignment sections list.
     *
     * @return \Illuminate\Http\Response
     */
    public function divisions()
    {
        try {
            $divisions = Division::all();
            return DivisionResource::collection($divisions)->additional([ "error" => false, "message" => 'Here is all division data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Create class.
     *
     * @return \Illuminate\Http\Response
     */
    public function createClass(request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => 'required|exists:classes,_id',
            'class_name' => 'required',
            'image' => 'filled|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'divisions' => 'required|array',
            'divisions.*' => 'required|exists:divisions,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $class = SchoolClass::create($request->except(['image']));

            if ($request->has('image')) {
                $image = $this->uploadFile($request->image, 'schoolclass/image');
                if ($image != false) {
                    $class->image = $image;
                }
            }

            $class->save();

            foreach($request->divisions as $division)
            {
                $division = $class->divisions()->create([
                   'division_id' => $division,
                   'created_by' => auth()->user()->_id
                ]);
            }

            $this->setResponse(false, 'Class created successfully.');
            return response()->json($this->_response); 

        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Class list.
     *
     * @return \Illuminate\Http\Response
     */
    public function classList()
    {
        try {
            $class = SchoolClass::all();
            return SchoolClassResource::collection($class)->additional([ "error" => false, "message" => 'Here is all class data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Class list.
     *
     * @return \Illuminate\Http\Response
     */
    public function classBySyllabus(request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $class = SchoolClass::select('class_name')->where(['syllabus_id' => $request->syllabus_id])->get();
            return DropdownResource::collection($class)->additional([ "error" => false, "message" => 'Here is all class data']);
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
    public function divisionList()
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

            return ClassDivisionResource::collection($session)->additional([ "error" => false, "message" => 'Here is all division data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Division list by class.
     *
     * @return \Illuminate\Http\Response
     */
    public function divisionByClass(request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $divisions = ClassDivision::where(['school_class_id' => $request->class_id])->get();
            return DropdownResource::collection($divisions)->additional([ "error" => false, "message" => 'Here is all division data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add Subject/Teacher for division.
     *
     * @return \Illuminate\Http\Response
     */
    public function addSubjectTeacher(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'class_teacher' => 'required_with:class_teacher_subject|exists:users,_id',
            'class_teacher_subject' => 'required_with:class_teacher|exists:subjects,_id',
            'ass_class_teacher' => 'required_with:ass_class_teacher_subject|exists:users,_id',
            'ass_class_teacher_subject' => 'required_with:ass_class_teacher|exists:subjects,_id',
            'teachers' => 'required_with:subjects|array',
            'teachers.*' => 'required|exists:users,_id',
            'subjects' => 'required_with:teachers|array',
            'subjects.*' => 'required|exists:subjects,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $division = ClassDivision::find($request->division_id);

            // Add Class Teacher
            if ($request->has('class_teacher')) {
                $update = ClassDivision::where('_id', $request->division_id)
                ->update([
                    'class_teacher' => $request->class_teacher,
                    'class_teacher_subject' => $request->class_teacher_subject,
                ]);

                $classTeacher = $division->SubjectTeacher()->create([
                    'teacher_id' => $request->class_teacher,
                    'subject_id' => $request->class_teacher_subject,
                    'is_class_teacher' => true,
                    'created_by' => auth()->user()->_id
                ]);
                dispatch(new AssignSchoolFriends($request->class_teacher));
            }

            // Add Assistive Class Teacher
            if ($request->has('ass_class_teacher')) {
                $update = ClassDivision::where('_id', $request->division_id)
                ->update([
                    'ass_class_teacher' => $request->ass_class_teacher,
                    'ass_class_teacher_subject' => $request->ass_class_teacher_subject,
                ]);

                $assClassTeacher = $division->SubjectTeacher()->create([
                    'teacher_id' => $request->ass_class_teacher,
                    'subject_id' => $request->ass_class_teacher_subject,
                    'is_ass_class_teacher' => true,
                    'created_by' => auth()->user()->_id
                ]);
                dispatch(new AssignSchoolFriends($request->ass_class_teacher));
            }

            // Add Other Teachers
            if ($request->has('teachers')) {
                foreach($request->teachers as $key=>$value)
                {
                    $teacher = $division->SubjectTeacher()->create([
                        'teacher_id' => $value,
                        'subject_id' => $request->subjects[$key],
                        'created_by' => auth()->user()->_id
                    ]);
                    dispatch(new AssignSchoolFriends($value));
                }
            }

            $this->setResponse(false, 'Teacher added successfully.');
            return response()->json($this->_response); 

        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Get student to add in division.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsersToAdd(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'role' => 'required|in:school-student,school-tutor',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $division = ClassDivision::find($request->division_id);
            if($request->role == 'school-student')
            {
                $user_ids = DivisionStudent::pluck('student_id')->toArray();
            }elseif($request->role == 'school-tutor'){
                $user_ids = DivisionSubjectTeacher::where(['class_division_id'=>$request->division_id])->pluck('teacher_id')->toArray();
            }

            $userData = User::role($request->role)->whereNotIn('_id', $user_ids)->Orderby('created_at', 'desc')->get();
            return GetUserToAddDivisionResource::collection($userData)->additional([ "error" => false, "message" => 'Here is school users data with basic details']);
        
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add student to division.
     *
     * @return \Illuminate\Http\Response
     */
    public function addDivisionStudent(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|exists:users,_id',
            // 'leader_ids' => 'filled|array|min:1|max:2',
            // 'leader_ids.*' => 'required|in_array:student_ids.*|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $division = ClassDivision::find($request->division_id);

            // Add all students
            foreach($request->student_ids as $student_id)
            {
                $exist = DivisionStudent::where(['class_division_id'=>$division->_id, 'student_id'=>$student_id])->count();
                if(!$exist)
                {
                    $division->students()->create([
                        'student_id' => $student_id
                    ]);
                    dispatch(new AssignSchoolFriends($student_id));
                }
            }

            // Add Leader
            // if(count($request->leader_ids))
            // {
            //     // Remove previous leader
            //     DivisionStudent::where(['class_division_id'=>$division->_id,'is_leader'=>true])->update(['is_leader'=>false]);

            //     //Assign new students
            //     DivisionStudent::where(['class_division_id'=>$division->_id])->whereIn('student_id',$request->leader_ids)->update(['is_leader'=>true]);
            // }

            $this->setResponse(false, 'Student added successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add subject leaders.
     *
     * @return \Illuminate\Http\Response
     */
    public function addSubjectLeader(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|exists:subjects,_id',
            // 'leader_ids' => 'required|array|min:1|size:'. count($request->get('subject_ids')),
            'leader_ids' => 'required|array|min:1',
            'leader_ids.*' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $division = ClassDivision::find($request->division_id);

            // Delete old leaders
            $division->subjectLeaders()->each(function($leader){
                $leader->delete();
            });

            // Add new leaders
            foreach($request->subject_ids as $key=>$subject_id)
            {
                $division->subjectLeaders()->create([
                    'subject_id' => $subject_id,
                    'leader_id' => $request->leader_ids[$key],
                    'status' => 1,
                    'created_by' => auth()->user()->id,
                ]);
            }

            $this->setResponse(false, 'Subject leaders added successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Remove user from the division.
     *
     * @return \Illuminate\Http\Response
     */
    public function removeUser(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $user = User::find($request->user_ids[0]);

            if($user->hasRole('school-student'))
            {
                foreach($request->user_ids as $user_id)
                {
                    $divisionStudentRemove = DivisionStudent::where(['class_division_id'=>$request->division_id, 'student_id'=>$user_id])->delete();

                    // Remove student from division leader
                    $leadershipRemove = DivisionSubjectLeader ::where(['class_division_id'=>$request->division_id, 'leader_id'=>$user_id])->delete();

                }
            }elseif($user->hasRole('school-tutor')){
                foreach($request->user_ids as $user_id)
                {
                    // Update and remove class teacher, if user is class teacher of division
                    $removeClassTeacher = ClassDivision::where(['_id' => $request->division_id, 'class_teacher'=>$user_id])->update(['class_teacher' => null, 'class_teacher_subject'=> null]);

                    // Update and remove assistive class teacher, if user is assistive class teacher of division
                    $removeClassAssTeacher = ClassDivision::where(['_id' => $request->division_id, 'ass_class_teacher'=>$user_id])->update(['ass_class_teacher' => null, 'ass_class_teacher_subject'=> null]);
                    
                    // Remove teacher from division
                    $divisionTeacherRemove = DivisionSubjectTeacher::where(['class_division_id'=>$request->division_id, 'teacher_id'=>$user_id])->delete();

                    // Remove teacher schedule
                    $scheduleRemove = DivisionSchedule::where(['class_division_id'=>$request->division_id, 'teacher_id'=>$user_id])->delete();

                    // Remove teacher sessions
                    $sessionsRemove = DivisionSession::where(['class_division_id'=>$request->division_id, 'teacher_id'=>$user_id])->where('date', '>=' , date('Y-m-d'))->delete();

                }
            }
            
            $this->setResponse(false, 'User removed successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Enable/Disable user from the division.
     *
     * @return \Illuminate\Http\Response
     */
    public function enableDisableUser(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'user_id' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $user = User::find($request->user_id);
            
            if($user->hasRole('school-student'))
            {
                $divisionStudent = DivisionStudent::where(['class_division_id'=>$request->division_id, 'student_id'=>$request->user_id])->first();
                if($divisionStudent->status)
                {
                    $divisionStudent->status = 0;
                }else{
                    $divisionStudent->status = 1;
                }
                $divisionStudent->save();
            }elseif($user->hasRole('school-tutor')){
                $divisionTeacher = DivisionSubjectTeacher::where(['class_division_id'=>$request->division_id, 'teacher_id'=>$request->user_id])->first();
                if($divisionTeacher->status)
                {
                    $divisionTeacher->status = 0;
                }else{
                    $divisionTeacher->status = 1;
                }
                $divisionTeacher->save();

                //EnableDisable this tutor schedule
                dispatch(new DisableEnableTutorSchedule($request->division_id, $request->user_id, $divisionTeacher->status));
            }
            
            $this->setResponse(false, 'Status updated successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add division time table.
     *
     * @return \Illuminate\Http\Response
     */
    public function addDivisionTimetable(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'required|exists:users,_id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'required|exists:subjects,_id',
            'days' => 'required|array',
            'days.*' => 'required|array',
            'days.*.*' => 'required',
            'start_time' => 'required|array',
            'start_time.*' => 'required|array',
            'start_time.*.*' => 'required',
            'end_time' => 'required|array',
            'end_time.*' => 'required|array',
            'end_time.*.*' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $division = ClassDivision::find($request->division_id);
            foreach($request->teacher_ids as $key=>$teacher_id)
            {
                // Delete old schedule that is removed(if present)
                $deleteSchedule = DivisionSchedule::where(['class_division_id'=>$request->division_id, 'teacher_id'=>$teacher_id, 'subject_id'=>$request->subject_ids[$key]])->whereNotIn('day', $request->days[$key])->delete();

                // Delete sessions for deleted schedule
                $deleteSessions = DivisionSession::where(['class_division_id'=>$request->division_id, 'teacher_id'=>$teacher_id, 'subject_id'=>$request->subject_ids[$key]])->where('date', '>=', date('Y-m-d'))->delete();


                foreach($request->days[$key] as $secondkey=>$day)
                {
                    // Update schedule
                    $schedule = $division->schedule()->updateOrCreate([
                        'teacher_id' => $teacher_id, 
                        'subject_id' => $request->subject_ids[$key],
                        'day' => $day,
                    ],[
                        'teacher_id' => $teacher_id, 
                        'subject_id' => $request->subject_ids[$key],
                        'day' => $day, 
                        'start_time' => $request->start_time[$key][$secondkey],
                        'end_time' => $request->end_time[$key][$secondkey],
                    ]);
                }
                dispatch(new GenerateDivisionSession($request->division_id, $teacher_id, $request->subject_ids[$key]));
            }

            $this->setResponse(false, 'Schedule added successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }


    /**
     * Get timetable to edit.
     *
     * @return \Illuminate\Http\Response
     */
    public function editTimetable(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $division = ClassDivision::find($request->division_id);
                        
            return EditDivisionScheduleResource::collection($division->schedule->groupBy(['teacher_id']))->additional([ "error" => false, "message" => 'Here is edit timetable data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Session list.
     *
     * @return \Illuminate\Http\Response
     */
    public function sessionList(Request $request, $type)
    {
        $request->merge(["type" => $type]);
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,completed,upcoming',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        $role = auth()->user()->getRole();
        try {
            switch ($role) {
                case 'school-student':
                    $divisionIds = auth()->user()->getDivisionIds();
                    switch ($type) {
                        case 'all':
                            $divisions = DivisionSession::whereIn('class_division_id', $divisionIds)->Orderby('date', 'asc')->get();
                        break;  
                        case 'completed':
                            $divisions = DivisionSession::whereIn('class_division_id', $divisionIds)->where('date', '<', date('Y-m-d'))->Orderby('date', 'asc')->get();
                        break; 
                        case 'upcoming':
                            $divisions = DivisionSession::whereIn('class_division_id', $divisionIds)->where('date', '>=', date('Y-m-d'))->Orderby('date', 'asc')->get();
                        break;
                    }
                break;
                case 'school-tutor':
                    $divisionIds = auth()->user()->getDivisionIds();
                    switch ($type) {
                        case 'all':
                            $divisions = DivisionSession::whereIn('class_division_id', $divisionIds)->where(["teacher_id"=>auth()->user()->id])->Orderby('date', 'asc')->get();
                        break;  
                        case 'completed':
                            $divisions = DivisionSession::whereIn('class_division_id', $divisionIds)->where('date', '<', date('Y-m-d'))->where(["teacher_id"=>auth()->user()->id])->Orderby('date', 'asc')->get();
                        break; 
                        case 'upcoming':
                            $divisions = DivisionSession::whereIn('class_division_id', $divisionIds)->where('date', '>=', date('Y-m-d'))->where(["teacher_id"=>auth()->user()->id])->Orderby('date', 'asc')->get();
                        break;
                    }
                break;
                case 'school-admin':
                    switch ($type) {
                        case 'all':
                            $divisions = DivisionSession::Orderby('date', 'asc')->get();
                        break;  
                        case 'completed':
                            $divisions = DivisionSession::where('date', '<', date('Y-m-d'))->Orderby('date', 'asc')->get();
                        break; 
                        case 'upcoming':
                            $divisions = DivisionSession::where('date', '>=', date('Y-m-d'))->Orderby('date', 'asc')->get();
                        break;
                    }
                break;
            }

            return DivisionSessionResource::collection($divisions)->additional([ "error" => false, "message" => 'Here is my session data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Filter session.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterSession(request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'filled|exists:class_divisions,_id',
            'teacher_id' => 'filled|exists:users,_id',
            'subject_id' => 'filled|exists:subjects,_id',
            'date' => 'filled|date|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $divisions = DivisionSession::when($request->has('division_id'), function ($query) use ($request) {
                            $query->where('class_division_id', $request->division_id);
                         })
                        ->when($request->has('teacher_id'), function ($query) use ($request) {
                            $query->where('teacher_id', $request->teacher_id);
                         })
                        ->when($request->has('subject_id'), function ($query) use ($request) {
                            $query->where('subject_id', $request->subject_id);
                         })
                        ->when($request->has('date'), function ($query) use ($request) {
                            $query->where('date', $request->date);
                         })
                        ->get();
            return DivisionSessionResource::collection($divisions)->additional([ "error" => false, "message" => 'Here is filtered session data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function takeSessionAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'date' => 'required|date|date_format:Y-m-d',
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|exists:users,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $sessions = DivisionSession::where(['class_division_id' => $request->division_id, 'date' => $request->date])->get();
            
			if(!$sessions->count())
            {
                $this->setResponse(true, "There is no any sassion for date ".$request->date);
                return response()->json($this->_response, 200);
            }
            
            // Division students
			$divisionStudents = DivisionStudent::where(['class_division_id' => $request->division_id])->pluck('student_id')->toArray();
            $absentStudent = array_diff($divisionStudents,$request->student_ids);
            
			foreach($sessions as $session)
            {
                // Delete old attendance
                $session->attendance()->each(function($attendance) {
                    $attendance->delete(); 
                 });

                foreach($request->student_ids as $student_id)
                {
                    $present =  $session->attendance()->create([
                        'class_division_id' => $request->division_id,
                        'student_id' => $student_id,
                        'date' => $session->date,
                        'status' => 'present',
                        'created_by' => auth()->user()->id
                    ]);
                }
                
                foreach($absentStudent as $student_id)
                {
                    $absent =  $session->attendance()->create([
                        'class_division_id' => $request->division_id,
                        'student_id' => $student_id,
                        'date' => $session->date,
                        'status' => 'absent',
                        'created_by' => auth()->user()->id
                    ]);
                }
            }

            $this->setResponse(false, 'Attendance taken successfully.');
            return response()->json($this->_response); 

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function getAttedanceByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
            'date' => 'required|date|date_format:Y-m-d',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $attendanceCheck = DivisionAttendance::where(['class_division_id' => $request->division_id, 'date' => $request->date])->count();
			if($attendanceCheck)
            {
                return (new DivisionAttendanceDetailsResource($request))->additional([ "error" => false, "message" => 'Here is attendance data']);
            }else{
                $studentIds = DivisionStudent::where(['class_division_id'=>$request->division_id])->pluck('student_id')->toArray();
                $students = User::whereIn('_id',$studentIds)->get();
                return UserpluckResource::collection($students)->additional([ "error" => false, "message" => 'Here is students data']);
            }
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /* Request for access */
    public function requestDivisionAccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'required|exists:class_divisions,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $division = ClassDivision::find($request->division_id);
            
            $mailData = [
                'user_name' => auth()->user()->full_name,
                'user_email' => auth()->user()->email,
                'user_tpid' => (auth()->user()->details->tp_id)?auth()->user()->details->tp_id:'NA',
                'user_role' => auth()->user()->getRole(),
                'division_name' => $division->name,
                'class' => $division->class->class_name,
                'email_subject' => 'Request For Class Access : '. $division->class->class_name.' - '.$division->name,
                'email_template' => 'DivisionAccessRequest',
				'host' => getHost()
            ];
            
            
            $adminEmails = User::role('school-admin')->pluck('email')->toArray();
            
            foreach($adminEmails as $email)
            {
                Mail::to($email)->queue(new Notification($mailData));
            }

            $this->setResponse(false, 'Request sent successfully.');
            return response()->json($this->_response); 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /* User subjects sropdown */
    public function userSubjectsDropdown()
    {        
        try {
            $role = auth()->user()->getRole();

            switch ($role) {
                case 'school-student':
                    $divisionIds = auth()->user()->getDivisionIds();
                    $subjectIds = DivisionSubjectTeacher::whereIn('class_division_id',$divisionIds)->pluck('subject_id');
                    $subjectDropdown = Subject::whereIn('_id',$subjectIds)->get();
                break;
                case 'school-tutor':
                    $subjectIds = DivisionSubjectTeacher::where(['teacher_id' => auth()->user()->_id])->pluck('subject_id');
                    $subjectDropdown = Subject::whereIn('_id',$subjectIds)->get();
                break;
                case 'school-admin':
                    $subjectDropdown = array();
                break;
            }

            return SubjectDropdownResource::collection($subjectDropdown)->additional([ "error" => false, "message" => 'Here is all subjects data']); 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
