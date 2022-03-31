<?php

namespace App\Jobs;

use App\Models\Timeline;

class DisableTimeline extends Job
{
    public $timeline;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Timeline $timeline)
    {
        $this->timeline = $timeline;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->timeline->abuse_count > 2)
        {
            $this->timeline->update(['status'=>0]);
        }
    }
}
