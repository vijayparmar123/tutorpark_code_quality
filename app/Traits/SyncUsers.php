<?php

namespace App\Traits;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Arr;

trait SyncUsers
{
    public $attachUsersEmail = [];
    public $detachUsers = [];
    public $attachUsers = [];
    public $relation = null;
    public $model = null;
    
    public function sync($keepUsersEmail, array $oldUsersEmail, $relation = 'members')
    {
        $this->relation = $relation;
        $this->model = class_basename($this);

        $keepUsersEmail = array_unique(array_map("trim", $keepUsersEmail));

        $this->attachUsersEmail = Arr::where($keepUsersEmail,function ($email) use ($oldUsersEmail){
            return in_array($email, $oldUsersEmail) ? false : true;
        });

        $this->detachUsers = $this->{$relation}->filter(function ($member) use ($keepUsersEmail){
            return !in_array($member->email, $keepUsersEmail) ? true : false;
        })->pluck('id');
        
        $this->attachUsers();

        $this->detachUsers();
    }

    private function attachUsers()
    {
        if(!empty($this->attachUsersEmail)){
            $this->attachUsers = User::whereIn('email', $this->attachUsersEmail)->pluck('_id')->toArray();
            $this->{$this->relation}()->attach($this->attachUsers);
            $this->save();
        }
    }

    private function detachUsers()
    {
        if(!empty($this->detachUsers)){
            foreach($this->detachUsers as $user){
                $this->{$this->relation}()->detach($user);

                //remove user from all tasks of this project
                if($this->model == 'Project' ){
                    $this->tasks->each(function($task) use ($user){
                        $task->assignedTo()->detach($user);
                    });
                }
            }
        }

        
    }
}
