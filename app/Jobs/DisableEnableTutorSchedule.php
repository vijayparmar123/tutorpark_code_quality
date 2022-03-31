<?php

namespace App\Jobs;

use App\Models\DivisionSchedule;

class DisableEnableTutorSchedule extends Job
{
    public $divisionId;
    public $teacherId;
    public $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($divisionId, $teacherId, $status)
    {
        $this->divisionId = $divisionId;
        $this->teacherId = $teacherId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $schedules = DivisionSchedule::withoutGlobalScopes()->where(['class_division_id'=>$this->divisionId, 'teacher_id'=>$this->teacherId])->get();
        foreach($schedules as $schedule)
        {
            // Enable/Disable schedule
            DivisionSchedule::withoutGlobalScopes()->where(['_id'=>$schedule->_id])->update(['status' => $this->status]);

            // Enable/Disable all the session of above schedule
            $schedule->sessions()->withoutGlobalScopes()->where('date','>=',date('Y-m-d'))->update(['status' => $this->status]);

            // Disable all the session of above schedule
            // $schedule->sessions()->each(function($session) {
            //     $session->update(['status' => $this->status]); 
            // });
        }
    }
}
