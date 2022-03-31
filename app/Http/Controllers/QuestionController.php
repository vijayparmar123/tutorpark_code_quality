<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionType;
use App\Models\QuestionBank;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\QuestionTypeResource;
use App\Http\Resources\QuestionBankResource;

class QuestionController extends Controller
{
    // Question types list
    public function questionTypes()
    {
        try {
            $questionTypes = QuestionType::all();
            return QuestionTypeResource::collection($questionTypes)->additional([ "error" => false, "message" => 'Here is question types data.']);
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Store Question
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => 'required|exists:classes,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'type' => 'required|exists:question_types,tag',
            "questions" => "required|array",
            "questions.*" => "required",
            "answers" => "required_if:type,q_a,mcq,blanks|array",
            "answers.*" => "required",
            "options" => "required_if:type,mcq|array",
            "options.*" => "required|array",
            "options.*.*" => "required",
            "comprehensive_questions" => "required_if:type,comprehension|array",
            "comprehensive_questions.*" => "required",
            "comprehensive_answers" => "required_if:type,comprehension|array",
            "comprehensive_answers.*" => "required",
			"matchoptions_left" => "required_if:type,match_following|array",
            "matchoptions_left.*" => "required",
			"matchoptions_right" => "required_if:type,match_following|array",
            "matchoptions_right.*" => "required",
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $questions = array();
            foreach($request->questions as $key=>$value)
            {
                $question = QuestionBank::create([
                    'syllabus_id' => $request->syllabus_id,
                    'class_id' => $request->class_id,
                    'subject_id' => $request->subject_id,
                    'syllabus_id' => $request->syllabus_id,
                    'question' => $value,
                    'created_by' => auth()->user()->id,
                ]);
                $questions[] = $question;

                switch ($request->type) {
                    case 'comprehension':
                        foreach($request->comprehensive_questions as $key=>$value){
                            $comprehensiveQuestion =  $question->comprehensiveQuestions()->create([
                                'question' => $value
                            ]);

                            $comprehensiveQuestion->answer()->create([
                                'answer' => $request->comprehensive_answers[$key]
                            ]);

                        }
                    break;
                        
                    case 'q_a':
                        // Question answer store in database
                        $answer = $question->answer()->create([
                            'answer' => $request->answers[$key]
                        ]);
                    break;
                        
                    case 'mcq':
                        // MCQ options and correct answer store in database
                        $answer = $question->answer()->create([
                            'answer' => $request->answers[$key]
                        ]);
                        
                        foreach($request->options[$key] as $option){
                            $question->options()->create([
                                'name' => $option
                            ]);
                        }
                    break;

                    case 'blanks':
                        // Blanks answer store in database
                        $answer = $question->answer()->create([
                            'answer' => $request->answers[$key]
                        ]);
                    break;

                    case 'match_following':
                        foreach($request->matchoptions_left as $option_key=>$option_value){
                            $leftOption =  $question->options()->create([
                                'type' => 'left',
                                'name' => $option_value
                            ]);

                            $rightOption =  $question->options()->create([
                                'type' => 'right',
                                'name' => $request->matchoptions_right[$option_key]
                            ]);

                            $leftOption->matching()->associate($rightOption)->save();
                            $rightOption->matching()->associate($leftOption)->save();
                        }
                    break;
                }
                
            }

            // Attach all above added questions with selected question type
            $questionType = QuestionType::where(['tag' => $request->type])->first();
            $type = $questionType->questions()->saveMany($questions);
            
            $this->setResponse(false, 'Question added successfully.');
            return response()->json($this->_response, 200);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Question List
    public function questionList()
    {        
        try {
            $questionsList = QuestionBank::Orderby('created_at', 'desc')->get();
            return QuestionBankResource::collection($questionsList)->additional([ "error" => false, "message" => 'Here is all questions data.']);
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Question types list
    public function filterQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => 'required|exists:classes,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'type_id' => 'required|exists:question_types,_id'
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $questionsList = QuestionBank::where([
                'syllabus_id' => $request->syllabus_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'question_type_id' => $request->type_id,
            ])->Orderby('created_at', 'desc')->get();
            
            return QuestionBankResource::collection($questionsList)->additional([ "error" => false, "message" => 'Here is all questions data.']);
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Get specific question details
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:question_banks,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $questionsList = QuestionBank::find($request->question_id);
            
            return (new QuestionBankResource($questionsList))->additional([ "error" => false, "message" => 'Here is all questions data.']);
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Get specific question details
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:question_banks,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $question =  QuestionBank::find($request->question_id);

            $question->delete();
            $this->setResponse(false, 'Question deleted successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
