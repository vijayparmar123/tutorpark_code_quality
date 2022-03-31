<?php

namespace App\Jobs;

use App\Models\UserDetails;

class UpdateTutorStatus extends Job
{
	public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tutor = UserDetails::where(['user_id' => $this->data['tutor_id']])->first();
		
		switch ($this->data['type']) {
		  case "student_added":
			$old_student_count = ($tutor->student_added_count)?$tutor->student_added_count:0;
			if($old_student_count < 10)
			{
				$new_count =  $old_student_count + 1;
				$tutor->student_added_count = $new_count;
				if($tutor->student_added_count > 9)
				{
					$tutor->tutor_verified_status = ($tutor->tutor_verified_status)?$tutor->tutor_verified_status + 25:25;
				}
				$tutor->save();
			}
		  break;
		  case "tutor_added":
			$old_tutor_count = ($tutor->tutor_added_count)?$tutor->tutor_added_count:0;
			if($old_tutor_count < 3)
			{
				$new_count =  $old_tutor_count + 1;
				$tutor->tutor_added_count = $new_count;
				if($tutor->tutor_added_count > 2)
				{
					$tutor->tutor_verified_status = ($tutor->tutor_verified_status)?$tutor->tutor_verified_status + 25:25;
				}
				$tutor->save();
			}
		  break;
		  case "course_completed":
			$old_course_count = ($tutor->course_completed_count)?$tutor->course_completed_count:0;
			if($old_course_count < 3)
			{
				$new_count =  $old_course_count + 1;
				$tutor->course_completed_count = $new_count;
				if($tutor->course_completed_count > 2)
				{
					$tutor->tutor_verified_status = ($tutor->tutor_verified_status)?$tutor->tutor_verified_status + 25:25;
				}
				$tutor->save();
			}
		  break;
		  case "answer_given":
			$old_answer_count = ($tutor->given_answer_count)?$tutor->given_answer_count:0;
			if($old_answer_count < 25)
			{
				$new_count =  $old_answer_count + 1;
				$tutor->given_answer_count = $new_count;
				if($tutor->given_answer_count > 24)
				{
					$tutor->tutor_verified_status = ($tutor->tutor_verified_status)?$tutor->tutor_verified_status + 25:25;
				}
				$tutor->save();
			}
		  break;
		}
    }
}
