<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Illuminate\Http\Request;

/**
 * Solo lectura: las facturas se emiten al pagar un pedido (PedidoController),
 * nunca antes. Que exista una factura significa que la venta se concretó.
 */
class FacturaController extends Controller
{
    /** Historial de compras del usuario. */
    public function index(Request $request)
    {
        // Se incluye el usuario porque la factura impresa lleva los datos del
        // cliente. El modelo oculta password y remember_token al serializar.
        $facturas = Factura::with('pedido.items.cerveza', 'user')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json($facturas);
    }

    public function show(Request $request, $id)
    {
        // Filtrar por user_id es obligatorio: con findOrFail a secas cualquier
        // usuario autenticado podría leer la factura de otro pasando su id.
        $factura = Factura::with('pedido.items.cerveza', 'user')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($factura);
    }
}
