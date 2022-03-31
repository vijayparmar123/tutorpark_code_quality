<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $permissions = null;
        
        if(isset($this->permission_ids)){
            $permissions = !empty($this->permission_ids) ? PermissionResource::collection($this->whenLoaded('permissions')) : null;
        }

        return [
            'id' => $this->_id,
            'name' => $this->name,
            'permissions' => $permissions,
            // 'permissions' => PermissionResource::collection($this->permission_ids),
            // 'permissions' => $this->permission_ids,
        ];
    }
}
