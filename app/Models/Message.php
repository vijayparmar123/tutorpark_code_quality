<?php

namespace App\Models;

use App\Traits\Timezone;
use Jenssegers\Mongodb\Eloquent\Model;

class Message extends Model
{
    use Timezone;

    protected $fillable = [
        'conversation_id',
        'body',
        'attachments',
        'created_by',
        'read_by',
        'deleted_by'
    ];

    protected static function booted()
    {
        static::creating(function($message){
            $message->created_by = auth()->id();
        }); 
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function clean()
    {
        $this->push('deleted_by', auth()->id());
    }
}
