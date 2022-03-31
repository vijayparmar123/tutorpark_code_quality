<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\DivisionStudent;
use App\Models\DivisionSubjectTeacher;

class AssignSchoolFriends extends Job
{
	public $user;
	public $userId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user = User::find($this->userId);
        $schoolId = $this->user->getSchoolID();
        $userIds = array();
		switch ($this->user->getRole()) {
            case 'school-student':
                $divisionIds = $this->user->getDivisionIds();
                if(count($divisionIds))
                {
                    $students = DivisionStudent::whereIn('class_division_id', $divisionIds)->pluck('student_id')->toArray();
                    $teachers = DivisionSubjectTeacher::whereIn('class_division_id', $divisionIds)->pluck('teacher_id')->toArray();    
                    
                    $userIds = array_merge($userIds, $students);
                    $userIds = array_merge($userIds, $teachers);
                }
                $schoolAdmins = User::role('school-admin')->where(['school_id' => $schoolId])->pluck('_id')->toArray();
                $userIds = array_merge($userIds, $schoolAdmins);
            break;
            case 'school-tutor':
                $divisionIds = $this->user->getDivisionIds();
                if(count($divisionIds))
                {
                    $students = DivisionStudent::whereIn('class_division_id', $divisionIds)->pluck('student_id')->toArray();
                    
                    $userIds = array_merge($userIds, $students);
                }
                $teachers = User::role('school-tutor')->where(['school_id' => $schoolId])->pluck('_id')->toArray();
                $schoolAdmins = User::role('school-admin')->where(['school_id' => $schoolId])->pluck('_id')->toArray();
                
                $userIds = array_merge($userIds, $teachers);
                $userIds = array_merge($userIds, $schoolAdmins);
            break;
            case 'school-admin':
                $teachers = User::role('school-tutor')->where(['school_id' => $schoolId])->pluck('_id')->toArray();
                $schoolAdmins = User::role('school-admin')->where(['school_id' => $schoolId])->pluck('_id')->toArray();
                $userIds = array_merge($userIds, $teachers);
                $userIds = array_merge($userIds, $schoolAdmins);
            break;
        }

        // Search
        $pos = array_search($this->user->_id, $userIds);
        
        // Remove current user from array
        if($pos)
        {
            unset($userIds[$pos]);
        }

        //Remove users ids who is already added in friends
        $alreadyFriendIds = $this->user->friends()->pluck('_id')->toArray();
        if(count($alreadyFriendIds))
        {  
            $userIds = array_diff($userIds, $alreadyFriendIds);
        }

        $users = User::whereIn('_id', $userIds)->get();
        foreach($users as $user)
        {
            //Add user to current user friend
            $this->user->addAsFriend($user);

            // Add current user to another user friend
            $user->addAsFriend($this->user);
        }
    }
}
