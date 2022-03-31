<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Questions;
use App\Http\Resources\FeedbackResource;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    /**
     * Feedback Record List.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        try {
            $feedbackData = Feedback::Orderby('created_at', 'desc')->get();
            return FeedbackResource::collection($feedbackData)->additional([ "error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * who added Feedback Record List.
     *
     * @return \Illuminate\Http\Response
    */
    public function addedFeedbackList()
    {
        try {
            $feedbackData = Feedback::where('given_by',auth()->user()->id)->Orderby('created_at', 'desc')->get();
            return FeedbackResource::collection($feedbackData)->additional([ "error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * who got Feedback Record List.
     *
     * @return \Illuminate\Http\Response
    */
    public function gotFeedbackList()
    {
        try {
            $feedbackData = Feedback::where('feedback_for_id',auth()->user()->id)->Orderby('created_at', 'desc')->get();
            return FeedbackResource::collection($feedbackData)->additional([ "error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

     /**
     * Add New Data insert Feedback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'max:100',
            'detailed_feedback' => 'required:max:500',
            'feedback_for' => 'required:max:100',
            // 'feedback_reference_id' => 'required|exists:questions,_id',
            'feedback_reference_id' => 'required',
            'total_ratings' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $today = Carbon::now();

            $request->request->add(['given_by' => auth()->user()->id]);
            $request->request->add(['date' => getDateTime($today)]);
            if($request->feedback_for=="question"){
                $questiondata = Questions::find($request->feedback_reference_id);
                $request->request->add(['feedback_for_id' => $questiondata->created_by]);
            }

            $feedbackStore = Feedback::create($request->all());
            
            if($feedbackStore->save()) {
                $this->setResponse(false, 'Feedback added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
