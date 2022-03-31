<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubjectListResource;
use Illuminate\Http\Request;
use Exception;
use App\Http\Resources\SubjectResource;
use Illuminate\Support\Facades\Validator;
use App\Models\Subject;
class SubjectController extends Controller
{
    /**
     * Subject Record List Show.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'exists:classes,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            // $subjectData = Subject::all();
            $subjectData = Subject::whereClassIn($request->class_id)->Orderby('created_at', 'desc')->get();
            return SubjectResource::collection($subjectData)->additional([ "error" => false, "message" => null]);
        } catch (Exception $e) {
        $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    public function list()
    {

        try {
            $subjectData = Subject::Orderby('created_at', 'desc')->get();
            return SubjectListResource::collection($subjectData)->additional(["error" => false, "message" => "Subject list data"]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * New Subject Insert .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'class_id' => 'required|exists:classes,_id',
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $subject =  Subject::create($request->except('class_id'));
            $subject->classes()->attach($request->class_id);
            
            if($subject) {
                $this->_response['data'] = $subject;
                $this->setResponse(false,'Subject added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * View Subject.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $subject =  Subject::find($request->id);
            
            if($subject) {
                return (new SubjectResource($subject))->additional(["error" => false, "message" => 'Subject data for Show']);
            } else {
                $this->setResponse(true, 'Subject Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Edit Subject
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $subject =  Subject::find($request->id);
            
            if($subject) {
                return (new SubjectResource($subject))->additional(["error" => false, "message" => 'Subject data for edit']);
            } else {
                $this->setResponse(true, 'Subject Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Subject.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'name' => 'required',
            'class_id' => 'required'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            
            $subjectUpdate = Subject::find($request->id);
            $subjectUpdate->classes()->detach($subjectUpdate->class_ids);
            $subjectUpdate->update($request->except('class_id'));
            $subjectUpdate->classes()->attach($request->class_id);
            
            if($subjectUpdate) {
                $this->setResponse(false, 'Subject data updated successfully in the database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Delete Subject.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
            $subjectDelete =  Subject::find($request->id);
            if($subjectDelete) {
                $subjectDelete->delete();
                $this->setResponse(false, 'Subject deleted from database.');
                return response()->json($this->_response);
            } 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // get list of Subject for deropdown item
    public function getSubjectDropdownList(){
        try {
            $subject= Subject::all()->pluck('name','id');
            
            if($subject) {
                $this->_response['data'] = $subject;
                $this->setResponse(false,'Subject list for dropdown');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
