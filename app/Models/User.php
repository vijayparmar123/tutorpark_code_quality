<?php

namespace App\Models;

use App\Traits\ImpersonateUser;
use App\Traits\ManageFriends;
use App\Traits\ManagePoints;
use App\Traits\ManageSchool;
use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Maklad\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notification;

class User extends Eloquent implements AuthenticatableContract, AuthorizableContract, JWTSubject, CanResetPasswordContract
{
    use Notifiable, Authenticatable, Authorizable, HasFactory, HasRoles, ManageFriends, ImpersonateUser, SoftDeletes, ManagePoints, CanResetPassword, ManageSchool;

    // protected $guard_name = 'api';

    protected $connection = 'mongodb';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'role_id', 'tp_id','timezone', "verify_token", "verified_at", 'allow_access', 'is_verified','school_id','linked_email'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $dates = [
        'verified_at',
        'deleted_at'
    ];

    protected $appends = ['full_name'];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if($user->password != null){
                $user->password = Hash::make($user->password);
            }
            $user->is_verified = 0;
            $user->verify_token = Str::random(40);
        });
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getRole()
    {
        $roles = $this->getRoleNames();
        return $roles->isNotEmpty() ? $roles[0] : null;
    }
    
    public function totalExperience( $in = 'years' )
    {
        $months = $this->details->experience->sum('experience_month');

        return $in == 'years' ? round($months / 12,1) : $months;
    }

    public function courses()
    {
        return $this->hasMany(Course::class,'created_by');
    }

    public function subscribeCourse()
    {
        return $this->hasMany(CourseSubscription::class);
    }

    public function details()
    {
        return $this->hasOne(UserDetails::class);
    }

    public function availability()
    {
        return $this->hasMany(TutorTimeTable::class);
    }

    public function blockUser()
    {
        return $this->hasMany(BlockUser::class);
    }

    public function isBlockedMe()
    {
        return $this->hasMany(BlockUser::class,'blocked_user');
    }

    public function session()
    {
        return $this->hasMany(Sessions::class,'tutor_id');
    }

    public function earnings()
    {
        return $this->hasMany(Transaction::class,'paid_to');
    }
    
    public function expenses()
    {
        return $this->hasMany(Transaction::class,'paid_from');
    }
    
    public function my_tuitions()
    {
        return $this->hasMany(Tuition::class,'tutor_id');
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class,NULL,'attendee_ids','event_ids');
    }

    public function importantEvents()
    {
        return $this->belongsToMany(Event::class,NULL,'favouriteUsers_id','favouriteEvents_id');
    }

    public function timelines()
    {
        return $this->belongsToMany(Timeline::class,NULL,'likedby_ids','likedtimeline_ids');
    }

    public function favouritetimelines()
    {
        return $this->belongsToMany(Timeline::class,NULL,'favouriteby_ids','favouritetimeline_ids');
    }

    public function disliketimelines()
    {
        return $this->belongsToMany(Timeline::class,NULL,'dislikedby_ids','dislikedtimeline_ids');
    }

    public function abusetimelines()
    {
        return $this->belongsToMany(Timeline::class,NULL,'abuseby_ids','abusetimeline_ids');
    }

    public function points()
    {
        return $this->hasOne(Point::class,'user_id');
    }

    public function childs()
    {
        return $this->belongsToMany(User::class,null,'parent_ids','child_ids',);
    }

    public function parents()
    {
        return $this->belongsToMany(User::class,null,'child_ids','parent_ids');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolSchedule()
    {
        return $this->hasMany(DivisionSchedule::class,'teacher_id');
    }

    public function invoice()
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }
	
	public function sendPasswordResetNotification($token){
		
		$mailData = [
            'token' => $token,
            'email' => $this->email,
            'host' => getHost(),
			'email_template' => 'ResetPassword',
			'email_subject' => 'Reset Password Request' 
        ];
		
		Mail::to($this->email)->send(new Notification($mailData));
	}
}
