<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $urlPrefix = function ($image) {
            return $image ? urldecode(url('storage', $image)) : null;
        };

        return [
            'id' => $this->id,
            'student_point' => $this->student_point,
            'tutor_point' => $this->tutor_point,
        ];
    }
}
