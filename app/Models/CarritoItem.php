<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    protected $fillable = ['user_id', 'cerveza_id', 'cantidad'];

    public function cerveza() {
        return $this->belongsTo(Cerveza::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
