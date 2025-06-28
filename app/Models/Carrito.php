<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $fillable = ['user_id', 'cerveza_id', 'cantidad'];

    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
