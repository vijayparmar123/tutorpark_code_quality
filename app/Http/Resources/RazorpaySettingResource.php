<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RazorpaySettingResource extends JsonResource
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
            'mode' => ($this->mode)?$this->mode:null,
            'test_key_id' => ($this->test_key_id)?$this->test_key_id:null,
            'test_secret' => ($this->test_secret)?$this->test_secret:null,
            'live_key_id' => ($this->live_key_id)?$this->live_key_id:null,
            'live_secret' => ($this->live_secret)?$this->live_secret:null,
        ];
    }
}
