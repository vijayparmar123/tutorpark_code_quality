<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Http\Resources\SyllabusResource;
use Illuminate\Support\Facades\Validator;

class SyllabusController extends Controller
{

    /**
     *Syllabus Record List Show
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        try {
            $syllabuses = Syllabus::Orderby('created_at', 'desc')->get();
            return SyllabusResource::collection($syllabuses)->additional(["error" => false, "message" => null]);
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
     * New Syllabus Add 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'active' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $syllabusStore = Syllabus::create($request->all());

            if ($syllabusStore) {
                $this->_response['data'] = $syllabusStore;
                $this->setResponse(false, 'Syllabus added successfully in the database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * View Syllabus.
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
            $syllabus =  Syllabus::find($request->id);

            if ($syllabus) {
                return (new SyllabusResource($syllabus))->additional(["error" => false, "message" => 'Syllabus data for Show ']);
            } else {
                $this->setResponse(true, 'Syllabus Data not found.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Edit Syllabus
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
            $syllabus =  Syllabus::find($request->id);

            if ($syllabus) {
                return (new SyllabusResource($syllabus))->additional(["error" => false, "message" => 'Syllabus data for edit']);
            } else {
                $this->setResponse(true, 'Syllabus Data not found.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Syllabus
     *  
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'active' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $syllabusUpdate = Syllabus::find($request->id);
            $syllabusUpdate->update($request->all());
            if ($syllabusUpdate) {
                $this->setResponse(false, 'Syllabus data updated successfully in the database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     *  Delete Syllabus 
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
            $syllabusDelete = Syllabus::find($request->id);
            if ($syllabusDelete) {
                $syllabusDelete->delete();
                $this->setResponse(false, 'Syllabus deleted from database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // get list of Syllabus for deropdown item
    public function getSyllabusDropdownList()
    {
        try {
            $syllabus = Syllabus::where(['active'=>'yes'])->get()->pluck('name', 'id');

            if ($syllabus) {
                $this->_response['data'] = $syllabus;
                $this->setResponse(false, 'Syllabus list for dropdown');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
