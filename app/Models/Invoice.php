<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Invoice extends Eloquent
{
    protected $fillable = [
        'invoice_no', 'date', 'payment_method', 'payment_status', 'transaction_id', 'amount','purpose', 'created_by',
        'mode_of_tuition', 'course_tuition_name', 'session_taken'
    ];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->invoice_no = $invoice->generateUniqueNo();
            $invoice->created_by = auth()->user()->id;
        });
    }

    public function invoicable()
    {
        return $this->morphTo();
    }

    public function generateUniqueNo()
    {
        $data = $this->select(['invoice_no'])->orderBy('created_at', 'desc')->first();

        if (!empty($data->invoice_no)) {
            $prefix = 'INV-';

            $split = explode("-", $data->invoice_no);
            $find = sizeof($split) - 1;
            $last_id = $split[$find];
            $last_id = substr($last_id, 1);
            $number = intval($last_id) + 1;
            $new_no = sprintf('%06d', $number);
            $invoiceNo = $prefix . $new_no;
            return $invoiceNo;
        } else {
            return 'INV-000001';
        }
    }
}
