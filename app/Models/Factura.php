<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Comprobante de una venta consumada. Existe solo si el pedido se pagó: no hay
 * facturas "impagas". El estado de la compra vive en Pedido.
 *
 * Los renglones se leen del pedido (pedido->items) en vez de duplicarse: el
 * pedido es inmutable una vez creado, así que no hay dos copias que se puedan
 * desincronizar.
 */
class Factura extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_id', 'user_id', 'cobrado_por', 'medio_pago', 'fecha', 'precio_total'];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Quien registró el cobro. Null en las facturas anteriores al cambio. */
    public function cobrador()
    {
        return $this->belongsTo(User::class, 'cobrado_por');
    }
}
