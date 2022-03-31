<?php

namespace App\Http\Controllers;

use App\Http\Resources\TuitionAddStudentListResource;
use App\Models\Tuition;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function getStudents(Request $request)
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

            $student_ids = UserDetails::whereIn('preferred_subjects', [$tuition->subject_id])->whereNotIn('subscribed_tuitions', [$tuition->_id])->pluck('user_id');
            $students = User::role('student')->whereIn('_id',$student_ids)->get();

            return TuitionAddStudentListResource::collection($students)->additional([ "error" => false, "message" => null]);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function getAllStudents()
    {

        try{
             $students = User::role('student')->get();
            return TuitionAddStudentListResource::collection($students)->additional([ "error" => false, "message" => null]);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
