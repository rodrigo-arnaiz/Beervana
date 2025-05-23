<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = ['user_id', 'fecha', 'precio_total'];

    public function detalles() {
        return $this->hasMany(DetalleFactura::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
