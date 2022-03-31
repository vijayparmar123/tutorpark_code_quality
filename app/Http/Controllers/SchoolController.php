<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use App\Http\Resources\SchoolResource;
use App\Http\Resources\SchoolDropdownResource;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    /**
     * Add School.
     *
     * @return \Illuminate\Http\Response
     */
    public function addSchool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_name' => 'required',
            'registration_no' => 'required',
            'school_pincode' => 'required',
            'school_city' => 'required',
            'school_phone' => 'required',
            'school_email' => 'required|email|unique:schools,email',
            'school_mobile' => 'required|numeric',
            'principal' => 'required',
            'vice_principal' => 'required',
            'incharge' => 'required',
            'working_start_date' => 'nullable',
            'working_end_date' => 'nullable',
            'school_attachment' => 'filled|mimes:jpg,bmp,png,jpeg,svg,pdf|max:2048',
            'school_image' => 'filled|mimes:jpg,bmp,png,jpeg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $school = School::create([
                'type' => 'school_platform',
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
                'is_verified' => false,
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
            
            $this->setResponse(false, 'School added successfully, will appear in list when admin verify it.');
            return response()->json($this->_response); 

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Display a listing of the school.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $schools = School::orderBy('created_at', 'desc')->get();
            return SchoolResource::collection($schools)->additional([ "error" => false, "message" => 'Here is all school data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * School collaboration list.
     *
     * @return \Illuminate\Http\Response
     */
    public function schoolCollaborationList()
    {
        try{
            $schools = School::where(['type' => 'school_collaboration'])->orderBy('created_at', 'desc')->get();
            return SchoolResource::collection($schools)->additional([ "error" => false, "message" => 'Here is all collaboration school data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * School collaboration list.
     *
     * @return \Illuminate\Http\Response
     */
    public function schoolPlatformList()
    {
        try{
            $schools = School::where(['type' => 'school_platform'])->orderBy('created_at', 'desc')->get();
            return SchoolResource::collection($schools)->additional([ "error" => false, "message" => 'Here is all school platform data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Verified School list.
     *
     * @return \Illuminate\Http\Response
     */
    public function verifiedSchoolList()
    {
        try{
            $schools = School::where(['is_verified' => true])->orderBy('created_at', 'desc')->get();
            return SchoolResource::collection($schools)->additional([ "error" => false, "message" => 'Here is all verified school data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * School dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function schoolDropdown()
    {
        try{
            $schools = School::where(['is_verified'=>true])->select(['_id','school_name'])->get();
            return SchoolDropdownResource::collection($schools)->additional([ "error" => false, "message" => 'Here is all school data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Verify School
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifySchool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $school = School::find($request->school_id);
            $school->is_verified = true;
            $school->verified_by = auth()->user()->_id;
            $school->save();

            $this->setResponse(false,'School verified successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Join School
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function joinSchool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $authUser = auth()->user();
            $school = School::find($request->school_id);
            
            if($authUser->linked_email)
            {
                $this->setResponse(true, "You already have joined school.");
                return response()->json($this->_response, 200);
            }

            if($authUser->hasRole('student'))
            {
                $schoolRole = 'school-student';
            }elseif($authUser->hasRole('tutor'))
            {
                $schoolRole = 'school-tutor';
            }else{
                $this->setResponse(true, "You are not allowed to join a school.");
                return response()->json($this->_response, 200);
            }
            
            $modifyEmail = explode('@',$authUser->email);
            $schoolEmail = $modifyEmail[0].'+'.$schoolRole.'@'.$modifyEmail[1];

            //Check user exist already with same email
            $checkDuplication = User::where(['email'=>$schoolEmail])->count();

            if($checkDuplication)
            {
                $this->setResponse(true, "User exist already with email - ".$schoolEmail);
                return response()->json($this->_response, 200);
            }

            $schoolUser = User::create([
                'first_name' => $authUser->first_name,
                'last_name' => $authUser->last_name,
                'email' => $schoolEmail,
                'password' => $authUser->password,
                'is_verified' => 1,
                'linked_email' => $authUser->email,
            ]);
            
            $schoolUser->assignRole($schoolRole);

            $details = $schoolUser->details()->create([
                "gender" => ($authUser->details->gender)?$authUser->details->gender:'',
                "address" => ($authUser->details->address)?$authUser->details->address:'',
                "area" => ($authUser->details->address)?$authUser->details->address:'',
                "state" => ($authUser->details->state)?$authUser->details->state:'',
                "city" => ($authUser->details->city)?$authUser->details->city:'',
                "country" => ($authUser->details->country)?$authUser->details->country : 'India',
                "nationality" => ($authUser->details->nationality)?$authUser->details->nationality : 'Indian',
                "pincode" => ($authUser->details->pincode)?$authUser->details->pincode:'',
                "phone" => ($authUser->details->phone)?$authUser->details->phone:'',
                "aadhar_id" => ($authUser->details->aadhar_id)?$authUser->details->aadhar_id:'',
                "birth_date" => ($authUser->details->birth_date)?$authUser->details->birth_date:'',
                'fb_url' => $authUser->details->fb_url !== '' ? $authUser->details->fb_url : null,
                'li_url' => $authUser->details->li_url !== '' ? $authUser->details->li_url : null,
                'tw_url' => $authUser->details->tw_url !== '' ? $authUser->details->tw_url : null,
                'insta_url' => $authUser->details->insta_url !== '' ? $authUser->details->insta_url : null,
            ]);

            // Set verified and password again, because of modification on model
            $schoolUser->update(['is_verified'=>1,'password'=>$authUser->password]);

            // Link new user email with login user
            $authUser->update(['linked_email'=>$schoolEmail]);

            // Associate school to new user
            $schoolUser->school()->associate($school)->save();

            $this->setResponse(false,'School joined successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
