<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Pedido extends Model
{
    /** Creado y reservando stock; el cliente todavia no eligio como pagar. */
    public const PENDIENTE = 'pendiente';

    /** El cliente confirmo que va a pagar en el local y tiene su comprobante. */
    public const CONFIRMADO = 'confirmado';

    public const PAGADO = 'pagado';
    public const CANCELADO = 'cancelado';
    public const VENCIDO = 'vencido';

    public const ESTADOS = [
        self::PENDIENTE, self::CONFIRMADO, self::PAGADO, self::CANCELADO, self::VENCIDO,
    ];

    /**
     * Estados que reservan stock y se pueden cobrar.
     *
     * Los dos ocupan unidades; la diferencia es informativa para el mostrador:
     * un pedido confirmado significa que alguien avisó que viene a pagarlo.
     */
    public const ESTADOS_ACTIVOS = [self::PENDIENTE, self::CONFIRMADO];

    /** Cuánto vale la reserva de un pedido sin pagar. */
    public const HORAS_VENCIMIENTO = 72;

    public const ENTREGA_ENVIO = 'envio';
    public const ENTREGA_RETIRO = 'retiro';
    public const METODOS_ENTREGA = [self::ENTREGA_ENVIO, self::ENTREGA_RETIRO];

    /**
     * Costo del envío a domicilio.
     *
     * Única fuente de verdad: el frontend lo consulta por GET /my_api/costo-envio
     * en vez de tener su propia copia.
     */
    public const COSTO_ENVIO = 200.00;

    /**
     * Alfabeto del código de mostrador: sin 0/O ni 1/I/L, que se confunden al
     * dictarlos por teléfono o al tipearlos desde el papel.
     */
    private const ALFABETO_CODIGO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected $fillable = [
        'codigo', 'user_id', 'estado', 'metodo_entrega', 'envio', 'precio_total', 'expira_en',
    ];

    protected $casts = [
        'expira_en' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function factura()
    {
        return $this->hasOne(Factura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Código único que el cliente lleva impreso y el cajero usa para encontrar
     * el pedido. Reintenta ante una colisión, que igual es improbable:
     * 31^6 ≈ 887 millones de combinaciones.
     */
    public static function generarCodigo(): string
    {
        do {
            $sufijo = '';
            for ($i = 0; $i < 6; $i++) {
                $sufijo .= self::ALFABETO_CODIGO[random_int(0, strlen(self::ALFABETO_CODIGO) - 1)];
            }
            $codigo = 'BV-'.$sufijo;
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }

    /** Cuánto se cobra de envío según el método elegido. */
    public static function costoEnvioPara(string $metodoEntrega): float
    {
        return $metodoEntrega === self::ENTREGA_RETIRO ? 0.0 : self::COSTO_ENVIO;
    }

    /**
     * Pedidos que están reservando stock ahora mismo: pendientes y sin vencer.
     *
     * Un pedido pendiente cuya fecha ya pasó no entra, aunque siga marcado como
     * 'pendiente' en la base. Por eso la disponibilidad es correcta aunque nunca
     * corra el comando de limpieza.
     */
    public function scopeReservando(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->where('expira_en', '>', now());
    }

    /** Pendiente pero con la fecha vencida: ya no reserva, falta marcarlo. */
    public function scopeVencidos(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->where('expira_en', '<=', now());
    }

    public function estaVencido(): bool
    {
        return in_array($this->estado, self::ESTADOS_ACTIVOS, true)
            && $this->expira_en !== null
            && $this->expira_en->isPast();
    }

    /** Si todavía se puede pagar o cancelar. */
    public function estaVigente(): bool
    {
        return in_array($this->estado, self::ESTADOS_ACTIVOS, true) && ! $this->estaVencido();
    }

    /** Minutos que le quedan al cliente antes de perder la reserva. */
    public function minutosRestantes(): int
    {
        if (! $this->estaVigente()) {
            return 0;
        }

        return max(0, (int) now()->diffInMinutes($this->expira_en, false));
    }
}
