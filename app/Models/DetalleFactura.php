<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    protected $table = 'detalle_factura';

    protected $fillable = ['factura_id', 'cerveza_id', 'cantidad', 'precio_unitario'];

    public function cerveza() {
        return $this->belongsTo(Cerveza::class);
    }

    public function factura() {
        return $this->belongsTo(Factura::class);
    }
}
