<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SetExperienceToInt::class,
        \App\Console\Commands\AddMyStudentColumn::class,
        \App\Console\Commands\generateGender::class,
        \App\Console\Commands\MettingNotification::class,
        \App\Console\Commands\TestNotification::class,
        \App\Console\Commands\MessageNotification::class,
        \App\Console\Commands\GenerateDivisionSession::class,
        \App\Console\Commands\AssignClassLeader::class,
        \App\Console\Commands\RemoveRejctedFriendRequest::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('test:notification')
                 // ->everyMinute();
		$schedule->command('custom:SetMyStudentField')
                 ->everyMinute();
		$schedule->command('custom:GenerateGender')
                 ->everyMinute();
		$schedule->command('meeting:notification')
                 ->everyThirtyMinutes();
		$schedule->command('custom:SetExperienceToInt')
                 ->everyMinute();
		$schedule->command('message:notification')
                 ->everyTenMinutes();
		$schedule->command('remove:rejectedfriendrequest')
                 ->daily();
                 
        //Run command every two weeks         		 
		$schedule->command('create:session')
        ->weekly()->mondays()
        ->when(function () {
            return date('W') % 2;
         })->at("13:38");	 
    }
}
