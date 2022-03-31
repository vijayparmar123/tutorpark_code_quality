<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClassesResource;
use App\Models\TpClass;
// use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    /**
     * Display Class Record List.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'exists:syllabuses,_id',
            'level_id' => 'exists:levels,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $classData = TpClass::whereSyllabus($request->syllabus_id)->whereLevel($request->level_id)->Orderby('created_at', 'desc')->get();
            return ClassesResource::collection($classData)->additional(["error" => false, "message" => null]);
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
    public function create(Request $request)
    {
        //
    }

    /**
     * New Class Add .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'syllabus_id' => 'exists:syllabuses,_id',
            'level_id' => 'required|exists:levels,_id',
            'status' => 'filled',

        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $class = TpClass::create($request->all());

            if ($class) {
                $this->_response['data'] = $class;
                $this->setResponse(false, 'Class added successfully in the database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * View Class Record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:classes,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $class =  TpClass::find($request->id);

            if ($class) {
                return (new ClassesResource($class))->additional(["error" => false, "message" => 'Class data for Show']);
            } else {
                $this->setResponse(true, 'Class Data not found.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Edit Class Record.
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
            $class =  TpClass::find($request->id);

            if ($class) {
                return (new ClassesResource($class))->additional(["error" => false, "message" => 'Class data for edit']);
            } else {
                $this->setResponse(true,  'Class Data not found.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Class Record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:classes,_id',
            'name' => 'required',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'level_id' =>  'required|exists:levels,_id',
            'status' => 'filled',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $classUpdate = TpClass::find($request->id);
            $classUpdate->update($request->all());
            // $classUpdate->update($request->except('syllabus_id','level_id'));
            if ($classUpdate) {
                $this->setResponse(false, 'Class data updated successfully in the database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Delete Class Record.
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
            $classDelete = TpClass::find($request->id);
            if ($classDelete) {
                $classDelete->delete();
                $this->setResponse(false, 'Class deleted from database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    // get list of Class for deropdown item
    public function getClassDropdownList()
    {
        try {
            $subject = TpClass::all()->pluck('name', 'id');

            if ($subject) {
                $this->_response['data'] = $subject;
                $this->setResponse(false, 'Class list for dropdown');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
