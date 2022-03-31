<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RejectedFriendRequest;

class RemoveRejctedFriendRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:rejectedfriendrequest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $today = date("Y-m-d");
        $removeDate = date('Y-m-d', strtotime($today. ' - 6 month'));
        $request = RejectedFriendRequest::where('rejected_date', '<=', $removeDate.' 23:59:59')->delete();
        
    }
}
