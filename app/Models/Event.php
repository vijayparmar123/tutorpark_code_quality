<?php

namespace App\Models;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

use App\Scopes\MySchoolData;
use Carbon\Carbon;

class Event extends Eloquent
{
    protected $fillable = [
        'title','topic','description','mode','price','target_audience','from_date','to_date','from_time','to_time','image','meeting_id','create_meeting_url', 'library_id','created_by','updated_by'
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            $from_date = request()->from_date;
            $to_date = request()->to_date;
            $from_time = request()->from_time;
            $to_time = request()->to_time;
            $event->from_date = date('Y-m-d H:i:s', strtotime("$from_date $from_time"));
            $event->to_date = date('Y-m-d H:i:s', strtotime("$to_date $to_time"));
        });

        static::addGlobalScope(new MySchoolData);
    }

    // protected $dates = [
        // 'from_date', 'to_date'
    // ];

    public function scopeTime($query,$type)
    {
        switch ($type) {
            case "list":
                return $query->where('from_date','>=',date("Y-m-d H:i:s"))->Orderby('from_date', 'asc');
                break;
            case "upcoming":
                // $nextDate = Carbon::now()->addDays(2)->endOfDay();
				$nextDate = date('Y-m-d H:i:s', strtotime("+2 days"));
                return $query->where('from_date','>=',date("Y-m-d H:i:s"))->where('from_date','<=',$nextDate)->Orderby('from_date', 'asc');
                break;
            case "history":
                return $query->where('from_date','<',date("Y-m-d H:i:s"))->Orderby('from_date', 'desc');
                break;
            default:
                return $query;
        }

    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    public function speaker()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class,NULL,'event_ids','attendee_ids');
    }

    public function favouriteUsers()
    {
        return $this->belongsToMany(User::class,NULL,'favouriteEvents_id','favouriteUsers_id');
    }
	
	public function linkTimeline()
	{
		return $this->morphMany(Timeline::class, 'linkable');
	}
	
	public function library()
    {
        return $this->belongsTo(Library::class);
    }
}
