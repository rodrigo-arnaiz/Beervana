<?php

namespace App\Http\Controllers;

use App\Services\CobroMercadoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe los avisos de pago de Mercado Pago.
 *
 * Es la única ruta de la app sin autenticación por diseño: la llama MP, no una
 * persona. Por eso hay una regla que no se negocia acá: del cuerpo del aviso se
 * toma SOLO el id del pago, y el estado se le pregunta a MP. Si se confiara en
 * el "status" que viene en el POST, cualquiera que descubra la URL manda un
 * approved y se lleva la mercadería sin pagar.
 *
 * Siempre responde 200. MP reintenta ante cualquier otra cosa, y un pedido que
 * no reconocemos no mejora porque nos lo reenvíen veinte veces.
 *
 * @see https://www.mercadopago.com.ar/developers/es/docs/your-integrations/notifications/webhooks
 */
class WebhookMercadoPagoController extends Controller
{
    public function __invoke(Request $request, CobroMercadoPago $cobro)
    {
        // MP manda el id en distintos lugares según el tipo de aviso
        $tipo = $request->input('type') ?? $request->query('type');
        $paymentId = $request->input('data.id')
            ?? $request->query('data_id')
            ?? $request->input('id');

        Log::info('MP: aviso recibido', ['tipo' => $tipo, 'payment_id' => $paymentId]);

        // Los avisos de merchant_order y demás no interesan: la venta se cierra
        // con el pago concreto.
        if ($tipo !== 'payment' || blank($paymentId)) {
            return response()->json(['ok' => true]);
        }

        try {
            $cobro->procesarAviso((string) $paymentId);
        } catch (\Throwable $e) {
            // Se loguea y se responde 200 igual: si devolvemos error, MP
            // reintenta en bucle un aviso que va a volver a fallar. El pago no
            // se pierde porque el frontend y el mostrador también consultan el
            // estado por su cuenta.
            Log::error('MP: falló el procesamiento del aviso', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
