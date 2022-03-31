<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TodoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name ? $this->name : null,
            'user_name' => $this->user ? $this->user->full_name : null,
            'is_completed' => $this->is_completed,
            'mark_date' => $this->mark_date ? getDateTime($this->mark_date) : null,
        ];
    }
}
