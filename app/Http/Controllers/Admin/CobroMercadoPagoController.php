<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CobroImposible;
use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Pedido;
use App\Services\CobroMercadoPago;
use App\Services\MercadoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Cobro por Mercado Pago generado desde el mostrador.
 *
 * El escenario es el cliente parado en la caja diciendo "lo pago con Mercado
 * Pago": el cajero genera el cobro, aparece un QR en pantalla, el cliente lo
 * escanea con el celular y paga. La pantalla se entera sola.
 *
 * A diferencia del cobro que inicia el cliente en la tienda, este queda
 * atribuido al cajero (iniciado_por), porque para el arqueo de caja del turno
 * esa venta la atendió él aunque la plata haya entrado por MP.
 */
class CobroMercadoPagoController extends Controller
{
    public function __construct(private CobroMercadoPago $cobro, private MercadoPago $mp)
    {
    }

    /** Genera el cobro y muestra la pantalla de espera con el QR. */
    public function crear(Request $request, $id)
    {
        if (! $this->mp->estaConfigurado()) {
            return back()->with('error', 'Mercado Pago no está configurado. Revisá el .env.');
        }

        $pedido = Pedido::findOrFail($id);

        try {
            $pago = $this->cobro->iniciar($pedido, $request->user(), Pago::ORIGEN_MOSTRADOR);
        } catch (CobroImposible $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('MP: no se pudo generar el cobro del mostrador', [
                'pedido' => $pedido->codigo,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo conectar con Mercado Pago. Probá de nuevo.');
        }

        return redirect()->route('mostrador.mp.esperar', ['pago' => $pago->id]);
    }

    /** La pantalla de espera: QR, link y el estado actualizándose solo. */
    public function esperar(Pago $pago)
    {
        return view('admin.mostrador-mercadopago', [
            'pago' => $pago->load('pedido.items.cerveza'),
        ]);
    }

    /**
     * Estado del cobro en JSON, para que la pantalla se refresque sola.
     *
     * Consulta a MP en cada llamada en vez de leer solo nuestra base: en local
     * el webhook no llega (MP no puede alcanzar localhost), así que si esto no
     * preguntara, la pantalla se quedaría esperando para siempre.
     */
    public function estado(Pago $pago)
    {
        $pago = $this->cobro->sincronizar($pago);
        $pedido = $pago->pedido->fresh();

        return response()->json([
            'estado' => $pago->estado,
            'detalle' => $pago->detalle_estado,
            'pedido_estado' => $pedido->estado,
            'listo' => $pedido->estado === Pedido::PAGADO,
            'volver_a' => route('mostrador.index'),
        ]);
    }

    /** Cancelar la espera: el cliente cambió de idea y paga en efectivo. */
    public function cancelar(Pago $pago)
    {
        if ($pago->estaAprobado()) {
            return redirect()->route('mostrador.index')
                ->with('error', 'Ese cobro ya se acreditó, no se puede cancelar desde acá.');
        }

        // Solo se cierra el intento de nuestro lado. La preferencia queda viva
        // en MP: si el cliente ya había escaneado el QR y paga igual, el pago
        // llega y se procesa. Por eso el pedido no se toca.
        $pago->update(['estado' => Pago::CANCELADO]);

        return redirect()->route('mostrador.index')
            ->with('exito', "Se canceló el cobro por Mercado Pago del pedido {$pago->pedido->codigo}.");
    }
}
