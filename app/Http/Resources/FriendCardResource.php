<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendCardResource extends JsonResource
{
    private static $type;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => ( $this->_id) ? $this->_id : null,
            // 'image' => ($this->resource instanceof User) ?  url('storage/' . "images/user/{$this->details->gender}.jpg") : url('storage/' . "images/user/{$this->{self::$type}->details->gender}.jpg"),
            'image' => ($this->resource instanceof User) ?  url('storage/images/user/'.strtolower($this->details->gender).".jpg") : url('storage/images/user/'.strtolower($this->{self::$type}->details->gender).".jpg"),
            // 'profile' => ($this->details) ? url('storage/images/user/'.strtolower($this->details->gender).".jpg") : null,
            // "name" => ($this->resource instanceof User) ? $this->full_name : $this->{self::$type}->full_name,
            "name" => ($this->resource instanceof User) ? $this->full_name : ($this->type == 'sent' ? $this->sender->full_name : $this->receiver->full_name),
            "email" => ($this->resource instanceof User) ? $this->email : ($this->type == 'sent' ? $this->sender->email : $this->receiver->email),
            "role" => ($this->resource instanceof User) ? $this->getRole() : $this->{self::$type}->getRole(),
            "description" => "This Teacher is aiming to cover all the chapters of maths",
            "phone" => ($this->resource instanceof User) ? $this->details->phone : $this->{self::$type}->details->phone,
            "employment_status" => ($this->resource instanceof User) ? $this->details->employment_status : $this->{self::$type}->details->employment_status,
            "city" => ($this->resource instanceof User) ? $this->details->city : $this->{self::$type}->details->city,
            "type" => $this->type ?? null,
            "created_at" => $this->created_at,
            "request_status" => self::$type
        ];
    }

    public static function listCollection($resource, $type)
    {
        self::$type = $type;
        return parent::collection($resource);
    }
}
