<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Mail\Notification;
use Illuminate\Support\Facades\Mail;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send testing mail to check cronjob is working or not.';

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
        $mailData = [
                'description' => 'This is test mail from tutorpark',
                'email_subject' => 'Test Mail - TutorPark',
                'email_template' => 'Test',
            ];
            
        Mail::to('vijay.parmar@dasinfomedia.com')->queue(new Notification($mailData));
    }
}
