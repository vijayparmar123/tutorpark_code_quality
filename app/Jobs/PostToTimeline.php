<?php

namespace App\Jobs;

class PostToTimeline extends Job
{
    public $modelObject;

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($modelObject, $data)
    {
        $this->modelObject = $modelObject;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->modelObject->linkTimeline()->create($this->data);
    }
}
