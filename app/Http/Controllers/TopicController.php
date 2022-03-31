<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\Topic;
use App\Http\Controllers\Controller;
use App\Http\Resources\TopicResource;
use Illuminate\Support\Facades\Validator;
class TopicController extends Controller
{
    /**
     * Topic Record List Show.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        try {
            $topicData = Topic::Orderby('created_at', 'desc')->get();
            return TopicResource::collection($topicData)->additional([ "error" => false, "message" => 'Here is all Topic data']);
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
     * New Topic Insert.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'key_points' => 'required',
            'assignment_id'=>'filled',
            'external_urls'=> 'filled',
            // 'required|regex:'.$regex,
            'description'=>'required|min:20|max:500',
            'subject_id'=>'required'

        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $topicStore = Topic::create($request->all());
            
            if($topicStore) {
                $this->_response['data'] = $topicStore;
                $this->setResponse(false, 'Topic added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true,  $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * View Topic .
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
            $topic =  Topic::find($request->id);
            
            if($topic) {
                return (new TopicResource($topic))->additional(["error" => false, "message" => 'Topic data for Show']);
            } else {
                $this->setResponse(true, 'Topic Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Edit Topic .
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
            $topic =  Topic::find($request->id);
            
            if($topic) {
                return (new TopicResource($topic))->additional(["error" => false, "message" => 'Topic data for edit']);
            } else {
                $this->setResponse(true, 'Topic Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Topic .
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'key_points' => 'required',
            'assignment_id'=>'filled',
            'external_urls'=>'filled',
            'description' => 'required|min:20|max:500',
            'subject_id'=>'required'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $topicUpdate = Topic::where($request->id)->update($request->all());
            if($topicUpdate) {
                $this->setResponse(false,'Topic data updated successfully in the database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Delete Topic .
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
            $topicDelete =  Topic::find($request->id);
            if($topicDelete) {
                $topicDelete->delete();
                $this->setResponse(false, 'Topic deleted from database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    // get list of Topic for deropdown item
    public function getTopicDropdownList(){
        try {
            $topic= Topic::all()->pluck('name','id');
            
            if($topic) {
                $this->_response['data'] = $topic;
                $this->setResponse(false,'Topic list for dropdown');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
