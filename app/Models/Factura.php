<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Factura extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'fecha', 'precio_total'];

    public function detalles() {
        return $this->hasMany(DetalleFactura::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
