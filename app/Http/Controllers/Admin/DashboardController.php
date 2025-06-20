<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cerveza;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
    public function index(Request $request)
    {


        $cervezas = Cerveza::all();

        $totalStock = $cervezas->sum('stock'); // suma el campo "stock"

        $pedidosPendientes = Factura::where('pagada', false)->count(); // los pedidos pendientes de pago

        $stockCritico = Cerveza::where('stock', '<', 10)->get(); // las cervezas con stock critico
        $totalStockCritico = $stockCritico->count();

        $topCervezas = DB::table('detalle_factura')
            ->join('facturas', 'detalle_factura.factura_id', '=', 'facturas.id')
            ->join('cervezas', 'detalle_factura.cerveza_id', '=', 'cervezas.id')
            ->select('cervezas.nombre', DB::raw('SUM(detalle_factura.cantidad) as total_vendido'))
            ->where('facturas.pagada', true)
            ->groupBy('cervezas.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $stockPorMarca = \App\Models\Cerveza::with('marca')
            ->get()
            ->groupBy('marca.nombre')
            ->map(function ($items) {
                return $items->sum('stock');
                });

        $facturacionPorCerveza = DetalleFactura::select(
                'cervezas.nombre as cerveza',
                DB::raw('SUM(detalle_factura.subtotal) as total_facturado')
            )
            ->join('cervezas', 'detalle_factura.cerveza_id', '=', 'cervezas.id')
            ->join('facturas', 'detalle_factura.factura_id', '=', 'facturas.id')
            ->where('facturas.pagada', true)
            ->groupBy('cervezas.nombre')
            ->orderByDesc('total_facturado')
            ->limit(10) // top 10
            ->get();

        $alertas = [];

        $cervezasCriticas = Cerveza::where('stock', '<=', 10)->get();

        foreach ($cervezasCriticas as $c) {
            if ($c->stock == 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'mensaje' => "Cerveza \"{$c->nombre}\" con stock en 0."
                ];
            } else {
                $alertas[] = [
                    'tipo' => 'warning',
                    'mensaje' => "Cerveza \"{$c->nombre}\" con stock bajo ({$c->stock} unidades)."
                ];
            }
        }


        return view('admin.dashboard', compact(
            'cervezas',
            'totalStock',
            'pedidosPendientes',
            'totalStockCritico',
            'topCervezas',
            'stockPorMarca', 
            'facturacionPorCerveza',
            'alertas'));
    }
}
