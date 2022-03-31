<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddMyStudentColumn extends Command
{
    private $_users;
    private $_count = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:SetMyStudentField';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add my_student_ids as ARRAY to DB if not.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->_users = \App\Models\User::all();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->_users->each(function ($user) {
            $this->setField($user->details);
        });
        echo $this->_count ." User Updated.\n";
    }

    private function setField($details)
    {
        $details->each(function($detail){
            if(!is_array($detail->my_students_ids)){
                $detail->my_students_ids = [];
                $detail->save();
                $this->_count++;
            }
        });
    }
}
