<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetExperienceToInt extends Command
{
    private $_users;
    private $_count = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:SetExperienceToInt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert DB field (experience) to INT type';

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
        $this->_users->each(function ($user){
            $this->convertColumn($user->details->experience);
        });

        echo $this->_count ." Model(s) Updated.\n";
    }

    private function convertColumn($experiences)
    {
        if($experiences->isNotEmpty()) {
            $experiences->each(function($experience){
                $exp = $experience->experience_month;

                $experience->experience_month = null;
                $experience->save();

                $experience->experience_month = intval($exp);
                $experience->save();
                
                $this->_count++;
            });
        }
    }
}
