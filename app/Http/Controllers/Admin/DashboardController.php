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
    // Solo una vez traemos las cervezas paginadas
    $cervezasPaginadas = Cerveza::with('marca')->paginate(10);

    // Solo contamos directamente sin traer todos los registros
    $totalCervezas = Cerveza::count();

    // Sumamos el stock directamente con agregación
    $totalStock = Cerveza::sum('stock');

    $pedidosPendientes = Factura::where('pagada', true)->count();

    $totalStockCritico = Cerveza::where('stock', '<', 10)->count();

    // Top cervezas vendidas
    $topCervezas = DB::table('detalle_factura')
        ->join('facturas', 'detalle_factura.factura_id', '=', 'facturas.id')
        ->join('cervezas', 'detalle_factura.cerveza_id', '=', 'cervezas.id')
        ->select('cervezas.nombre', DB::raw('SUM(detalle_factura.cantidad) as total_vendido'))
        ->where('facturas.pagada', true)
        ->groupBy('cervezas.nombre')
        ->orderByDesc('total_vendido')
        ->limit(5)
        ->get();

    // Total stock agrupado por marca, usando query directa
    $stockPorMarca = DB::table('cervezas')
        ->join('marcas', 'cervezas.marca_id', '=', 'marcas.id')
        ->select('marcas.nombre', DB::raw('SUM(cervezas.stock) as total_stock'))
        ->groupBy('marcas.nombre')
        ->pluck('total_stock', 'nombre'); // para obtener [marca => total_stock]

    // Facturación por cerveza
    $facturacionPorCerveza = DB::table('detalle_factura')
        ->join('cervezas', 'detalle_factura.cerveza_id', '=', 'cervezas.id')
        ->join('facturas', 'detalle_factura.factura_id', '=', 'facturas.id')
        ->where('facturas.pagada', true)
        ->select('cervezas.nombre as cerveza', DB::raw('SUM(detalle_factura.subtotal) as total_facturado'))
        ->groupBy('cervezas.nombre')
        ->orderByDesc('total_facturado')
        ->limit(10)
        ->get();

    // Cervezas con stock <= 10 para alertas
    $cervezasCriticas = Cerveza::where('stock', '<=', 10)->get(['nombre', 'stock']);

    $alertas = $cervezasCriticas->map(function ($c) {
        return [
            'tipo' => $c->stock == 0 ? 'danger' : 'warning',
            'mensaje' => "Cerveza \"{$c->nombre}\" con stock " . ($c->stock == 0 ? "en 0." : "bajo ({$c->stock} unidades).")
        ];
    });

    return view('admin.dashboard', compact(
        'cervezasPaginadas',
        'totalCervezas',
        'totalStock',
        'pedidosPendientes',
        'totalStockCritico',
        'topCervezas',
        'stockPorMarca',
        'facturacionPorCerveza',
        'alertas'
    ));
}

}