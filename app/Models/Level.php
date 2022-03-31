<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Level extends Eloquent
{
    protected $fillable = [
        'name','description', 'active'
    ];

    public function class(){
        return $this->hasMany(TpClass::class);
      }
}
