<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cerveza extends Model
{

    private const SQL_RESERVADO = "(
        select coalesce(sum(pi.cantidad), 0)
        from pedido_items pi
        join pedidos p on p.id = pi.pedido_id
        where pi.cerveza_id = cervezas.id
          and p.estado in ('pendiente', 'confirmado')
          and p.expira_en > now()
    )";

    protected $fillable = ['nombre', 'precio', 'marca_id', 'graduacion', 'tipo_envase', 'estilo_id', 'ibu', 'capacidad', 'imagen', 'image_public_id', 'stock', 'descripcion'];

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function estilo()
    {
        return $this->belongsTo(Estilo::class);
    }

    /**
     * Agrega `reservado` y `disponible` a la consulta.
     *
     *   stock      -> unidades físicas; solo baja cuando un pedido se paga
     *   reservado  -> comprometidas por pedidos pendientes SIN vencer
     *   disponible -> lo que se puede pedir ahora = stock - reservado
     *
     * La condición de vencimiento va en la subconsulta a propósito: un pedido
     * pendiente que ya venció deja de descontar solo, sin que tenga que correr
     * ninguna tarea programada. En Vercel no hay dónde correr una.
     */
    public function scopeConDisponibilidad(Builder $query): Builder
    {
        return $query
            ->select('cervezas.*')
            ->selectRaw(self::SQL_RESERVADO.' as reservado')
            ->selectRaw('cervezas.stock - '.self::SQL_RESERVADO.' as disponible');
    }

    /** Solo las que tienen al menos una unidad libre para pedir. */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->conDisponibilidad()
            ->whereRaw('cervezas.stock > '.self::SQL_RESERVADO);
    }

    /** Unidades comprometidas por pedidos pendientes vigentes. */
    public function reservado(): int
    {
        return (int) PedidoItem::query()
            ->join('pedidos', 'pedidos.id', '=', 'pedido_items.pedido_id')
            ->where('pedido_items.cerveza_id', $this->id)
            // Los confirmados también reservan: el cliente avisó que viene a
            // pagarlos, pero las unidades ya estaban apartadas desde antes.
            ->whereIn('pedidos.estado', Pedido::ESTADOS_ACTIVOS)
            ->where('pedidos.expira_en', '>', now())
            ->sum('pedido_items.cantidad');
    }

    /** Unidades que se pueden pedir ahora mismo. */
    public function disponible(): int
    {
        return (int) $this->stock - $this->reservado();
    }
}
