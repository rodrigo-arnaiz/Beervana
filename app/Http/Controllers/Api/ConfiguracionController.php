<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;

class ConfiguracionController extends Controller
{
    /**
     * Valores que el frontend necesita mostrar antes de generar la factura.
     *
     * Existe para que el costo del envío tenga una sola fuente de verdad. Si el
     * carrito lo tuviera hardcodeado, cambiarlo acá dejaría al usuario viendo un
     * total y pagando otro, que es justamente el problema que se arregló.
     */
    public function envio()
    {
        return response()->json([
            'costo_envio' => (float) Pedido::COSTO_ENVIO,
            'metodos' => Pedido::METODOS_ENTREGA,
            'horas_vencimiento_pedido' => Pedido::HORAS_VENCIMIENTO,
        ]);
    }
}
