<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\Assignment;

class StudentAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $pendingAssignments = Assignment::whereHas('studentAssignment', function($q) {
            $q->where(['student_id' => auth()->user()->id, 'student_status' => 'pending']);
        })->where(['is_released' => true])->Orderby('created_at', 'desc')->get();

        $attemptedAssignments = Assignment::whereHas('studentAssignment', function($q) {
            $q->where(['student_id' => auth()->user()->id, 'student_status' => 'attempted']);
        })->where(['is_released' => true])->Orderby('created_at', 'desc')->get();
        
        return [
            'pending_assignments' => $pendingAssignments->isNotEmpty() ? AssignmentResource::collection($pendingAssignments) : null,
            'attempted_assignments' => $attemptedAssignments->isNotEmpty() ? AssignmentResource::collection($attemptedAssignments) : null,
        ];
    }
}
