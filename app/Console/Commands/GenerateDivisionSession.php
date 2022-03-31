<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DivisionSchedule;
use App\Models\School;

class GenerateDivisionSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate school class division session as per schedule.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $schedules = DivisionSchedule::get();
        
        // $dateFromString = date("Y-m-d", strtotime("-1 day"));
        // $dateToString = date("Y-m-d", strtotime("+2 day"));
        
        foreach ($schedules as $schedule) {

            /*########### School Working Years details collect ###########*/
            $schoolId = $schedule->division->class->createdBy->getSchoolId();
            $school = School::find($schoolId);
            $schoolWorkingStartDate = $school->working_start_date;
            $schoolWorkingEndDate = $school->working_end_date;
            /*########### School Working Years details collect ###########*/

            /*########### Collect last session date and add 2 new session ###########*/
            $dateStart = ($schedule->sessions)?$schedule->sessions->last()->date:date("Y-m-d", strtotime("-1 day"));
            $dateStart = date('Y-m-d', strtotime($dateStart. ' + 1 days'));
            $dateEnd = date('Y-m-d', strtotime($dateStart. ' + 14 days'));
            /*########### Collect last session date and add 2 new session ###########*/

        
            $period = new \DatePeriod(new \DateTime($dateStart), new \DateInterval('P1D'), (new \DateTime($dateEnd)));
			$dates = iterator_to_array($period); 
			
            foreach ($dates as $val) {
				$date = $val->format('Y-m-d'); //format date
				$day_name = strtolower(date('l', strtotime($date))); //get week day
				if($day_name == $schedule->day)
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
