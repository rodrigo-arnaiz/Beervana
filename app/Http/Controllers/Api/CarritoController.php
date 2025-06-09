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

    public function comprar(Request $request)
    {
        $user = $request->user();
        $carrito = Carrito::with('items.cerveza')->where('user_id', $user->id)->first();
        #$carrito = Carrito::where('user_id', $user->id)->with('items.cerveza')->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío'], 400);
        }

        DB::beginTransaction();

        try {
            $precio_total = 0;

            // Crear factura
            $factura = Factura::create([
                'user_id' => $user->id,
                'fecha' => now(),
                'precio_total' => 0,
                'pagada' => false,
            ]);

            foreach ($carrito->items as $item) {
                $cerveza = $item->cerveza;

                if ($cerveza->stock < $item->cantidad) {
                    throw new \Exception("No hay stock suficiente para la cerveza {$cerveza->nombre}");
                }

                $subtotal = $cerveza->precio * $item->cantidad;

                DetalleFactura::create([
                    'factura_id' => $factura->id,
                    'cerveza_id' => $cerveza->id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $cerveza->precio,
                    'subtotal' => $subtotal,
                ]);

                // Descontamos el stock
                $cerveza->stock -= $item->cantidad;
                $cerveza->save();

                $precio_total += $subtotal;
            }

            /* $factura->precio_total = $precio_total;
            $factura->save(); */
            $factura->update(['precio_total' => $precio_total]);

            // Vaciar carrito
            $carrito->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Compra realizada con éxito',
                #'factura_id' => $factura->id
                'factura' => $factura->load('detalles.cerveza')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar la compra'], 500);
        }
    }
}
