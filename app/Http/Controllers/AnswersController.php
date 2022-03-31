<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\Answers;
use App\Jobs\UpdateTutorStatus;
use App\Http\Resources\AnswersResource;
use Illuminate\Support\Facades\Validator;

class AnswersController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // public function index()
    // {
    //     try {
    //         $AnswersData = Answers::all();
    //         return AnswersResource::collection($AnswersData)->additional([ "error" => false, "message" => null]);
    //     } catch (Exception $e) {
    //         $this->setResponse(true, $e->getMessage());
    //         return response()->json($this->_response, 500);
    //     }
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required',
            'answer'=>'max:500',
			'library_id' => 'filled|exists:libraries,_id',
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $AnswersStore = Answers::create($request->all());
            
            // if($request->has('image')){
            //     $image = $this->uploadFile($request->image,'images/notebook/');
            //     if($image != false){
            //         $noteBookStore->image = $image;
            //     }
            // }

            if($AnswersStore->save()) {
                if(auth()->user()->hasRole('tutor'))
                {
                    // Avail point for answered a question
                    $pointData = [
                        'comment' => 'received points for answered a question',
                        'transaction_type' => 'received',
                        'source_of_point' => 'answered_question'
                    ];
                    
                    auth()->user()->availPoints($pointData);
					
					// Update tutor status
					$data = [
						'type' => 'answer_given',
						'tutor_id' => auth()->user()->id
					];
					dispatch(new UpdateTutorStatus($data));
                }


                //$this->_response['data'] = $noteBookStore;
                $this->setResponse(false, 'Answers added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Update Answer Data */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:answers,_id',
            'question_id' => 'required',
            'answer'=>'max:500',
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $answersUpdate = Answers::find($request->id);
           
            $answersUpdate->update($request->all());

            // if($request->has('image')){
            //     $picName = 'images/notebook/' . getUniqueStamp() . '.' .$request->image->extension();
            //     $request->image->storeAs('public', $picName);
            //     $notebookUpdate->update(['image' => $picName]);
            // }
            if($answersUpdate){
                $this->_response['data'] = $answersUpdate;
                $this->setResponse(false,'Answer data updated successfully in the database.');
                return response()->json($this->_response);
            }
 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    /*Addbest Answer Data */
    public function addBestAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:answers,_id',
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            
            $answersUpdate = Answers::find($request->id);
            if(isset($answersUpdate->best_answer)){
                $this->setResponse(true, "Already this answer is best answer.");
                return response()->json($this->_response, 400);
            }
            else{
                $answersUpdate->best_answer = auth()->user()->id;
                $answersUpdate->save();

                if($answersUpdate){
                    $this->setResponse(false, "Successfully Marked as Best Answer.");
                    return response()->json($this->_response, 200);
                }
            }
 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
