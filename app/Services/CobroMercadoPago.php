<?php

namespace App\Services;

use App\Exceptions\CobroImposible;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * El cobro por Mercado Pago, de punta a punta.
 *
 * Junta las dos mitades: hablar con MP (MercadoPago) y cerrar la venta
 * (RegistrarCobro). Los controladores no saben de preferencias ni de estados de
 * MP; piden "cobrá esto" o "fijate cómo viene" y reciben un Pago nuestro.
 */
class CobroMercadoPago
{
    public function __construct(
        private MercadoPago $mp,
        private RegistrarCobro $cobro,
    ) {
    }

    /**
     * Arranca un cobro: crea la preferencia en MP y guarda el intento.
     *
     * @throws CobroImposible si el pedido no está en condiciones de cobrarse
     */
    public function iniciar(Pedido $pedido, ?User $iniciador, string $origen): Pago
    {
        // Se valida antes de ir a MP: crear una preferencia para un pedido que
        // ya venció deja al cliente pagando algo que después no se le puede
        // entregar, y devolver esa plata es un problema fuera del sistema.
        if ($pedido->estado === Pedido::PAGADO) {
            throw CobroImposible::yaPagado();
        }

        if ($pedido->estaVencido()) {
            throw CobroImposible::vencido();
        }

        if (! in_array($pedido->estado, Pedido::ESTADOS_ACTIVOS, true)) {
            throw CobroImposible::inactivo();
        }

        // Si ya hay un intento vivo se reusa en vez de crear otro: dos
        // preferencias abiertas para el mismo pedido es la receta para que
        // alguien pague dos veces.
        $vigente = $this->intentoVigente($pedido);

        if ($vigente) {
            return $this->completarQr($vigente, $pedido);
        }

        $pedido->loadMissing('items.cerveza');

        $preferencia = $this->mp->crearPreferencia($pedido);

        // Se abren las dos puertas para el mismo pedido: el QR (se paga desde
        // la app de MP) y el link del checkout (tarjeta, en el navegador). Son
        // dos objetos distintos en MP, pero los dos llevan el código del pedido
        // como external_reference, así que da igual por cuál entre la plata.
        $qr = $this->generarQr($pedido);

        return Pago::create([
            'pedido_id' => $pedido->id,
            'iniciado_por' => $iniciador?->id,
            'origen' => $origen,
            'preference_id' => $preferencia['id'] ?? null,
            'estado' => Pago::PENDIENTE,
            'monto' => $pedido->precio_total,
            // Cuál de las dos URLs que devuelve MP corresponde lo decide el
            // cliente, que es quien sabe si el token es de prueba o real.
            'init_point' => $this->mp->puntoDeCheckout($preferencia),
            'qr_data' => $qr['qr_data'] ?? null,
            'in_store_order_id' => $qr['in_store_order_id'] ?? null,
        ]);
    }

    /**
     * Le pregunta a MP cómo viene el pago y, si está aprobado, cierra la venta.
     *
     * Es lo que hace que todo funcione sin webhook: en local MP no puede
     * alcanzar localhost, así que en vez de esperar el aviso se consulta. En
     * producción el webhook llega antes, pero esto queda como red de contención
     * por si un aviso se pierde.
     */
    public function sincronizar(Pago $pago): Pago
    {
        if ($pago->estaAprobado()) {
            return $pago;
        }

        $datos = $this->mp->ultimoPagoDePedido($pago->pedido->codigo);

        if (! $datos) {
            return $pago;
        }

        return $this->aplicar($pago, $datos);
    }

    /**
     * Procesa un aviso del webhook.
     *
     * Nunca se le cree el estado que viene en el cuerpo del aviso: se toma solo
     * el id y se le pregunta a MP. Si no, cualquiera que descubra la URL puede
     * mandar un "approved" y llevarse la mercadería sin pagar.
     */
    public function procesarAviso(string $paymentId): ?Pago
    {
        $datos = $this->mp->buscarPago($paymentId);

        if (! $datos) {
            Log::warning('MP: aviso de un pago que no existe', ['payment_id' => $paymentId]);

            return null;
        }

        $codigo = $datos['external_reference'] ?? null;

        if (! $codigo) {
            return null;
        }

        $pedido = Pedido::where('codigo', $codigo)->first();

        if (! $pedido) {
            Log::warning('MP: aviso de un pedido desconocido', ['codigo' => $codigo]);

            return null;
        }

        // Puede no haber intento previo si la preferencia se creó en otra
        // instancia (o se perdió la fila): se registra igual, porque lo que
        // manda es que MP dice que está pago.
        $pago = Pago::where('pedido_id', $pedido->id)
            ->latest('created_at')
            ->first()
            ?? Pago::create([
                'pedido_id' => $pedido->id,
                'origen' => Pago::ORIGEN_TIENDA,
                'estado' => Pago::PENDIENTE,
                'monto' => $pedido->precio_total,
            ]);

        return $this->aplicar($pago, $datos);
    }

    /**
     * El QR presencial, si la caja está configurada.
     *
     * Que falle no puede voltear el cobro: sin QR el cliente igual puede pagar
     * con tarjeta por el link del checkout. Se registra y se sigue.
     */
    private function generarQr(Pedido $pedido): array
    {
        if (! $this->mp->tieneCaja()) {
            return [];
        }

        try {
            return $this->mp->crearQrDinamico($pedido);
        } catch (\Throwable $e) {
            Log::warning('MP: no se pudo generar el QR, queda solo el link', [
                'pedido' => $pedido->codigo,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Si un intento reusado quedó sin QR, se le agrega ahora.
     *
     * Pasa cuando el QR falló la primera vez (MP caído, caja mal configurada)
     * o cuando el intento se creó antes de que existiera la caja. El link ya
     * es válido, así que no se crea otro intento: se completa el que hay.
     */
    private function completarQr(Pago $pago, Pedido $pedido): Pago
    {
        if ($pago->qr_data || ! $this->mp->tieneCaja()) {
            return $pago;
        }

        $pedido->loadMissing('items.cerveza');
        $qr = $this->generarQr($pedido);

        if (! empty($qr['qr_data'])) {
            $pago->update([
                'qr_data' => $qr['qr_data'],
                'in_store_order_id' => $qr['in_store_order_id'] ?? null,
            ]);
        }

        return $pago->fresh();
    }

    /** El intento que todavía puede prosperar, si hay alguno. */
    public function intentoVigente(Pedido $pedido): ?Pago
    {
        return Pago::where('pedido_id', $pedido->id)
            ->whereIn('estado', [Pago::PENDIENTE, Pago::EN_PROCESO])
            ->whereNotNull('init_point')
            ->latest('created_at')
            ->first();
    }

    /** El último intento de un pedido, en cualquier estado. */
    public function ultimoIntento(Pedido $pedido): ?Pago
    {
        return Pago::where('pedido_id', $pedido->id)->latest('created_at')->first();
    }

    /** Vuelca lo que dice MP sobre el pago y, si corresponde, cierra la venta. */
    private function aplicar(Pago $pago, array $datos): Pago
    {
        $estado = Pago::estadoDesdeMercadoPago($datos['status'] ?? null);

        $pago->update([
            'payment_id' => (string) ($datos['id'] ?? $pago->payment_id),
            'estado' => $estado,
            'detalle_estado' => $datos['status_detail'] ?? null,
            'pagado_en' => $estado === Pago::APROBADO ? now() : null,
        ]);

        if ($estado !== Pago::APROBADO) {
            return $pago->fresh();
        }

        try {
            // Un cobro del mostrador se le atribuye al cajero que lo generó;
            // uno de la tienda no tiene cobrador, porque no atendió nadie.
            $this->cobro->registrar(
                $pago->pedido,
                $pago->origen === Pago::ORIGEN_MOSTRADOR ? $pago->iniciado_por : null,
                RegistrarCobro::MERCADO_PAGO,
                idempotente: true, // MP reintenta el mismo aviso varias veces
            );
        } catch (CobroImposible $e) {
            // El pago entró pero la venta no se puede cerrar (se quedó sin
            // stock mientras el cliente pagaba). Queda registrado para poder
            // devolver la plata a mano: no hay forma automática y correcta de
            // resolverlo, y perderlo en silencio es peor.
            Log::error('MP: pago aprobado que no se pudo facturar', [
                'pago_id' => $pago->id,
                'pedido' => $pago->pedido->codigo,
                'motivo' => $e->getMessage(),
            ]);
        }

        return $pago->fresh();
    }
}
