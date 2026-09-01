<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Pedido;

/**
 * Mercado Pago de mentira, para desarrollar y demostrar sin credenciales.
 *
 * Reemplaza a MercadoPago cuando MERCADOPAGO_MODO=simulado. No llama a nadie:
 * devuelve lo mismo que devolvería la API de MP, con la forma exacta que espera
 * el resto del sistema.
 *
 * El punto es que NADA más cambia. Los controladores, el webhook, la pantalla
 * del mostrador y el frontend son los mismos: el único que sabe que esto es una
 * simulación es este archivo. Así el modo real y el simulado recorren el mismo
 * camino, y probar en simulado sirve para confiar en el real.
 *
 * En vez del checkout de MP, el link lleva a una pantalla propia donde se elige
 * si el pago sale aprobado o rechazado. Esa decisión se guarda en el payment_id
 * del intento, que es de donde la leen los dos caminos por los que el sistema
 * se entera de un pago: la consulta periódica y el webhook.
 */
class MercadoPagoSimulado extends MercadoPago
{
    /** Prefijos que codifican el desenlace en el propio id del pago. */
    private const APROBADO = 'SIM-APRO';
    private const RECHAZADO = 'SIM-RECH';

    /** En simulado nunca falta el token: no hay a quién autenticarse. */
    public function estaConfigurado(): bool
    {
        return true;
    }

    public function crearPreferencia(Pedido $pedido): array
    {
        // El "checkout" es una pantalla nuestra. El resto de la respuesta imita
        // a la de MP para que quien la consuma no note la diferencia.
        $id = 'SIM-PREF-'.$pedido->codigo;

        return [
            'id' => $id,
            'init_point' => route('checkout.simulado', ['codigo' => $pedido->codigo]),
            'sandbox_init_point' => route('checkout.simulado', ['codigo' => $pedido->codigo]),
            'external_reference' => $pedido->codigo,
        ];
    }

    public function buscarPago(string $paymentId): ?array
    {
        $pago = Pago::where('payment_id', $paymentId)->first();

        if (! $pago) {
            return null;
        }

        return $this->comoRespuestaDeMp($paymentId, $pago->pedido->codigo);
    }

    public function ultimoPagoDePedido(string $codigoPedido): ?array
    {
        $pedido = Pedido::where('codigo', $codigoPedido)->first();

        if (! $pedido) {
            return null;
        }

        $pago = Pago::where('pedido_id', $pedido->id)
            ->whereNotNull('payment_id')
            ->latest('updated_at')
            ->first();

        // Sin payment_id todavía no pasó nada: es el equivalente a que el
        // cliente tenga el checkout abierto sin haber apretado nada.
        if (! $pago) {
            return null;
        }

        return $this->comoRespuestaDeMp($pago->payment_id, $codigoPedido);
    }

    /**
     * Registra la decisión tomada en la pantalla del checkout falso.
     *
     * Escribe el desenlace en el payment_id en vez de tocar el estado del Pago
     * directamente: así el flujo sigue siendo "MP sabe algo, nosotros se lo
     * preguntamos", igual que en el modo real. Si acá se marcara el pago como
     * aprobado a mano, el modo simulado dejaría de probar el camino de verdad.
     */
    public function resolver(Pago $pago, bool $aprobado): string
    {
        $paymentId = ($aprobado ? self::APROBADO : self::RECHAZADO).'-'.$pago->id;

        $pago->update(['payment_id' => $paymentId]);

        return $paymentId;
    }

    /** En simulado no hay URLs que validar: nada sale de esta máquina. */
    public function esUrlPublica(?string $url): bool
    {
        return false;
    }

    /** La misma forma que tiene un pago en la API de MP. */
    private function comoRespuestaDeMp(string $paymentId, string $codigoPedido): array
    {
        $aprobado = str_starts_with($paymentId, self::APROBADO);

        return [
            'id' => $paymentId,
            'status' => $aprobado ? 'approved' : 'rejected',
            'status_detail' => $aprobado ? 'accredited' : 'cc_rejected_other_reason',
            'external_reference' => $codigoPedido,
        ];
    }
}
