<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CobroImposible;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\RegistrarCobro;
use Illuminate\Http\Request;

/**
 * Pantalla de cobro presencial.
 *
 * Separa los pedidos en dos grupos porque significan cosas distintas para quien
 * atiende: los CONFIRMADOS son clientes que avisaron que vienen a pagar, y los
 * PENDIENTES son pedidos que el cliente dejó armados sin decidir nada todavía.
 * Los dos reservan stock y los dos se pueden cobrar.
 */
class MostradorController extends Controller
{
    public function index(Request $request)
    {
        $buscado = trim((string) $request->query('codigo'));

        $encontrado = $buscado === ''
            ? null
            : Pedido::with('items.cerveza', 'user', 'factura')
                ->where('codigo', strtoupper($buscado))
                ->first();

        $activos = Pedido::with('items.cerveza', 'user')
            ->reservando()
            ->orderBy('expira_en')
            ->get();

        return view('admin.mostrador', [
            'buscado' => $buscado,
            'encontrado' => $encontrado,
            'sinResultado' => $buscado !== '' && ! $encontrado,
            // Los que avisaron que vienen van primero: son los que hay que atender
            'confirmados' => $activos->where('estado', Pedido::CONFIRMADO),
            'pendientes' => $activos->where('estado', Pedido::PENDIENTE),
        ]);
    }

    /** Registra el cobro: baja el stock y emite la factura. */
    public function cobrar(Request $request, RegistrarCobro $cobro, $id)
    {
        $pedido = Pedido::findOrFail($id);

        try {
            // El descuento de stock y la emisión de la factura viven en el
            // servicio: el cobro entra por acá, por la API y por el webhook de
            // Mercado Pago, y las tres tienen que hacer exactamente lo mismo.
            $cobro->registrar($pedido, $request->user()->id, RegistrarCobro::EFECTIVO);
        } catch (CobroImposible $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo cobrar: '.$e->getMessage());
        }

        return redirect()
            ->route('mostrador.index')
            ->with('exito', "Cobrado el pedido {$pedido->codigo} por \${$pedido->precio_total}.");
    }
}
