<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\FriendRequest;
use App\Models\School;
use App\Models\ClassDivision;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserpluckResource;
use App\Http\Resources\UserDetailsResource;
use App\Http\Resources\FriendProfileResource;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
use App\Mail\InviteUser;
use App\Mail\VerifyUser;
use App\Mail\Notification;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Illuminate\Support\Facades\Auth;
use App\Jobs\AssignSchoolFriends;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware('ApplySchoolUser')->only(['index']); //This applies it only to listings method in this case
        $this->middleware('ApplySchoolUser', ['except' => ['store']]);
    }

    /*User List Data show */
    public function index()
    {
        try {
            $userData = User::Orderby('created_at', 'desc')->get();
            return UserResource::collection($userData)->additional([ "error" => false, "message" => 'Here is all users data']);
        
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*User List Data show */
    public function userDropdown()
    {
        try {
            $userData = User::Orderby('created_at', 'desc')->get();
            return UserpluckResource::collection($userData)->additional([ "error" => false, "message" => 'Here is all users data with basic details']);
        
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Friends List Data show */
    public function friendsDropdown()
    {
        try {
            $friends = auth()->user()->friends();
            return UserpluckResource::collection($friends)->additional([ "error" => false, "message" => 'Here is all friends with basic details']);
        
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*User List By Role */
    public function userByRole(request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:tutor,student,clerk,admin,parent,school-admin,school-student,school-tutor',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $userData = User::role($request->role)->Orderby('created_at', 'desc')->get();
            return UserResource::collection($userData)->additional([ "error" => false, "message" => 'Here is all users data with basic details']);
        
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*User List By Role */
    public function dropdownByRole(request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:tutor,student,clerk,admin,parent,school-admin,school-student,school-tutor',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $userData = User::role($request->role)->Orderby('created_at', 'desc')->get();
            return UserpluckResource::collection($userData)->additional([ "error" => false, "message" => 'Here is all users data with basic details']);
        
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*New User Insert Data */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:tutor,student,clerk,admin,parent,school-admin,school-tutor,school-student',
            'password' => 'required|confirmed|min:8',
            'address' => 'required',
            'gender' => 'required',
            'city' => 'required',
            'state' => 'required',
            'birth_date' => 'date',
            'pincode' => 'required',
            'childs' => 'required_if:role,parent|array',
            'childs.*' => 'required|exists:users,_id',
            'school_type' => 'required_if:role,school-admin|in:new,existing',
            'school_id' => 'required_if:school_type,existing|exists:schools,_id',
            'school_name' => 'required_if:school_type,new',
            'registration_no' => 'required_if:school_type,new',
            'school_pincode' => 'required_if:school_type,new',
            'school_city' => 'required_if:school_type,new',
            'school_phone' => 'required_if:school_type,new|numeric',
            'school_email' => 'required_if:school_type,new|email|unique:schools,email',
            'school_mobile' => 'required_if:school_type,new|numeric',
            'principal' => 'required_if:school_type,new',
            'vice_principal' => 'required_if:school_type,new',
            'incharge' => 'required_if:school_type,new',
            'working_start_date' => 'required_if:school_type,new',
            'working_end_date' => 'required_if:school_type,new',
            'school_attachment' => 'required_if:school_type,new|mimes:jpg,bmp,png,jpeg,svg,pdf|max:2048',
            'school_image' => 'required_if:school_type,new|mimes:jpg,bmp,png,jpeg,svg|max:2048',
            'school_class_id' => 'required_if:role,school-student|exists:school_classes,_id',
            'school_class_division_id' => 'required_if:role,school-student|exists:class_divisions,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            
            $user = User::create($request->except('role','gender'));

            $user->details()->create([
                "gender" => $request->gender,
                "address" => $request->address,
                "area" => $request->address,
                "state" => $request->state,
                "city" => $request->city,
                "country" => $request->has('country') ? $request->country : 'India',
                "nationality" => $request->has('nationality') ? $request->nationality : 'Indian',
                "pincode" => $request->pincode,
                "phone" => $request->phone,
                "aadhar_id" => $request->aadhar_id,
                "birth_date" =>  new DateTime($request->birth_date),
                'topic' => $request->topic !== '' ? $request->topic : null,
                'mode_of_classes' => $request->mode_of_teaching !== '' ? $request->online_cost_per_hour : null,
                'online_cost_per_hour' => $request->online_cost_per_hour !== '' ? $request->online_cost_per_hour : null,
                'fb_url' => $request->fb_url !== '' ? $request->fb_url : null,
                'li_url' => $request->li_url !== '' ? $request->li_url : null,
                'tw_url' => $request->tw_url !== '' ? $request->tw_url : null,
                'insta_url' => $request->insta_url !== '' ? $request->insta_url : null,
            ]);

            $user->assignRole($request->role);

            $pointData = [
                'comment' => 'received points to signup at tutorpark',
                'type' => 'received',
                'source_of_point' => 'signup',
				'user_id' => $user->_id
            ];

            if($user->hasRole('student') || $user->hasRole('tutor') || $user->hasRole('school-student') || $user->hasRole('school-tutor'))
            {
                $user->signupPoints($pointData);
            }
            
            // Assign child to parent
            if($user->hasRole('parent'))
            {
                $user->childs()->attach($request->childs);
            }

            // Assign school, class and division to schoo-student and school-tutor
            if($user->hasRole('school-student') || $user->hasRole('school-tutor'))
            {
                // Associate school to newly registered user if login user has school associated
                if(auth()->user()->hasRole('school-tutor') || auth()->user()->hasRole('school-admin'))
                {
                    if(auth()->user()->hasSchool())
                    {
                        $school = auth()->user()->school;
                        $user->assignSchool($school);
                    }
                }

                if($user->hasRole('school-student'))
                {
                    $division = ClassDivision::find($request->school_class_division_id);
                    $division->students()->create([
                        'student_id' => $user->_id
                    ]);
                }

                dispatch(new AssignSchoolFriends($user->_id));
            }

            // Add school or assign school
            if($user->hasRole('school-admin'))
            {
                if($request->school_type == 'new')
                {
                    $school = School::create([
                        'type' => 'school_collaboration',
                        'school_name' => $request->school_name,
                        'registration_no' => $request->registration_no,
                        'pincode' => $request->school_pincode,
                        'city' => $request->school_city,
                        'phone' => $request->school_phone,
                        'email' => $request->school_email,
                        'mobile' => $request->school_mobile,
                        'principal' => $request->principal,
                        'vice_principal' => $request->vice_principal,
                        'incharge' => $request->incharge,
                        'working_start_date' => $request->working_start_date,
                        'working_end_date' => $request->working_end_date,
                        'is_verified' => true,
                        'verified_by' => auth()->user()->_id,
                        'created_by' => auth()->user()->_id,
                    ]);

                    if($request->has('school_attachment')){
                        $image = $this->uploadFile($request->school_attachment,'images/school/');
                        if($image != false){
                            $school->attachment = $image;
                        }
                    }

                    if($request->has('school_image')){
                        $image = $this->uploadFile($request->school_image,'images/school/');
                        if($image != false){
                            $school->image = $image;
                        }
                    }

                    $school->save();
                }else{
                    $school = School::find($request->school_id);
                }

                $user->school()->associate($school)->save();
                dispatch(new AssignSchoolFriends($user->_id));
            }

            $mailData = [
                'verify_token' => $user->verify_token,
                'host' => getHost(), 
            ];
            Mail::to($request->email)->queue(new VerifyUser($mailData));

            // return $this->login($request);
            $this->_response['data'] = '';
            $this->setResponse(false, 'User added successfully.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true,  $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
   
    /*Update User Data */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'role' => 'required|in:tutor,student,clerk,admin,school-tutor,school-student',
            'address' => 'required',
            'gender' => 'required',
            'city' => 'required',
            'state' => 'required',
            'birth_date' => 'date',
            'syllabus_id' => 'required_if:role,tutor,student|exists:syllabuses,_id',
            'class_id' => 'required_if:role,tutor,student|exists:classes,_id',
            'subject_id' => 'required_if:role,tutor,student|exists:subjects,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            // $userUpdate =  User::where('_id', $request->id)->update($request->all());

            $user = User::find(auth()->id());
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();

            $details = $user->details;
            $details->phone=$request->phone;
            $details->aadhar_id=$request->aadhar_id;
            $details->nationality=$request->nationality;
            $details->topic=$request->topic;
            $details->mode_of_classes=$request->mode_of_teaching;
            $details->online_cost_per_hour=$request->online_cost_per_hour;
            $details->fb_url=$request->fb_url;
            $details->li_url=$request->li_url;
            $details->tw_url=$request->tw_url;
            $details->insta_url=$request->insta_url;
            $details->gender=$request->gender;
            $details->languages=$request->languages;
            $details->address=$request->address;
            $details->area=$request->address;
            $details->birth_date=$request->birth_date;
            $details->pincode=$request->pincode;
            $details->country="India";
            // $details->hide_area=$request->hide_area;
            $details->save($request->except(['phone','aadhar_id','gender','nationality','mode_of_classes','topic','online_cost_per_hour','fb_url','li_url','tw_url','insta_url','languages','education','experience','timing','preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics', 'address', 'area', 'birth_date', 'pincode', 'country']));
            
            //save data of 'preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics'
            if($request->has('syllabus_id'))
            {
                $details->preferredBoards()->detach($details->preferredBoards->pluck('id')->toArray());
                $details->preferredBoards()->attach([$request->syllabus_id]);
            }
            

            // $details->preferredClasses()->attach(json_decode($request->class_ids));
            if($request->has('class_id'))
            {
                $details->preferredClasses()->attach([$request->class_id]);
            }

            if($request->has('subject_id'))
            {
                $details->preferredSubjects()->attach([$request->subject_id]);
            }

            $educations = $request->has('education') ? $request->education : [];
            if(!empty($educations))
            {
                foreach($details->education as $education_old){
                    $EduData_old = $details->education->find($education_old['id']);
                    $EduData_old->delete();
                }
                foreach($educations as $education){
                   $details->education()->create($education);
                }
            }

            $experiences =  $request->has('experience') ? $request->experience : [];
            if(!empty($experiences))
            {
                foreach($details->experience as $experience_old){
                    $ExpData_old = $details->experience->find($experience_old['id']);
                    $ExpData_old->delete();
                }

                foreach($experiences as $experience){
                   $details->experience()->create([
                    "organization" => $experience['organization'],
                    "designation" => $experience['designation'],
                    "experience_month" => intval($experience['experience_month']),
                   ]);
                }
            }
           
            $availabilities = $request->has('availability') ? $request->availability : [];
            
            /** Delete availability slots that are not passed in API */
            $deleteSlots = $user->availability->filter(function($slot) use($availabilities){
                return in_array($slot->id, Arr::pluck($availabilities, 'id')) ? false : true;
            });
            $deleteSlots->each->delete();

            if(!empty($availabilities))
            {
                foreach($availabilities as $times){

                    $availability = $user->availability()->find($times['id']);
                    if($availability){
                            $availability->day =  $times['day'];
                            $availability->start_time = Carbon::createFromFormat('H:i',$times['start_time']);
                            $availability->end_time = Carbon::createFromFormat('H:i',$times['end_time']);
                            $availability->save();
                    }else{
                        $user->availability()->create([
                            "day" =>  $times['day'],
                            "start_time" => Carbon::createFromFormat('H:i',$times['start_time']),
                            "end_time" => Carbon::createFromFormat('H:i',$times['end_time'])
                        ]);
                    }
                }
            }

            $user = User::find(auth()->id());
            return (new UserResource($user))->additional(["error" => false, "message" => 'Updated Successfully.']);
            //$this->setResponse(false, 'Updated Successfully.');

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

     /*Update User Data */
     public function updateOtherUser(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'user_id' => 'required|exists:users,_id',
             'first_name' => 'required',
             'last_name' => 'required',
             'role' => 'required|in:tutor,student,clerk,admin,school-tutor,school-student,school-admin',
             'address' => 'required',
             'gender' => 'required',
             'city' => 'required',
             'state' => 'required',
             'birth_date' => 'date',
             'syllabus_id' => 'required_if:role,tutor,student|exists:syllabuses,_id',
             'class_id' => 'required_if:role,tutor,student|exists:classes,_id',
             'subject_id' => 'required_if:role,tutor,student|exists:subjects,_id',
         ]);
 
         if ($validator->fails()) {
             $this->setResponse(true, $validator->errors()->all());
             return response()->json($this->_response, 400);
         }
         
         try {
             // $userUpdate =  User::where('_id', $request->id)->update($request->all());
 
             $user = User::find($request->user_id);
             $user->first_name = $request->first_name;
             $user->last_name = $request->last_name;
             $user->save();
 
             $details = $user->details;
             $details->phone=$request->phone;
             $details->aadhar_id=$request->aadhar_id;
             $details->nationality=$request->nationality;
             $details->topic=$request->topic;
             $details->mode_of_classes=$request->mode_of_teaching;
             $details->online_cost_per_hour=$request->online_cost_per_hour;
             $details->fb_url=$request->fb_url;
             $details->li_url=$request->li_url;
             $details->tw_url=$request->tw_url;
             $details->insta_url=$request->insta_url;
             $details->gender=$request->gender;
             $details->languages=$request->languages;
             $details->address=$request->address;
             $details->area=$request->address;
             $details->birth_date=$request->birth_date;
             $details->pincode=$request->pincode;
             $details->country="India";
             // $details->hide_area=$request->hide_area;
             $details->save($request->except(['phone','aadhar_id','gender','nationality','mode_of_classes','topic','online_cost_per_hour','fb_url','li_url','tw_url','insta_url','languages','education','experience','timing','preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics', 'address', 'area', 'birth_date', 'pincode', 'country']));
             
             //save data of 'preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics'
             if($request->has('syllabus_id'))
             {
                 $details->preferredBoards()->detach($details->preferredBoards->pluck('id')->toArray());
                 $details->preferredBoards()->attach([$request->syllabus_id]);
             }
             
 
             // $details->preferredClasses()->attach(json_decode($request->class_ids));
             if($request->has('class_id'))
             {
                 $details->preferredClasses()->attach([$request->class_id]);
             }
 
             if($request->has('subject_id'))
             {
                 $details->preferredSubjects()->attach([$request->subject_id]);
             }
 
             $educations = $request->has('education') ? $request->education : [];
             if(!empty($educations))
             {
                 foreach($details->education as $education_old){
                     $EduData_old = $details->education->find($education_old['id']);
                     $EduData_old->delete();
                 }
                 foreach($educations as $education){
                    $details->education()->create($education);
                 }
             }
 
             $experiences =  $request->has('experience') ? $request->experience : [];
             if(!empty($experiences))
             {
                 foreach($details->experience as $experience_old){
                     $ExpData_old = $details->experience->find($experience_old['id']);
                     $ExpData_old->delete();
                 }
 
                 foreach($experiences as $experience){
                    $details->experience()->create([
                     "organization" => $experience['organization'],
                     "designation" => $experience['designation'],
                     "experience_month" => intval($experience['experience_month']),
                    ]);
                 }
             }
            
             $availabilities = $request->has('availability') ? $request->availability : [];
             
             /** Delete availability slots that are not passed in API */
             $deleteSlots = $user->availability->filter(function($slot) use($availabilities){
                 return in_array($slot->id, Arr::pluck($availabilities, 'id')) ? false : true;
             });
             $deleteSlots->each->delete();
 
             if(!empty($availabilities))
             {
                 foreach($availabilities as $times){
 
                     $availability = $user->availability()->find($times['id']);
                     if($availability){
                             $availability->day =  $times['day'];
                             $availability->start_time = Carbon::createFromFormat('H:i',$times['start_time']);
                             $availability->end_time = Carbon::createFromFormat('H:i',$times['end_time']);
                             $availability->save();
                     }else{
                         $user->availability()->create([
                             "day" =>  $times['day'],
                             "start_time" => Carbon::createFromFormat('H:i',$times['start_time']),
                             "end_time" => Carbon::createFromFormat('H:i',$times['end_time'])
                         ]);
                     }
                 }
             }
 
             $user = User::find($request->user_id);
             return (new UserResource($user))->additional(["error" => false, "message" => 'Updated Successfully.']);
             //$this->setResponse(false, 'Updated Successfully.');
 
         } catch (\Exception $e) {
             $this->setResponse(true, $e->getMessage());
             return response()->json($this->_response, 500);
         }
     }

    /*Delete User Data*/
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $userDelete =  User::find($request->id);
            if($userDelete) {
                $userDelete->delete();
                // $userDelete->update('soft_delete', 1);
                $this->setResponse(false, 'User deleted from database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

      /* All User Profile List Api  (list)*/
     public function allProfiles()
     {
         try {
             $userProfileData = UserDetails::all();
             return UserDetailsResource::collection($userProfileData)->additional([ "error" => false, "message" => 'Here is all users Profile data']);
         
         } catch (UserNotDefinedException $e) {
             $this->setResponse(true, $e->getMessage());
             return response()->json($this->_response, 500);
         }
     }

     /* Update User Profile Api  (Update)*/
     
     public function updateUserPrfile(Request $request)
     {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'gender'=>'required',
            'phone'=>'required|numeric|min:10',
            'dob'=>'required|date',
            'country'=>'required',
            'nationality'=>'required',
            'aadhar_id'=>'required|numeric|min:16',
            'area'=>'required',
            'city'=>'required',
            'district'=>'required',
            'state'=>'required',
            'geo_location'=>'filled',
            'marital_status'=>'filled',/*'boolean'*/
            'education_details'=>'required',
            'languages'=>'required',
            'professional_details'=>'required',
            'employment_status'=>'filled',
            'total_ratings'=>'filled',
            'avg_ratings'=>'filled',
            'tp_points_balance'=>'filled',
            'verified_status_percentage'=>'required',
            'verified_status'=>'required',
            'fb_url'=>'filled',
            'li_url'=>'filled',
            'tw_url'=>'filled',
            'insta_url'=>'filled',
            'online_cost_per_hour'=>'required|numeric|min:4',
            'offline_cost_per_hour'=>'required|numeric|min:4',
            'institute_cost_per_hour'=>'required|numeric|min:4',
            'tutor_home_cost_per_hour'=>'required|numeric|min:4',
            'student_home_cost_per_hour'=>'required|numeric|min:4',
            'discount_limit'=>'required|numeric|min:1',
            'preferred_board'=>'required',
            'subject_ids'=>'required',
            'topic'=>'required',
            'mode_of_classes'=>'required',
            'request_received'=>'required',
            'request_sent'=>'required',
            'friends_id'=>'required',
            'my_students_ids'=>'required',
            'my_school_id'=>'required',
            'tutor_experience'=>'required|numeric',
            'tutor_commission'=>'required|numeric',
            'notifications'=>'required|min:2|Max:6',
            'parent_ids'=>'required',
         ]);
 
         if ($validator->fails()) {
             $this->setResponse(true, $validator->errors()->all());
             return response()->json($this->_response, 400);
         }
         try {
            
                $userProfileUpdate = UserDetails::where('user_id', $request->user_id??auth()->user()->id)->update($request->all());
                if($userProfileUpdate){
                    $this->setResponse(false, 'User data updated successfully in the database.');
                    return response()->json($this->_response);
                } 
 
         } catch (\Exception $e) {
             $this->setResponse(true, $e->getMessage());
             return response()->json($this->_response, 500);
         }
     }

    // /* Selected User Profile View */
    public function profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
			
            $user = User::where('email', $request->email)->first();
            // $pointData = [
            //     'comment' => 'received points to signup at tutorpark',
            //     'type' => 'received',
            //     'source_of_point' => 'signup'
            // ];

            // $user->availPoints($pointData);
            
            return (new UserResource($user))->additional(["error" => false, "message" => "Retrived user profile successfully"]);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function friendprofile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $user = User::where('email', $request->email)->first();

            return (new FriendProfileResource($user))->additional(["error" => false, "message" => null]);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function impersonate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'email' => 'required|exists:users,email',
            'id' => 'required|exists:users,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            // $token = auth()->user()->actingAs($request->email);
            $user = User::find($request->id);
            // $token = Auth::login($user);
            if(!$user->hasRole('admin'))
            {
                /* Logout user and then imporsonate using anoher use 
                                            OR
                Logout imporsonated user and login with actual user */

                Auth::logout();
                
                $token = Auth::login($user);
                return $this->respondWithToken($token);
            }else{
                $this->setResponse(false, 'You cant impersonate using admin user.','401');
                return response()->json($this->_response, 200);
            }
            // $this->_response['data'] = [
            //     "auth_token" => $token,
            // ];
            
            // return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'data' => (new UserResource(auth()->user())),
            'access_token' => $token,
            'permissions' => PermissionResource::collection(auth()->user()->getAllPermissions()),
            'token_type' => 'bearer',
            'error' => false,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'message' => "Logged in successfully."
        ]);
    }
    
    public function assignrole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $user = User::where('email', $request->email)->first();
            $user->assignRole($request->role);
            return response()->json("true", 200);
        }catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }

    }
    
    public function earnings()
    {
        try{
            if(auth()->user()->hasRole('admin'))
            {
                $earnings = Transaction::Orderby('created_at', 'desc')->get();
            }else{
                $earnings = auth()->user()->earnings;
            }
            
            return TransactionResource::collection($earnings)->additional([ "error" => false, "message" => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Update User Data */
    public function updateForSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_ids' => 'required',
            'subject_id' => 'required|exists:subjects,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
           
            $user = User::where('email', $request->email)->first();
            // $user->first_name = $request->first_name;
            // $user->last_name = $request->last_name;
            // $user->save();

            $details = $user->details;
            
            //save data of 'preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics'
            $details->preferredBoards()->attach([$request->syllabus_id]);
            $details->preferredClasses()->attach(json_decode($request->class_ids));
            $details->preferredSubjects()->attach([$request->subject_id]);

           

            $this->_response = (new UserResource($user))->additional(["error" => false, "message" => 'Updated Successfully.']);
            //$this->setResponse(false, 'Updated Successfully.');
            return $this->_response;

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function expenses()
    {
        try{
            $expenses = auth()->user()->expenses;
            return TransactionResource::collection($expenses)->additional([ "error" => false, "message" => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function inviteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users',
        ],[
            'email.unique' => 'User has already been register with this email.',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $requestAlreadySent = FriendRequest::where("from" , auth()->user()->email)->where("to" , $request->email)->exists();

            if( ! $requestAlreadySent ) {
                $requestData = FriendRequest::create([
                    "from" => auth()->user()->email,
                    "to" => $request->email
                ]);
                $requestData->is_invited = true;
                $requestData->save();
                $mailData = [
                    'sender_name' => auth()->user()->full_name,
                    'sender_image' => url('storage/' . "images/user/".auth()->user()->details->gender.".jpg"),
					'host' => getHost()
                ];
                Mail::to($request->email)->queue(new InviteUser($mailData));

                $this->setResponse(false, "Invitaion sent successfully.");
            }else{
                $this->setResponse(false, "Invitaion already pending.");
            }
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Verify User Email */
    public function VerifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verify_token' => 'required|exists:users,verify_token',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $verifyEmail =  User::where('verify_token', $request->verify_token)->update(['is_verified'=>1,'verify_token'=>'']);

            if($verifyEmail) {
                $this->_response['data'] = '';
                $this->setResponse(false, 'Email verified Successfully.');
                return response()->json($this->_response); 
            } 

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/*Verify User Directly - for developer use purpose */
    public function directVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $verifyEmail =  User::where('email', $request->email)->update(['is_verified'=>1,'verify_token'=>'']);

            if($verifyEmail) {
                $this->_response['data'] = '';
                $this->setResponse(false, 'User verified Successfully.');
                return response()->json($this->_response); 
            } 

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /* Mychilds list */
    public function myChilds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $parent =  User::find($request->parent_id);
            if($parent->childs)
            {
                return UserResource::collection($parent->childs)->additional([ "error" => false, "message" => 'Here is all childs data.']);;
            }else{
                $this->_response['data'] = '';
                $this->setResponse(false, 'Here is all childs data.');
                return response()->json($this->_response); 
            } 

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /* Store Gio Address */
    public function storeGeoLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'], 
            'longitude' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'geo_location' => 'required'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $update =  UserDetails::where('user_id', auth()->user()->id)->update(['latitude'=>$request->latitude,'longitude'=>$request->longitude,'geo_location'=>$request->geo_location]);
            $user = User::find(auth()->user()->id);
            
            return (new UserResource($user))->additional(["error" => false, "message" => "Retrived user profile successfully"]);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	 /* Update hide area option */
    public function updateHideArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hide_area' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $update =  UserDetails::where('user_id', auth()->user()->id)->update(['hide_area'=>$request->hide_area]);
            $user = User::find(auth()->user()->id);
            
            return (new UserResource($user))->additional(["error" => false, "message" => "Geotag area updated successfully."]);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /* Request for access */
    public function requestForAccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $mailData = [
                'user_name' => auth()->user()->full_name,
                'user_email' => auth()->user()->email,
                'user_tpid' => (auth()->user()->details->tp_id)?auth()->user()->details->tp_id:'NA',
                'user_role' => auth()->user()->getRole(),
                'description' => $request->description,
                'email_subject' => 'Request For Access - '. auth()->user()->full_name,
                'email_template' => 'AccessRequest',
				'host' => getHost()
            ];
            
            $adminEmails = User::role('admin')->pluck('email')->toArray();
            
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
	
	/* Test Mail */
    public function testMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $mailData = [
                'description' => 'This is test mail from tutorpark',
                'email_subject' => 'Test Mail - TutorPark',
                'email_template' => 'Test',
            ];
            
            Mail::to($request->email)->queue(new Notification($mailData));

            $this->setResponse(false, 'Request sent successfully.');
            return response()->json($this->_response); 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
