<?php

namespace App\Jobs;

class PostPayment extends Job
{
    public $modelObject;

    public $payment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($modelObject, $payment)
    {
        $this->modelObject = $modelObject;
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->modelObject->payment()->create($this->payment);
    }
}
