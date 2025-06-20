<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleFactura extends Model
{
    use HasFactory;
    protected $table = 'detalle_factura';

    protected $fillable = ['factura_id', 'cerveza_id', 'cantidad', 'precio_unitario', 'subtotal'];

    public function cerveza() {
        return $this->belongsTo(Cerveza::class);
    }

    public function factura() {
        return $this->belongsTo(Factura::class);
    }
}
