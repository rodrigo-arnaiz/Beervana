<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Services\CobroMercadoPago;
use App\Services\MercadoPago;
use App\Services\MercadoPagoSimulado;
use Illuminate\Http\Request;

/**
 * El checkout de mentira: lo que reemplaza a la pantalla de Mercado Pago
 * cuando MERCADOPAGO_MODO=simulado.
 *
 * Existe para poder desarrollar y demostrar el flujo completo sin credenciales
 * y sin internet. Va sin login porque el checkout real tampoco lo tiene: al
 * cliente se le pasa un link (o un QR) y paga, sin cuenta en Beervana.
 *
 * Se identifica por el código del pedido y no por el id del pago, para que la
 * URL sea la misma que se pueda leer de un QR sin filtrar ids internos.
 */
class CheckoutSimuladoController extends Controller
{
    public function __construct(private MercadoPago $mp, private CobroMercadoPago $cobro)
    {
    }

    public function mostrar(string $codigo)
    {
        abort_unless($this->esSimulado(), 404);

        $pedido = Pedido::with('items.cerveza')->where('codigo', $codigo)->firstOrFail();
        $pago = $this->cobro->ultimoIntento($pedido);

        abort_if(! $pago, 404, 'Ese pedido no tiene un cobro iniciado.');

        return view('checkout-simulado', [
            'pedido' => $pedido,
            'pago' => $pago,
            'yaResuelto' => $pago->payment_id !== null,
        ]);
    }

    /**
     * El cliente eligió: aprobar o rechazar.
     *
     * Después de anotar la decisión se llama al webhook por dentro, que es
     * exactamente lo que haría Mercado Pago. Gracias a eso el modo simulado
     * recorre el mismo camino que el real —incluido el webhook— en vez de tomar
     * un atajo que dejaría ese código sin ejercitar.
     */
    public function resolver(Request $request, string $codigo)
    {
        abort_unless($this->esSimulado(), 404);

        $datos = $request->validate([
            'resultado' => 'required|in:aprobar,rechazar',
        ]);

        $pedido = Pedido::where('codigo', $codigo)->firstOrFail();
        $pago = $this->cobro->ultimoIntento($pedido);

        abort_if(! $pago, 404);

        /** @var MercadoPagoSimulado $mp */
        $mp = $this->mp;
        $paymentId = $mp->resolver($pago, $datos['resultado'] === 'aprobar');

        $this->cobro->procesarAviso($paymentId);

        return redirect()->route('checkout.simulado', ['codigo' => $codigo]);
    }

    private function esSimulado(): bool
    {
        return config('services.mercadopago.modo') === 'simulado';
    }
}
