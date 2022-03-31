<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\Assignment;

class TutorAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $draftAssignments = Assignment::where([['created_by','=', auth()->user()->id], ['is_released','=',false],['is_expired','=',false]])->Orderby('created_at', 'desc')->get();
        $publishedAssignments = Assignment::where([['created_by','=', auth()->user()->id], ['is_released','=',true],['is_expired','=',false]])->Orderby('from_date', 'asc')->get();
        // $expiredAssignments = Assignment::where('is_expired','=',true)->get();
        
        return [
            'draft_assignments' => $draftAssignments->isNotEmpty() ? AssignmentResource::collection($draftAssignments) : null,
            'published_assignments' => $publishedAssignments->isNotEmpty() ? AssignmentResource::collection($publishedAssignments) : null,
            // 'assignment_history' => $expiredAssignments->isNotEmpty() ? AssignmentResource::collection($expiredAssignments) : null,
        ];
    }
}
