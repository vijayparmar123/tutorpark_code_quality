<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionsResource;
use App\Mail\Notification;
use App\Models\DivisionSubjectTeacher;
use App\Models\Questions;
use App\Models\User;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class QuestionsController extends Controller
{
    /**
     * Question Record List.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $questionsData = Questions::where('created_by', auth()->user()->id)->Orderby('created_at', 'desc')->get();
            return QuestionsResource::collection($questionsData)->additional(["error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add New Data insert Questions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'topic_name' => 'required:max:100',
            'question' => 'max:100',
        ];

        if (auth()->user()->hasRole('student')) {
            $rules['syllabus_id'] = 'required|exists:syllabuses,_id';
            $rules['class_id'] = ['required', 'exists:classes,_id', new SyllabusExists];
            $rules['subject_id'] = ['required', 'exists:subjects,_id', new ClassExists];
        }

        if (auth()->user()->hasRole('school-student')) {
            $rules['subject_id'] = 'required|exists:subjects,_id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {

            if (auth()->user()->hasRole('school-student')) {
                $division_id = auth()->user()->getDivisionIds();
                $request->request->add(['created_by' => auth()->user()->id, 'division_id' => (count($division_id))?$division_id[0]:null]);
                $questionStore = Questions::create($request->all());

                if ($questionStore->save()) {

                    if (auth()->user()->hasRole('school-student')) {
                        // Avail point for answered a question
                        $pointData = [
                            'comment' => 'spent points for post a question',
                            'transaction_type' => 'spent',
                            'source_of_point' => 'post_question',
                        ];

                        auth()->user()->availPoints($pointData);
                    }

                    if(count($division_id))
                    {
                        // Matching tutor emails
                        $teacher_id = DivisionSubjectTeacher::where(['class_division_id'=>$division_id[0], 'subject_id'=>$request->subject_id])->first();
                        $teacher = User::find($teacher_id->teacher_id);
                        
                        if($teacher->email)
                        {
                            $mailData = [
                                'student_name' => auth()->user()->full_name,
                                'student_email' => auth()->user()->email,
                                'student_tpid' => (auth()->user()->details->tp_id) ? auth()->user()->details->tp_id : 'NA',
                                'question' => $request->question,
                                'host' => getHost(),
                                'email_subject' => 'New question assigned',
                                'email_template' => 'QuestionNotification',
                            ];
        
                            Mail::to($teacher->email)->queue(new Notification($mailData));
                        }
                    }
                }

                //$this->_response['data'] = $noteBookStore;
                $this->setResponse(false, 'Question added successfully.');
                return response()->json($this->_response);

            } else {
                $request->request->add(['created_by' => auth()->user()->id]);
                $questionStore = Questions::create($request->all());

                /*if($request->has('image')){
                $image = $this->uploadFile($request->image,'images/notebook/');
                if($image != false){
                $noteBookStore->image = $image;
                }
                } */

                if ($questionStore->save()) {

                    if (auth()->user()->hasRole('student')) {
                        // Avail point for answered a question
                        $pointData = [
                            'comment' => 'spent points for post a question',
                            'transaction_type' => 'spent',
                            'source_of_point' => 'post_question',
                        ];

                        auth()->user()->availPoints($pointData);
                    }

                    // Matching tutor emails
                    $emails = User::role('tutor')->with(['details' => function ($query) use ($request) {
                        $query->whereIn('preferred_boards', [$request->syllabus_id])
                            ->whereIn('preferred_classes', [$request->class_id])
                            ->whereIn('preferred_subjects', [$request->subject_id]);
                    }])->pluck('email');

                    $mailData = [
                        'student_name' => auth()->user()->full_name,
                        'student_email' => auth()->user()->email,
                        'student_tpid' => (auth()->user()->details->tp_id) ? auth()->user()->details->tp_id : 'NA',
                        'question' => $request->question,
                        'host' => getHost(),
                        'email_subject' => 'New question assigned',
                        'email_template' => 'QuestionNotification',
                    ];

                    foreach ($emails as $email) {
                        Mail::to($email)->queue(new Notification($mailData));
                    }
                }

                //$this->_response['data'] = $noteBookStore;
                $this->setResponse(false, 'Question added successfully.');
                return response()->json($this->_response);
            }

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Update User Data */
    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:questions,_id',
            'topic_name' => 'required:max:100',
            'question' => 'max:100',
        ];

        if (auth()->user()->hasRole('student')) {
            $rules['syllabus_id'] = 'required|exists:syllabuses,_id';
            $rules['class_id'] = ['required', 'exists:classes,_id', new SyllabusExists];
            $rules['subject_id'] = ['required', 'exists:subjects,_id', new ClassExists];
        }

        if (auth()->user()->hasRole('school-student')) {
            $rules['subject_id'] = 'required|exists:subjects,_id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $request->request->add(['created_by' => auth()->user()->id]);
            $questionUpdate = Questions::find($request->id);

            $questionUpdate->update($request->all());

            // if($request->has('image')){
            //     $picName = 'images/notebook/' . getUniqueStamp() . '.' .$request->image->extension();
            //     $request->image->storeAs('public', $picName);
            //     $notebookUpdate->update(['image' => $picName]);
            // }
            if ($questionUpdate) {
                $this->_response['data'] = $questionUpdate;
                $this->setResponse(false, 'Question data updated successfully in the database.');
                return response()->json($this->_response);
            }

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Update Like & Dislike Data */
    public function addLikeDislike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:questions,_id',
            'like_type' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $questionUpdate = Questions::find($request->id);
            if ($request->like_type == 'like') {
                $questionUpdate->like = $questionUpdate->like ? (int) (($questionUpdate->like) + 1) : 1;
            } else {
                $questionUpdate->dislike = $questionUpdate->dislike ? (int) (($questionUpdate->dislike) + 1) : 1;
            }
            $questionUpdate->save();

            if ($questionUpdate) {
                $this->_response['data'] = [
                    'like' => $questionUpdate->like ? $questionUpdate->like : 0,
                    'dislike' => $questionUpdate->dislike ? $questionUpdate->dislike : 0,
                ];
                $this->setResponse(false, 'Add ' . $request->like_type . ' in Question.');
                return response()->json($this->_response);
            }

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:questions,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $question = Questions::find($request->id);
            $question->delete();
            $this->setResponse(false, "Question Deleted Successfully.");
            return response()->json($this->_response, 200);
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // filter list for question list
    public function filterList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'exists:syllabuses,_id',
            'class_id' => 'exists:classes,_id',
            'subject_id' => 'exists:subjects,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $questionsData = Questions::whereSyllabus($request->syllabus_id)
                ->whereClass($request->class_id)
                ->whereSubject($request->subject_id)
                ->Orderby('created_at', 'desc')->get();
            return QuestionsResource::collection($questionsData)->additional(["error" => false, "message" => null]);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }

    }
}
