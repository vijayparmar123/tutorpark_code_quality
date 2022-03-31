<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class SharedLibraryResource extends JsonResource
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
            'shared_by'=> ($this->shareBy)?new UserpluckResource($this->shareBy):null,
            'shared_to'=> ($this->shareTo)?new UserpluckResource($this->shareTo):null,
            'library'=> ($this->library)?new LibraryResource($this->library):null,
        ];
    }
}
