<?php

namespace App\Http\Controllers;

use App\Http\Resources\TutorListResource;
use App\Http\Resources\myStudentResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;

class TutorController extends Controller
{
    public function findTutor()
    {
        // $validator = Validator::make($request->all(), [
        //     'syllabus_id' => 'exists:syllabuses,_id',
        //     'class_id' => 'exists:classes,_id',
        //     'subject_id' => 'exists:subjects,_id',
        //     // 'topic_id' => 'exists:topics,_id',
        //     'topic' => 'filled|max:100',
        //     'city' => 'filled|max:50',
        //     'mode' => 'filled|max:50',
        //     'type' => 'filled|max:50',
        //     'gender' => 'filled|max:10',
        //     'experience' => 'filled|numeric',
        // ]);
        
        // if ($validator->fails()) {
        //     $this->setResponse(true, $validator->errors()->all());
        //     return response()->json($this->_response, 400);
        // }

        try{
            // $mode = $request->mode;
            // $type = $request->type;
            // $topic = $request->topic;
            $tutors = User::role('tutor')->Orderby('created_at', 'desc')->get();
            // $tutors = $tutors->each->details
            //             ->whereSyllabusIn($request->syllabus_id)
            //             ->whereClassIn($request->class_id)
            //             ->whereSubjectIn($request->subject_id)
            //             // ->whereTopic($request->topic_id)
            //             ->when($mode, function($query, $mode){
            //                 return $query->where('mode', 'like', "%{$mode}%");
            //             })
            //             ->when($type, function($query, $type){
            //                 return $query->where('type', 'like', "%{$type}%");
            //             })
            //             ->when($topic, function($query, $topic){
            //                 return $query->where('topic', 'like', "%{$topic}%");
            //             })
            //         ->get();

            // if($request->has('gender')){
            //     $gender = $request->gender;
            //     $tutors = $tutors->filter(function($tutor) use($gender){
            //         return $tutor->details->gender === $gender;
            //     });
            // }

            // if($request->has('city')){
            //     $city = $request->city;
            //     $tutors = $tutors->filter(function($tutor) use($city){
            //         return $tutor->details->city === $city;
            //     });
            // }

            // if($request->has('experience')){
            //     $experience = $request->experience;
            //     $tutors = $tutors->filter(function($tutor) use($experience){
            //         return $tutor->totalExperience() >= $experience;
            //     });
            // }

            return (TutorListResource::collection($tutors))->additional(["error" => false, "message" => null]);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // tutor filter
    public function filterList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'max:50',
            'syllabus_id' => 'exists:syllabuses,_id',
            'class_id' => 'exists:classes,_id',
            'subject_id' => 'exists:subjects,_id',
            'mode' => 'max:50',
            'gender' => 'max:10',
            'experience' => 'numeric',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $city = $request->city;
            $syllabusID = $request->syllabus_id;
            $classID = $request->class_id;
            $subjectID = $request->subject_id;
            $mode = $request->mode;
            $gender = $request->gender;
            $experience = $request->experience;

            $tutors = User::role('tutor')->whereHas('details', function ($query) use ($city, $syllabusID, $classID, $subjectID, $mode, $gender) {
                $query->when($city, function($query, $city){
                    return $query->where(["city"=>$city]);
                })
                ->when($syllabusID, function($query, $syllabusID){
                    return $query->whereIn('preferred_boards', [$syllabusID]);
                })
                ->when($classID, function($query, $classID){
                    return $query->whereIn('preferred_classes', [$classID]);
                })
                ->when($subjectID, function($query, $subjectID){
                    return $query->whereIn('preferred_subjects', [$subjectID]);
                })
                ->when($mode, function($query, $mode){
                    return $query->where(["mode_of_classes"=>$mode]);
                })
                ->when($gender, function($query, $gender){
                    return $query->where(["gender"=>$gender]);
                });
            })->get();

            $tutors = $tutors->filter(function($tutor) use($experience){
                return $tutor->totalExperience() >= $experience;
            });
            
            return TutorListResource::collection($tutors)->additional([ "error" => false, "message" => null]);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }

    }
	
	// My student list
    public function myStudent()
    {
		try{
			$students = new collection();
			$myTuition = auth()->user()->my_tuitions;
			foreach($myTuition as $tuition)
			{
				$subscribedStudents = $tuition->students;
				foreach($subscribedStudents as $student)
				{
					if($student->user->hasRole('student'))
					{
						$user = $student->user;
						$students[] = $user;
					}
				}
			}
			
			$myCourse = auth()->user()->courses;
			foreach($myCourse as $course)
			{
				$courseSubscription = $course->subscriptions;
				// dd($courseSubscription);
				foreach($courseSubscription as $subscription)
				{
					if($subscription->subscribedUser)
					{
						if($subscription->subscribedUser->hasRole('student'))
						{
							$subscribedStudent = $subscription->subscribedUser;
							$students[] = $subscribedStudent;
						}
					}
				}
			}
			$students = $students->unique();

			return myStudentResource::collection($students)->additional([ "error" => false, "message" => 'My student list']);
		} catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
	}
}