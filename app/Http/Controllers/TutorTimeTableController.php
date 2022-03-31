<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TutorTimeTable;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Resources\TutorTimeTableResource;
// use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Validator;
class TutorTimeTableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $libraryData = TutorTimeTable::Orderby('created_at', 'desc')->get();
            return TutorTimeTableResource::collection($libraryData)->additional([ "error" => false, "message" => 'Here is all Library data']);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'=>'required',
            'end_date'=>'required',
            'week_day'=>'required',
            'times'=>'required',
            'teaching_mode'=>'required',
            'tutor_id'=>'required'
            

        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $timetable = TutorTimeTable::create($request->all());
            
            if($timetable) {
                // Avail point for Create Timetable
                $pointData = [
                    'comment' => 'received points for creating timetable',
                    'transaction_type' => 'received',
                    'source_of_point' => 'create_timetable'
                ];
                
                auth()->user()->availPoints($pointData);

                $this->_response['data'] = $timetable;
                $this->setResponse(false, 'Timetable added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
