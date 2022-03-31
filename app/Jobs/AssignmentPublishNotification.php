<?php

namespace App\Jobs;
use App\Mail\AssignmentNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AssignmentPublishNotification extends Job
{
    public $assignment;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $assignmentData = [
            'title' => $this->assignment->title,
            'from_date' => date('Y-m-d', strtotime($this->assignment->from_date)),
            'to_date' => date('Y-m-d', strtotime($this->assignment->to_date)),
            'tutor' => $this->assignment->author->full_name,
        ];
        
        $syllabus_id = $this->assignment->syllabus_id;
        $class_id = $this->assignment->class_id;
        $subject_id = $this->assignment->subject_id;

        //Get users email of assignment
        $emails = User::whereHas('details', function($q) use($syllabus_id, $class_id, $subject_id){

            $q->where('preferred_boards', $syllabus_id, true)
                ->where('preferred_classes', $class_id, true)
                ->where('preferred_subjects', $subject_id, true);
        
        })->pluck('email')->toArray();

        foreach($emails as $recipient)
        {
            Mail::to($recipient)->queue(new AssignmentNotification($assignmentData));
        }

    }
}
