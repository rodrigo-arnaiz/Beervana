<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedido_items';

    protected $fillable = ['pedido_id', 'cerveza_id', 'cantidad', 'precio_unitario', 'subtotal'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function cerveza()
    {
        return $this->belongsTo(Cerveza::class);
    }
}
