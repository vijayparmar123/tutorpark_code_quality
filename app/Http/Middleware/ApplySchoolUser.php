<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Database\Eloquent\Builder;

class ApplySchoolUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if(auth()->check()) {
            if(auth()->user()->hasSchool())
            {
                User::addGlobalScope('myschoolusers', function (Builder $builder) {
                    $schoolId = auth()->user()->getSchoolID();
                    $builder->where('school_id',$schoolId);
                });
            }else{
                User::addGlobalScope('myschoolusers', function (Builder $builder) {
                    $builder->where('school_id', '=', '')->orWhereNull('school_id');
                });
            }            
        }
        return $next($request);
    }
}
