<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Cerveza;
use App\Models\Factura;
use App\Models\DetalleFactura;
use Illuminate\Support\Facades\DB;
use Log;

class CarritoController extends Controller
{
    public function ver(Request $request)
    {
        $carrito = Carrito::firstOrCreate(['user_id' => $request->user()->id]);
        $carrito->load('items.cerveza');

        return response()->json($carrito);
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'cerveza_id' => 'required|exists:cervezas,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $carrito = Carrito::firstOrCreate(['user_id' => $request->user()->id]);

        $item = $carrito->items()->where('cerveza_id', $request->cerveza_id)->first();

        if ($item) {
            $item->cantidad += $request->cantidad;
            $item->save();
        } else {
            $carrito->items()->create([
                'cerveza_id' => $request->cerveza_id,
                'cantidad' => $request->cantidad,
            ]);
        }

        return response()->json(['message' => 'Cerveza agregada al carrito']);
    }

    public function quitar(Request $request, Cerveza $cerveza)
    {
        $carrito = Carrito::where('user_id', $request->user()->id)->first();

        if (!$carrito) {
            return response()->json(['message' => 'Carrito vacío'], 404);
        }

        $item = $carrito->items()->where('cerveza_id', $cerveza->id)->first();

        if (!$item) {
            return response()->json(['message' => 'Esa cerveza no está en el carrito'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Cerveza eliminada del carrito']);
    }

    public function vaciar(Request $request)
    {
        $carrito = Carrito::where('user_id', $request->user()->id)->first();

        if ($carrito) {
            $carrito->items()->delete();
        }

        return response()->json(['message' => 'Carrito vaciado']);
    }

public function generarFactura(Request $request)
{
    $user = $request->user();
    $items = $request->input('items', []);

    if (empty($items)) {
        return response()->json(['message' => 'El carrito está vacío'], 400);
    }

    DB::beginTransaction();

    try {
        $precio_total = 0;

        $factura = Factura::create([
            'user_id' => $user->id,
            'fecha' => now(),
            'precio_total' => 0,
            'pagada' => false,
        ]);

        foreach ($items as $item) {
            $cerveza = Cerveza::find($item['id']);

            if (!$cerveza || $cerveza->stock < $item['cantidad']) {
                throw new \Exception("No hay stock suficiente para la cerveza {$cerveza->nombre}");
            }

            $subtotal = $cerveza->precio * $item['cantidad'];

            DetalleFactura::create([
                'factura_id' => $factura->id,
                'cerveza_id' => $cerveza->id,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $cerveza->precio,
                'subtotal' => $subtotal,
            ]);

            $precio_total += $subtotal;
        }

        $factura->update(['precio_total' => $precio_total]);

        DB::commit();

        return response()->json([
            'message' => 'Factura generada exitosamente',
            'factura' => $factura->load('detalles.cerveza')
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



public function sincronizar(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    $items = $request->input('items', []);

    \Log::info('Items recibidos para sincronizar:', $items);


    if (!is_array($items) || empty($items)) {
        return response()->json(['error' => 'No se recibieron items válidos'], 400);
    }

    $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

    // Borrar los ítems anteriores del carrito
    $carrito->items()->delete();

    // Insertar los nuevos ítems asociados correctamente
    foreach ($items as $item) {
        $carrito->items()->create([
            'cerveza_id' => $item['cerveza_id'],
            'cantidad' => $item['cantidad']
        ]);
    }

    return response()->json(['message' => 'Carrito sincronizado correctamente']);
}



}
