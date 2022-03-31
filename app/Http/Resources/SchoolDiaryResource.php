<?php

namespace App\Http\Resources;

use App\Models\SchoolDiary;
use App\Models\ClassDivision;
use App\Models\SchoolDiarySubject;
use Illuminate\Database\Eloquent\Collection;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolDiaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $division = ClassDivision::find($this->division_id);
        $teacher_ids = $division->SubjectTeacher()->pluck('teacher_id')->toArray();
        $leader_ids = $division->subjectLeaders()->pluck('leader_id')->toArray();
        
        $user_ids = array_merge($teacher_ids, $leader_ids);
        $user_ids = array_unique($user_ids);

        $ids = SchoolDiary::where(['division_id'=>$this->division_id, 'date'=>$this->date])->whereIn('created_by', $user_ids)->pluck('_id'); 
        $details = SchoolDiarySubject::whereIn('school_diary_id', $ids)->get();

        $details = $details->groupBy('subject_id');

        return [
            'division_id' => $this->division_id,
            'division_name' => ($this->division)?$this->division->name:null,
            'class_name' => ($this->division)?$this->division->class->class_name:null,
            'date' => date('d-m-Y',strtotime($this->date)),
            'details' => ($details)?DiarySubjectDetailsResource::collection($details):null,
        ];
    }
}
