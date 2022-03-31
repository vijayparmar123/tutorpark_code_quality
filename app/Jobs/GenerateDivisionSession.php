<?php

namespace App\Jobs;

use App\Models\DivisionSchedule;
use App\Models\School;

class GenerateDivisionSession extends Job
{

     public $divisionId;
     public $teacherId;
     public $subjectId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($divisionId, $teacherId, $subjectId)
    {
        $this->divisionId = $divisionId;
        $this->teacherId = $teacherId;
        $this->subjectId = $subjectId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $schedules = DivisionSchedule::where(['class_division_id'=>$this->divisionId, 'teacher_id'=>$this->teacherId, 'subject_id'=>$this->subjectId])->get();
        
        $dateFromString = date("Y-m-d", strtotime("-1 day"));
        $dateToString = date("Y-m-d", strtotime("+1 month"));
        
        foreach ($schedules as $schedule) {
			/*########### School Working Years details collect ###########*/
            $schoolId = $schedule->division->class->createdBy->getSchoolId();
            $school = School::find($schoolId);
            $schoolWorkingStartDate = $school->working_start_date;
            $schoolWorkingEndDate = $school->working_end_date;
            /*########### School Working Years details collect ###########*/
			
            $dateStart = date("Y-m-d", strtotime("-1 day"));
			$dateEnd = date("Y-m-d", strtotime("+1 month"));
				
			$period = new \DatePeriod(new \DateTime($dateStart), new \DateInterval('P1D'), (new \DateTime($dateEnd)));
			$dates = iterator_to_array($period); 
			
            foreach ($dates as $val) {
				$date = $val->format('Y-m-d'); //format date
				$day_name = strtolower(date('l', strtotime($date))); //get week day
				if($day_name == strtolower($schedule->day))
				{
					if($schoolWorkingStartDate <= $date && $schoolWorkingEndDate >= $date)
					{
						$schedule->sessions()->create([
							'class_division_id' => $schedule->class_division_id,
							'date' => $date,
							'teacher_id' => $schedule->teacher_id,
							'subject_id' => $schedule->subject_id,
							'day' => $schedule->day,
							'start_time' => $schedule->start_time,
							'end_time' => $schedule->end_time,
							'meeting_id' => substr(md5(mt_rand()), 0, 32),
						]);
					}
				}
			}
        }
    }
}
