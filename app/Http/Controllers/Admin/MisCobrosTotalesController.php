<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Cobros registrados por quien está usando el sistema.
 *
 * Un empleado ve solo los suyos: es su arqueo de caja. Un admin puede ver los
 * de cualquiera pasando ?cobrador=, porque necesita el control del negocio.
 */
class MisCobrosTotalesController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Un empleado no elige: siempre son los propios
        $cobradorId = $usuario->esAdmin()
            ? ($request->integer('cobrador') ?: $usuario->id)
            : $usuario->id;

        $desde = $request->date('desde') ?? now()->startOfDay();
        $hasta = $request->date('hasta') ?? now()->endOfDay();

        $facturas = Factura::with('pedido.items.cerveza', 'user')
            ->where('cobrado_por', $cobradorId)
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderByDesc('id')
            ->get();

        return view('admin.mis-cobros', [
            'facturas' => $facturas,
            'total' => $facturas->sum('precio_total'),
            'desde' => $desde,
            'hasta' => $hasta,
            'cobradorId' => $cobradorId,
            'esAdmin' => $usuario->esAdmin(),
            // Para que el admin pueda mirar la caja de cada empleado
            'personal' => $usuario->esAdmin()
                ? User::whereIn('rol', [User::ROL_EMPLEADO, User::ROL_ADMIN])->orderBy('name')->get()
                : collect(),
        ]);
    }
}
