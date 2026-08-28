<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CobroImposible;
use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Pedido;
use App\Services\CobroMercadoPago;
use App\Services\MercadoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Pago por Mercado Pago iniciado por el cliente desde la tienda.
 *
 * El cliente nunca ve el id interno del pedido: todo va por el código (BV-XXXXXX),
 * igual que el resto de la API, para que no se pueda recorrer pedidos ajenos
 * cambiando un número en la URL.
 */
class PagoMercadoPagoController extends Controller
{
    public function __construct(private CobroMercadoPago $cobro, private MercadoPago $mp)
    {
    }

    /** Arranca el pago y devuelve la URL del checkout de Mercado Pago. */
    public function crear(Request $request, string $codigo)
    {
        if (! $this->mp->estaConfigurado()) {
            return response()->json([
                'error' => 'Mercado Pago no está configurado en este entorno.',
            ], 503);
        }

        $pedido = $this->pedidoDelUsuario($request, $codigo);

        try {
            $pago = $this->cobro->iniciar($pedido, null, \App\Models\Pago::ORIGEN_TIENDA);
        } catch (CobroImposible $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            Log::error('MP: no se pudo crear la preferencia', [
                'pedido' => $codigo,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'No se pudo conectar con Mercado Pago. Probá de nuevo en un momento.',
            ], 502);
        }

        return response()->json([
            'init_point' => $pago->init_point,
            'pago' => $this->comoJson($pago),
        ], 201);
    }

    /**
     * Estado del pago, consultándole a Mercado Pago.
     *
     * El frontend lo llama al volver del checkout: el webhook puede no haber
     * llegado todavía (o no existir, si se está corriendo en local), y sin esto
     * el cliente vuelve a una pantalla que sigue diciendo "pendiente" aunque
     * acabe de pagar.
     */
    public function estado(Request $request, string $codigo)
    {
        $pedido = $this->pedidoDelUsuario($request, $codigo);

        $pago = $this->cobro->ultimoIntento($pedido);

        if (! $pago) {
            return response()->json(['pago' => null, 'pedido_estado' => $pedido->estado]);
        }

        $pago = $this->cobro->sincronizar($pago);
        $pedido = $pedido->fresh();

        return response()->json([
            'pago' => $this->comoJson($pago),
            'pedido_estado' => $pedido->estado,
            // Va acá para que el frontend pueda llevar al comprobante apenas se
            // acredita, sin tener que pedir el historial entero y buscarla.
            'factura_id' => $pedido->estado === Pedido::PAGADO
                ? Factura::where('pedido_id', $pedido->id)->value('id')
                : null,
        ]);
    }

    /** Que nadie mire ni pague el pedido de otro. */
    private function pedidoDelUsuario(Request $request, string $codigo): Pedido
    {
        return Pedido::with('items.cerveza')
            ->where('codigo', $codigo)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    /** Solo lo que el frontend necesita: nada de ids internos ni del token. */
    private function comoJson(\App\Models\Pago $pago): array
    {
        return [
            'estado' => $pago->estado,
            'detalle' => $pago->detalle_estado,
            'monto' => $pago->monto,
            'init_point' => $pago->init_point,
            // La trama del QR presencial. Va al frontend para que la dibuje
            // quien esté en una computadora: desde el celular no sirve, porque
            // nadie puede escanear su propia pantalla.
            'qr_data' => $pago->qr_data,
            'pagado_en' => $pago->pagado_en,
        ];
    }
}
