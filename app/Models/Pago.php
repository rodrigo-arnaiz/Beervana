<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un intento de cobro por Mercado Pago.
 *
 * "Intento" y no "pago": la fila nace cuando se crea la preferencia, mucho
 * antes de que alguien ponga una tarjeta, y puede terminar en nada. El pedido
 * sigue siendo el que manda sobre si la venta está cerrada o no.
 */
class Pago extends Model
{
    /** Se creó la preferencia; nadie pagó todavía. */
    public const PENDIENTE = 'pendiente';

    /** MP confirmó el pago. Es el único estado que cierra la venta. */
    public const APROBADO = 'aprobado';

    /** MP lo tiene en revisión (suele pasar con transferencias). */
    public const EN_PROCESO = 'en_proceso';

    public const RECHAZADO = 'rechazado';

    /** El cliente abandonó el checkout o se canceló. */
    public const CANCELADO = 'cancelado';

    public const ORIGEN_TIENDA = 'tienda';
    public const ORIGEN_MOSTRADOR = 'mostrador';

    protected $fillable = [
        'pedido_id',
        'iniciado_por',
        'origen',
        'preference_id',
        'payment_id',
        'estado',
        'detalle_estado',
        'monto',
        'init_point',
        'qr_data',
        'in_store_order_id',
        'pagado_en',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado_en' => 'datetime',
    ];

    /**
     * Traducción de los estados de Mercado Pago a los nuestros.
     *
     * MP maneja más estados de los que a este negocio le importan. Se traduce
     * en un solo lugar para que el resto del código no tenga que aprenderse el
     * vocabulario de MP ni adivinar si 'authorized' cuenta como pagado.
     *
     * @see https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/additional-content/your-integrations/notifications/webhooks
     */
    public static function estadoDesdeMercadoPago(?string $estadoMp): string
    {
        return match ($estadoMp) {
            'approved' => self::APROBADO,
            'in_process', 'pending', 'authorized' => self::EN_PROCESO,
            'rejected' => self::RECHAZADO,
            'cancelled', 'refunded', 'charged_back' => self::CANCELADO,
            default => self::PENDIENTE,
        };
    }

    public function estaAprobado(): bool
    {
        return $this->estado === self::APROBADO;
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /** El cajero que generó el cobro, si salió del mostrador. */
    public function iniciador()
    {
        return $this->belongsTo(User::class, 'iniciado_por');
    }
}
