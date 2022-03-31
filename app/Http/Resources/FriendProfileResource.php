<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FriendProfileResource extends JsonResource
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
            // 'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'image' => ($this->resource instanceof User) ?  url('storage/images/user/'.strtolower($this->details->gender).".jpg") : url('storage/images/user/'.strtolower($this->details->gender).".jpg"),
            'role_id' => $this->role_id,
            'timezone' => $this->timezone,
            'allow_access' => $this->allow_access,
            'is_verified' => $this->is_verified,
            'user_details' => new UserDetailsResource($this->details),
        ];
    }
}
