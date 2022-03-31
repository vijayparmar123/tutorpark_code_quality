<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use DateTime;
use App\Mail\Notification;
use Illuminate\Support\Facades\Mail;

class MessageNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify about new message received at tutorpark';

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
        $time = new DateTime('-10 minutes');
		
		// $messages = Conversation::where(['module'=>'message'])->with(['messages' => function ($query) use($time) {
			// $query->whereDate('created_at', '>', $time);
		// }])->get();
		
		$messages = Message::where('created_at', '>', $time)
		->with(['conversation' => function ($query) {
			$query->where(['module'=>'message']);
		}])->get();
		
		foreach($messages as $message)
		{
			$member_ids = $message->conversation->member_ids;
			$sender = $message->created_by;
			
			
			// Remove sender from members who receive notification
			if (($key = array_search($sender, $member_ids)) !== false) {
				unset($member_ids[$key]);
			}
			
			// Remove read by from members who receive notification
			foreach($message->read_by as $readBy)
			{
				if (($key = array_search($readBy, $member_ids)) !== false) {
					unset($member_ids[$key]);
				}
			}
			
			$senderUser = User::find($sender);
			foreach($member_ids as $user)
			{
				$receiver_email = User::find($user)->email;
				$mailData = [
					'sender_name' => $senderUser->full_name,
					'sender_email' => $senderUser->email,
					'message' => $message->body,
					'email_subject' => 'You have new message at TutorPark',
					'email_template' => 'MessageNotification',
				];
				
				Mail::to($receiver_email)->queue(new Notification($mailData));
				
			}
		}
    }
}
