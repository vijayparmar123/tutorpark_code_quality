<?php 

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\User;

class MySchoolData implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if(auth()->user())
        {
            if(auth()->user()->hasSchool())
            {
                $schoolId = auth()->user()->getSchoolID();
                $schoolUserIds = User::where(['school_id'=>$schoolId])->pluck('_id')->toArray();
                $builder->whereIn('created_by',$schoolUserIds);
            }else{
                $tutorParkUserIds = User::where('school_id', '=', '')->orWhereNull('school_id')->pluck('_id')->toArray();
                $builder->whereIn('created_by',$tutorParkUserIds);
            }
        }
    }
}