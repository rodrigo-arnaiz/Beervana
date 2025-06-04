<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factura;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $facturas = Factura::with(['detalles.cerveza'])
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($facturas);
    }

    public function pagar(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);

        if ($factura->pagada) {
            return response()->json(['message' => 'La factura ya fue pagada'], 400);
        }

        $factura->pagada = true;
        $factura->save();

        return response()->json(['message' => 'Factura pagada con exito', 'factura' => $factura]);
    }
}
