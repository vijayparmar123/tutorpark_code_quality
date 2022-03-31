<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\AssignmentPublishNotification;
use App\Models\User;
use App\Models\Section;
use App\Models\Assignment;
use App\Models\StudentAssignment;
use App\Models\StudentAssignmentSection;
use App\Models\StudentAssignmentSectionQuestion;
use App\Http\Resources\UserpluckResource;
use App\Http\Resources\SectionResource;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\TutorAssignmentResource;
use App\Http\Resources\StudentAssignmentResource;
use App\Http\Resources\StudentSubmittedAssignmentListResource;
use App\Http\Resources\SubmittedAssignmentResource;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    /**
     * Assignment sections list.
     *
     * @return \Illuminate\Http\Response
     */
    public function sections()
    {
        try {
            $sections = Section::all();
            return SectionResource::collection($sections)->additional([ "error" => false, "message" => 'Here is all sections data']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
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
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => 'required|exists:classes,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'title' => 'required',
            'total_mark' => 'required|numeric',
            'image' => 'filled|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'sections' => 'required|array',
            'sections.*' => 'required|exists:sections,_id',
            'section_type' => 'required|array',
            'section_type.*' => 'required|exists:question_types,_id',
            'section_description' => 'required|array',
            'section_description.*' => 'required',
            'section_mark' => 'required|array',
            'section_mark.*' => 'required|numeric',
            'questions' => 'required|array',
            'questions.*' => 'required|array',
            'questions.*.*' => 'required|exists:question_banks,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        // dd($request);

        try {
        
            $assignment = Assignment::create($request->except(['image']));

            if ($request->has('image')) {
                $image = $this->uploadFile($request->image, 'assignment/image');
                if ($image != false) {
                    $assignment->image = $image;
                }
            }

            $assignment->save();

            foreach($request->sections as $key=>$value)
            {
                $assignmentSection =  $assignment->sections()->create([
                    'section_id' => $value,
                    'section_type' => $request->section_type[$key],
                    'description' => $request->section_description[$key],
                    'total_marks' => $request->section_mark[$key],
                ]);

                foreach($request->questions[$key] as $secondkey=>$secondvalue)
                {
                    $assignmentSection->questions()->create([
                        'question_id' => $secondvalue,
                        'mark' => $request->question_mark[$key][$secondkey]
                    ]);
                }
            }
            $this->setResponse(false, 'Assignment added successfully.');
            return response()->json($this->_response, 200);


        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Assignment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,_id',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => 'required|exists:classes,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'title' => 'required',
            'total_mark' => 'required|numeric',
            'image' => 'filled|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'sections' => 'required|array',
            'sections.*' => 'required|exists:sections,_id',
            'section_type' => 'required|array',
            'section_type.*' => 'required|exists:question_types,_id',
            'section_description' => 'required|array',
            'section_description.*' => 'required',
            'section_mark' => 'required|array',
            'section_mark.*' => 'required|numeric',
            'questions' => 'required|array',
            'questions.*' => 'required|array',
            'questions.*.*' => 'required|exists:question_banks,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        // dd($request);

        try {
            //Check assignment is published or not
            $assignment = Assignment::find($request->assignment_id);
            
            if($assignment->is_released)
            {
                $this->setResponse(false, "You can't update published assignment.");
                return response()->json($this->_response, 200);
            }
            $assignment->update($request->toArray());
            
            if ($request->has('image')) {
                $image = $this->uploadFile($request->image, 'assignment/image');
                if ($image != false) {
                    $assignment->image = $image;
                }
            }

            $assignment->save();

            // Delete all old sections of assignment
            $assignment->sections()->each(function($section) {
                $section->delete(); 
            });

            foreach($request->sections as $key=>$value)
            {
                $assignmentSection =  $assignment->sections()->create([
                    'section_id' => $value,
                    'section_type' => $request->section_type[$key],
                    'description' => $request->section_description[$key],
                    'total_marks' => $request->section_mark[$key],
                ]);

                foreach($request->questions[$key] as $secondkey=>$secondvalue)
                {
                    $assignmentSection->questions()->create([
                        'question_id' => $secondvalue,
                        'mark' => $request->question_mark[$key][$secondkey]
                    ]);
                }
            }
            $this->setResponse(false, 'Assignment updated successfully.');
            return response()->json($this->_response, 200);


        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Assignment list
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function myAssignment()
    {
        try {
            if(auth()->user()->hasRole('tutor'))
            {
                return (new TutorAssignmentResource(auth()->user()))->additional([ "error" => false, "message" => 'Here is all assignments data']);
            }else{
                return (new StudentAssignmentResource(auth()->user()))->additional([ "error" => false, "message" => 'Here is all assignments data']);
            }
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function view(request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:assignments,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $assignment = Assignment::find($request->id);
            return (new AssignmentResource($assignment))->additional([ "error" => false, "message" => 'Here is assignments data']);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        } 
    }

    /**
     * Remove the specified assignment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:assignments,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $assignment = Assignment::find($request->id);
            $assignment->delete();

            $this->setResponse(false, "Assignment Deleted Successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Student list for specific assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignmentStudents(request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $assignment = Assignment::find($request->assignment_id);
            
            $students = User::role('student')->whereHas('details', function($q) use($assignment) {
                $q->where('preferred_boards', $assignment->syllabus_id, true)
                ->where('preferred_classes', $assignment->class_id, true)
                ->where('preferred_subjects', $assignment->subject_id, true);
            
            })->get();

            return UserpluckResource::collection($students)->additional([ "error" => false, "message" => 'Here is all students data of specific assignment']);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Publish Assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function publishAssignment(request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:assignments,_id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,_id',
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        // dd($request);
        try{
            $assignment = Assignment::find($request->id);
            $assignment->from_date = $request->from_date;
            $assignment->to_date = $request->to_date;
            $assignment->is_released = true;
            $assignment->save();
            
            //Get users email of assignment
            // $students = User::whereHas('details', function($q) use($assignment) {
            //     $q->where('preferred_boards', $assignment->syllabus_id, true)
            //     ->where('preferred_classes', $assignment->class_id, true)
            //     ->where('preferred_subjects', $assignment->subject_id, true);
            
            // })->pluck('_id')->toArray();

            foreach($request->user_ids as $student)
            {
                // Attach assignment with students
                $assignmentSection =  $assignment->studentAssignment()->create([
                    'student_id' => $student
                ]);
            }
            
            $today = date('Y-m-d');
            $from_date = date('Y-m-d',strtotime($request->from_date));

            if($today == $from_date)
            {
                /** Send notification to all the student **/
                dispatch(new AssignmentPublishNotification($assignment));
            }

            $this->setResponse(false, "Assignment published successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Submit Assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function submitAssignment(request $request)
    {

        $rules = [
            'assignment_id' => 'required|exists:assignments,_id',
            'section_type' => 'required|array',
            'section_type.*' => 'required|exists:question_types,tag',
            'assignment_section_ids' => 'required|array',
            'assignment_section_ids.*' => 'required|exists:assignment_sections,_id',
            'assignment_section_question_ids' => 'required|required',
            'assignment_section_question_ids.*' => 'required|required',
            'assignment_section_question_ids.*.*' => 'required|exists:section_questions,_id',
        ];

        // if (in_array('q_a', $request->input('section_type', []))) {
            // $rules['answers'] = 'required|array';
            // $rules['answers.*'] = 'required|array';
            // $rules['answers.*.*'] = 'required';
        // }
        
        // if (in_array('mcq', $request->input('section_type', []))) {
            // $rules['mcq_answers'] = 'required|array';
            // $rules['mcq_answers.*'] = 'required|array';
            // $rules['mcq_answers.*.*'] = 'required';
        // }

        // if (in_array('blanks', $request->input('section_type', []))) {
            // $rules['answers'] = 'required|array';
            // $rules['answers.*'] = 'required|array';
            // $rules['answers.*.*'] = 'required';
        // }

        if (in_array('comprehension', $request->input('section_type', []))) {
            $rules['comprehensive_question_ids'] = 'required|array';
            $rules['comprehensive_question_ids.*.*'] = 'required|array';
            $rules['comprehensive_question_ids.*.*.*'] = 'required|exists:comprehensive_questions,_id';
            $rules['comprehensive_answers'] = 'required|array';
            $rules['comprehensive_answers.*.*'] = 'required|array';
            $rules['comprehensive_answers.*.*.*'] = 'required';
        }

        if (in_array('match_following', $request->input('section_type', []))) {
            $rules['matchoptions_left'] = 'required|array';
            $rules['matchoptions_left.*.*'] = 'required|array';
            $rules['matchoptions_left.*.*.*'] = 'required|exists:question_options,_id';

            $rules['matching_key'] = 'required|array';
            $rules['matching_key.*.*'] = 'required|array';
            $rules['matching_key.*.*.*'] = 'required|exists:sections,tag';

            $rules['matchoptions_right'] = 'required|array';
            $rules['matchoptions_right.*.*'] = 'required|array';
            $rules['matchoptions_right.*.*.*'] = 'required|exists:question_options,_id';
        }

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        // dd($request->toArray());
        try{
            // Check if assignment already submitted

            $check = StudentAssignment::where(['student_id' => auth()->user()->id, 'assignment_id' => $request->assignment_id, 'student_status' => 'attempted'])->count();
            if($check)
            {
                $this->setResponse(false, "You have already submitted this assignment.");
                return response()->json($this->_response, 200);
            }
            
            $studentAssignment = StudentAssignment::where(['student_id' => auth()->user()->id, 'assignment_id' => $request->assignment_id])->first();
            
            if($studentAssignment)
            {
                foreach($request->assignment_section_ids as $key=>$value)
                {
					if($value != null && $value != 'null')
					{
						$studentAssignmentSection =  $studentAssignment->studentAssignmentSection()->create([
							'assignment_section_id' => $value,
							'obtained_mark' => ''
						]);
						
						foreach($request->assignment_section_question_ids[$key] as $second_key=>$question)
						{
							if($question != null && $question != 'null')
							{
								$studentAssignmentSectionQuestion =  $studentAssignmentSection->studentAssignmentSectionQuestion()->create([
									'assignment_section_question_id' => $question,
									'obtained_mark' => ''
								]);

								switch ($request->section_type[$key]) {
									case 'comprehension':
										foreach($request->comprehensive_question_ids[$key][$second_key] as $third_key=>$comprehensive_question)
										{
											if($comprehensive_question != null && $comprehensive_question != 'null')
											{
												$comprehensiveQuestion =  $studentAssignmentSectionQuestion->studentAssignmentComprehensiveQuestion()->create([
													'comprehensive_question_id' => $comprehensive_question,
													'obtained_mark' => ''
												]);

												$answer =  $comprehensiveQuestion->studentAssignmentComprehensiveAnswer()->create([
													'answer' => $request->comprehensive_answers[$key][$second_key][$third_key]
												]);
											}
										}
									break;
										
									case 'q_a':
										if($request->answers[$key][$second_key] != null && $request->answers[$key][$second_key] != 'null')
										{
											// Question answer store in database
											$answer = $studentAssignmentSectionQuestion->studentAssignmentSectionQuestionAnswer()->create([
												'given_answer' => $request->answers[$key][$second_key]
											]);
										}
									break;
										
									case 'mcq':
										if($request->mcq_answers[$key][$second_key] != null && $request->mcq_answers[$key][$second_key] != 'null')
										{
											// Question answer store in database
											$answer = $studentAssignmentSectionQuestion->studentAssignmentSectionQuestionAnswer()->create([
												'given_answer' => $request->mcq_answers[$key][$second_key]
											]);
										}
									break;

									case 'blanks':
										if($request->answers[$key][$second_key] != null && $request->answers[$key][$second_key] != 'null')
										{
											// Question answer store in database
											$answer = $studentAssignmentSectionQuestion->studentAssignmentSectionQuestionAnswer()->create([
												'given_answer' => $request->answers[$key][$second_key]
											]);
										}
									break;

									case 'match_following':
										// Store option match the following
										
										foreach($request->matchoptions_left[$key][$second_key] as $four_key=>$question_option)
										{
											$leftOption =  $studentAssignmentSectionQuestion->StudentAssignmentSectionQuestionOptions()->create([
												'question_option_id' => $question_option,
												'type' => 'left',
												'matching_key' => $request->matching_key[$key][$second_key][$four_key]
											]);
											
											$matching_key = $request->matching_key[$key][$second_key][$four_key];

											$rightOption =  $studentAssignmentSectionQuestion->StudentAssignmentSectionQuestionOptions()->create([
												'question_option_id' => $request->matchoptions_right[$key][$second_key][$matching_key],
												'type' => 'right',
												'key' =>  $matching_key
											]);

											$leftOption->matching()->associate($rightOption)->save();
											$rightOption->matching()->associate($leftOption)->save();
										}

									break;
								}
							}
						}
					}
                }

                $studentAssignment->student_status = 'attempted';
                $studentAssignment->attempted_date = Carbon::now();
                $studentAssignment->save();

                $this->setResponse(false, "Assignment submitted successfully.");
                return response()->json($this->_response, 200);
            }else{
                $this->setResponse(false, "You don't have assigned this assignment.");
                return response()->json($this->_response, 200);
            }
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Student list who attempted specific assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function studentSubmittedAssignment(request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $studentAssignmentData = StudentAssignment::where(['assignment_id' => $request->assignment_id,'student_status'=>'attempted'])->get();

            return StudentSubmittedAssignmentListResource::collection($studentAssignmentData)->additional([ "error" => false, "message" => 'Here is all student list who submitted specific assignment']);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Student list who not attempted specific assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignmentAttemptRemainStudent(request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $student_ids = StudentAssignment::where(['assignment_id' => $request->assignment_id, 'student_status' => 'pending'])->pluck('student_id')->toArray();
            $students = User::whereIn('_id',$student_ids)->get();
            
            return UserpluckResource::collection($students)->additional([ "error" => false, "message" => 'Here is all student list who not submitted specific assignment']);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Student list who not attempted specific assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewSubmittedAssignment(request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_assignment_id' => 'required|exists:student_assignments,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $submittedAssignmentData = StudentAssignment::find($request->student_assignment_id);
            
            return (new SubmittedAssignmentResource($submittedAssignmentData))->additional([ "error" => false, "message" => 'Here is student submitted assignment']);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Student list who not attempted specific assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteSubmittedAssignment(request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_assignment_id' => 'required|exists:student_assignments,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $submittedAssignment = StudentAssignment::find($request->student_assignment_id);
            
            $submittedAssignment->studentAssignmentSection()->delete();

            $this->setResponse(false, "Assignment deleted successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Student list who not attempted specific assignment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignmentMarking(request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_assignment_id' => 'required|exists:student_assignments,_id',
            // 'assignment_obtained_mark' => 'required|numeric',
            'assignment_section_id' => 'required|array',
            'assignment_section_id.*' => 'required|exists:student_assignment_sections,assignment_section_id',
            // 'assignment_section_mark' => 'required|array',
            // 'assignment_section_mark.*' => 'required|numeric',
            'section_question_id' => 'required|array',
            'section_question_id.*' => 'required|array',
            'section_question_id.*.*' => 'required|exists:student_assignment_section_questions,_id',
			'section_question_mark' => 'required|array',
            'section_question_mark.*' => 'required|array',
            'section_question_mark.*.*' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $submittedAssignment = StudentAssignment::find($request->student_assignment_id);
            
			$assignmentMark = 0;
			foreach($request->assignment_section_id as $key=>$value)
            {
				$sectionMarks = 0;
				foreach($request->section_question_id[$key] as $secondkey=>$secondvalue)
				{
					$questionMark = StudentAssignmentSectionQuestion::where('_id', $secondvalue)->update(['obtained_mark' => $request->section_question_mark[$key][$secondkey]]);
					$sectionMarks += $request->section_question_mark[$key][$secondkey];
				}
				
				$updateSectionMark = StudentAssignmentSection::where('assignment_section_id', $value)->update(['obtained_mark' => $sectionMarks]);
				$assignmentMark += $sectionMarks;
			}
			
			$submittedAssignment->obtained_mark = $assignmentMark;
            $submittedAssignment->tutor_status = 'checked';
			$submittedAssignment->save();
			
            $this->setResponse(false, "Assignment evaluted successfully.");
            return response()->json($this->_response, 200);
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
