<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cerveza;
use Illuminate\Http\Request;

class CervezaController extends Controller
{
    # devuelve las cervezas con stock mayor a 0
    #public function index(){
      #  $cervezas = Cerveza::where('stock', '>', 0)->get();
       # return response()->json($cervezas, 200);
    #}
    public function index(Request $request)
    {
        // disponibles() filtra por stock MENOS lo reservado por pedidos
        // pendientes vigentes, y agrega las columnas `reservado` y `disponible`.
        // Una cerveza con stock 5 y 5 unidades reservadas no aparece: no se
        // puede pedir aunque físicamente esté en el depósito.
        $query = Cerveza::with('marca', 'estilo')->disponibles();

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('estilo_id')) {
            $query->where('estilo_id', $request->estilo_id);
        }

        $cervezas = $query->orderBy('nombre')->get();

        return response()->json($cervezas, 200);
    } 
}
