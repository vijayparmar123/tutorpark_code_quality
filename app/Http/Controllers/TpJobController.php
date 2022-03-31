<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobResource;
use App\Models\TpJob;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TpJobController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'exists:syllabuses,_id',
            'class_id' => 'exists:classes,_id',
            'subject_id' => 'exists:subjects,_id',
            // 'topic_id' => 'exists:topics,_id',
            'topic' => 'required:max:100',
            'city' => 'filled|max:50',
            'topic' => 'filled|max:50',
            'mode' => 'filled|max:50',
            'type' => 'filled|max:50',
            'gender' => 'filled|max:10',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            // $jobs = TpJob::all();
            $mode = $request->mode;
            $type = $request->type;
            $topic = $request->topic;
            $jobs = TpJob::whereSyllabus($request->syllabus_id)
                        ->whereClass($request->class_id)
                        ->whereSubject($request->subject_id)
                        // ->whereTopic($request->topic_id)
                        ->when($mode, function($query, $mode){
                            return $query->where('mode', 'like', "%{$mode}%");
                        })
                        ->when($type, function($query, $type){
                            return $query->where('type', 'like', "%{$type}%");
                        })
                        ->when($topic, function($query, $topic){
                            return $query->where('topic', 'like', "%{$topic}%");
                        })
                        ->Orderby('created_at', 'desc')
                        ->get();
            
            if($request->has('city')){
                $city = $request->city;
                $jobs = $jobs->filter(function($job) use($city){
                    return $job->isCity($city);
                });
            }

            if($request->has('gender')){
                $gender = $request->gender;
                $jobs = $jobs->filter(function($job) use($gender){
                    return $job->isGender($gender);
                });
            }


            return (JobResource::collection($jobs))->additional(["error" => false, "message" => null]);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required','exists:classes,_id', new SyllabusExists],
            'subject_id' => ['required','exists:subjects,_id', new ClassExists],
            'mode' => 'required',
            'type' => 'required',
            'topic' => 'required|regex:/^[\pL\pN\s\-\_\']+$/u|max:150',
            'requirements' => 'required|max:500',
            // 'requirements' => 'required|regex:/^[\pL\pN\s\-\_\'\.]+$/u|max:500',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            
            $job = TpJob::create($request->all());
            $job->created_by = auth()->id();
            $job->save();

            if(auth()->user()->hasRole('student'))
            {
                // Avail point for answered a question
                $pointData = [
                    'comment' => 'received points for post a job',
                    'transaction_type' => 'received',
                    'source_of_point' => 'post_job'
                ];
                
                auth()->user()->availPoints($pointData);
            }

            $this->setResponse(false, 'Job Posted Successfully.');
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

}
