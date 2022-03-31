<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Models\ClassDivision;

class DivisionAttendanceDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $division = ClassDivision::find($request->division_id);
        $attendance = $division->attendance()->where(['date' => $request->date])->groupBy('student_id','date','status')->get();
        $total_present = $division->attendance()->where(['date' => $request->date,'status'=>'present'])->groupBy('student_id','date','status')->get();
        $total_absent = $division->attendance()->where(['date' => $request->date,'status'=>'absent'])->groupBy('student_id','date','status')->get();
        
        return [
            'total_student' => ($division->students)?$division->students()->count():0,
            'total_present' => ($total_present)?count($total_present):0,
            'total_absent' => ($total_absent)?count($total_absent):0,
            'attendance' => ($attendance->count())?DivisionAttendanceResource::collection($attendance):null,
        ];
    }
}
