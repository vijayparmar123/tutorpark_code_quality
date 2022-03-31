<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use DateTime;
use Carbon\Carbon;
use App\Mail\EventNotification;
use Illuminate\Support\Facades\Mail;

class MettingNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify every attendee of specific BigBlueButton meeting about 10 minutes ago by mail.';

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
        // $time = Carbon::now()->addMinutes(30);
		$now = date("Y-m-d H:i:s");
		$time = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        $events = Event::where('from_date', '>', $now)->where('from_date', '<', $time)->get();
        		
        foreach ($events as $event) {
            $emails = $event->attendees()->pluck('email')->toArray();
            $author_email = $event->speaker()->pluck('email')->toArray();
            $emails = array_merge($emails,$author_email);
            $emails = array_unique($emails);
                        
            $mailData = [
                'event_name' => $event->title,
                'schedule_time' => date("d-m-Y g:i a",strtotime($event->from_date)),
                'host_email' => $event->speaker->email,
                'host_name' => $event->speaker->full_name,
                'host_mobile' => $event->speaker->details->phone
            ];
            
            foreach($emails as $recipient)
            {
                Mail::to($recipient)->queue(new EventNotification($mailData));
            }   
        }
    }
}
