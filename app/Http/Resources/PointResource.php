<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointResource extends JsonResource
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
            'balance' => $this->balance,
            'tp_id' => $this->user->details->tp_id,
            'history' => ($this->history)?PointHistoryResource::collection($this->history):null,
        ];
    }
}
