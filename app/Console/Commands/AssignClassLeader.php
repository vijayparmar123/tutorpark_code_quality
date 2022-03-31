<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClassDivision;
use App\Models\DivisionStudent;
use App\Models\User;

class AssignClassLeader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:leader';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To assign class leader by highest Tp Points';

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
        $divisions = ClassDivision::get();
        
        foreach($divisions as $division)
        {
            $userPoints = array();
            $leader_id = 0;
            if($division->students()->count())
            {
                foreach($division->students as $student)
                {
                    $user = User::find($student->student_id);
                    
                    if($user)
                    {
                        $userPoints[$student->student_id] = $user->balance();
                    }
                }
                $leader_id = array_search(max($userPoints), $userPoints);
            }
            
            // Add Leader
            if($leader_id)
            {
                // Remove previous leader
                DivisionStudent::where(['class_division_id'=>$division->_id,'is_leader'=>true])->update(['is_leader'=>false]);

                //Assign new students
                DivisionStudent::where(['class_division_id'=>$division->_id, 'student_id'=>$leader_id])->update(['is_leader'=>true]);
            }
        }
    }
}
