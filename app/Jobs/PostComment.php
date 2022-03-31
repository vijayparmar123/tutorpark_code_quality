<?php

namespace App\Jobs;

class PostComment
{
    public $modelObject;

    public $comment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($modelObject, $comment)
    {
        $this->modelObject = $modelObject;
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->modelObject->comments()->create($this->comment);
    }
}
