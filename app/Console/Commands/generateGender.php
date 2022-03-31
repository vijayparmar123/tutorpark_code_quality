<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class generateGender extends Command
{
    private $_users;
    private $_count = 0;
    private $_gender = [
        "male",
        "female"
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:GenerateGender';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create gender randomly is not set already.';

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
            if($user->details->gender == null)
            {
                $user->details->gender = Arr::random($this->_gender);
                $user->details->save();
                $this->_count++;
            }
        });

        echo $this->_count ." ". Str::plural('User', $this->_count) ." Updated.\n";
    }
}
