<?php

namespace App\Models;

use App\Traits\SyncUsers;
use App\Traits\Timezone;
use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;

class Conversation extends Model
{
    use Timezone,SyncUsers;

    protected $fillable = [
        'logo',
        'user_ids',
        'created_by',
        'project_id',
        'type',
        'deleted_by',
        'last_message_at',
    ];

    protected $dates = [
        'last_message_at'
    ];

    protected static function booted()
    {
        // Delete all message of specific conversation when conversation deleted
        static::deleting(function($conversation) { 
            $conversation->messages()->each(function($message) {
               $message->delete(); 
            });
       });
    }

    public function getLastMessageAtAttribute($value)
    {
        if($value){
            $date = Carbon::create($value->toDateTime());
            return $date->setTimezone(getUserTimezone());
        }
    }

    public function othersMessages()
    {
        return $this->messages()->whereNotIn('created_by',[auth()->id()]);
    }

    public function members()
    {
        return $this->belongsToMany(User::class,NULL,'conversation_ids','member_ids');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    
    public function owner()
    {
       return $this->belongsTo(User::class,'created_by');
    }
}
