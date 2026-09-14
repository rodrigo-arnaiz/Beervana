<?php

namespace App\Services;

use App\Exceptions\CobroImposible;
use App\Models\Cerveza;
use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

/**
 * Cierra la venta de un pedido: descuenta el stock y emite la factura.
 *
 * Existe porque el cobro llega por tres puertas distintas —el mostrador del
 * panel, la API del mostrador y ahora el webhook de Mercado Pago— y las tres
 * tienen que hacer exactamente lo mismo. Cuando la lógica estaba copiada en
 * cada controlador, agregar un estado nuevo significaba acordarse de tocar
 * todas las copias; la que se olvidaba fallaba en silencio.
 */
class RegistrarCobro
{
    public const EFECTIVO = 'efectivo';
    public const MERCADO_PAGO = 'mercadopago';

    /**
     * @param  Pedido    $pedido       el pedido a cerrar
     * @param  int|null  $cobradorId   quién atendió; null si lo pagó el cliente solo
     * @param  string    $medioPago    con qué se pagó
     * @param  bool      $idempotente  qué hacer si el pedido ya estaba pagado
     *
     * Sobre $idempotente: cobrar dos veces significa cosas distintas según
     * quién pregunte. Mercado Pago reintenta el mismo aviso varias veces y ahí
     * el segundo tiene que devolver la factura que ya existe, sin descontar
     * stock de nuevo. Pero una persona que aprieta "Cobrar" dos veces en el
     * mostrador necesita que le digan que ese pedido ya estaba cobrado: un
     * éxito silencioso se lee como "listo, cobré", y nadie cobró nada.
     *
     * @throws CobroImposible si el pedido no está en condiciones de cobrarse
     */
    public function registrar(
        Pedido $pedido,
        ?int $cobradorId,
        string $medioPago = self::EFECTIVO,
        bool $idempotente = false,
    ): Factura {
        // El vencimiento se marca ACÁ, fuera de la transacción, y no adentro:
        // el throw que viene después haría rollback de la propia marca, y el
        // pedido seguiría figurando como pendiente reservando stock que ya
        // nadie va a pagar.
        if ($pedido->estado !== Pedido::PAGADO && $pedido->estaVencido()) {
            $pedido->update(['estado' => Pedido::VENCIDO, 'expira_en' => null]);

            throw CobroImposible::vencido();
        }

        return DB::transaction(function () use ($pedido, $cobradorId, $medioPago, $idempotente) {
            // Se relee dentro de la transacción y con lock: dos cobros
            // simultáneos del mismo pedido (el cajero y el webhook de MP
            // llegando juntos) tienen que serializarse acá, o los dos ven
            // "pendiente" y los dos descuentan stock.
            $pedido = Pedido::with('items')->lockForUpdate()->findOrFail($pedido->id);

            if ($pedido->estado === Pedido::PAGADO) {
                $yaEmitida = Factura::where('pedido_id', $pedido->id)->first();

                if ($idempotente && $yaEmitida) {
                    return $yaEmitida;
                }

                if (! $idempotente) {
                    throw CobroImposible::yaPagado();
                }

                // Pagado y sin factura no debería pasar, pero si pasa es mejor
                // emitirla que dejar una venta cobrada sin comprobante.
            } else {
                // Se revisa de nuevo bajo lock por si venció entre el chequeo
                // de arriba y esta línea. Acá solo se rechaza: la marca ya se
                // hizo afuera, donde sobrevive al rollback.
                if ($pedido->estaVencido()) {
                    throw CobroImposible::vencido();
                }

                if (! in_array($pedido->estado, Pedido::ESTADOS_ACTIVOS, true)) {
                    throw CobroImposible::inactivo();
                }

                foreach ($pedido->items as $item) {
                    $cerveza = Cerveza::whereKey($item->cerveza_id)->lockForUpdate()->first();

                    if ($cerveza->stock < $item->cantidad) {
                        throw CobroImposible::sinStock($cerveza->nombre);
                    }

                    $cerveza->decrement('stock', $item->cantidad);
                }

                $pedido->update(['estado' => Pedido::PAGADO, 'expira_en' => null]);
            }

            return Factura::create([
                'pedido_id' => $pedido->id,
                'user_id' => $pedido->user_id,   // a nombre de quién es la factura
                'cobrado_por' => $cobradorId,    // quién la cobró
                'medio_pago' => $medioPago,
                'fecha' => now(),
                'precio_total' => $pedido->precio_total,
            ]);
        });
    }
}
