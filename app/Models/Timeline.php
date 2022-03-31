<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Scopes\MySchoolData;

class Timeline extends Eloquent
{
    protected $fillable = [
        'audiance','description','image','video','created_by','datetime','status','like_count','dislike_count','abuse_count','liked_by','dislike_by','abuse_by','is_reposted','reposted_id'
    ];

    protected $dates = [
        'datetime'
    ];

    protected static function booted()
    {
        static::creating(function ($timeline) {
            $timeline->status = 1;
            $timeline->like_count = 0;
            $timeline->dislike_count = 0;
            $timeline->abuse_count = 0;
            $timeline->favouriteBy = 0;
            $timeline->datetime = date('Y-m-d H:i:s');
        });

        static::addGlobalScope(new MySchoolData);

        static::addGlobalScope('abused', function (Builder $builder) {
            $builder->where('status', '!=', 0);
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function likeBy()
    {
        return $this->belongsToMany(User::class,NULL,'likedtimeline_ids','likedby_ids');
    }

    public function favouriteBy()
    {
        return $this->belongsToMany(User::class,NULL,'favouritetimeline_ids','favouriteby_ids');
    }

    public function dislikeBy()
    {
        return $this->belongsToMany(User::class,NULL,'dislikedtimeline_ids','dislikedby_ids');
    }

    public function abuseBy()
    {
        return $this->belongsToMany(User::class,NULL,'abusetimeline_ids','abuseby_ids');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
	
	public function repostedTimeline()
    {
        return $this->belongsTo(Timeline::class, 'reposted_id');
    }
	
	public function linkable()
    {
        return $this->morphTo();
    }
}
