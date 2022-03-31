<?php

namespace App\Jobs;

use App\Models\Transaction;

class AddEarningTransactionJob extends Job
{
    public $modelObject;

    public $transaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($modelObject, $transaction)
    {
        $transaction['tp_commission'] = getTpCommission().' %';
        $transaction['final_amount'] = $transaction['amount'] - ($transaction['amount'] * getTpCommission()/100);
        $this->modelObject = $modelObject;
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->modelObject->transactions()->create($this->transaction);
    }
}
