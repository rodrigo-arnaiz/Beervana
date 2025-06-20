<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Models\Factura;
use App\Models\DetalleFactura;

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

        return response()->json(['message' => 'Factura pagada con éxito', 'factura' => $factura]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.cerveza_id' => 'required|exists:cervezas,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            $total = 0;

            $factura = Factura::create([
                'user_id' => $user->id,
                'fecha' => $request->fecha,
                'precio_total' => 0,
                'pagada' => false,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $total += $subtotal;

                DetalleFactura::create([
                    'factura_id' => $factura->id,
                    'cerveza_id' => $item['cerveza_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }

            $factura->update(['precio_total' => $total]);

            DB::commit();

            return response()->json([
                'message' => 'Factura creada correctamente',
                'factura' => $factura->load('detalles.cerveza')
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear la factura', 'details' => $e->getMessage()], 500);
        }
    }
}
