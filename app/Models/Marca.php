<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $fillable = ['nombre'];

    public function cervezas(){
        return $this->hasMany(Cerveza::class);
    }

}
