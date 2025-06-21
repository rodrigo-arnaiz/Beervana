<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cerveza;
use Illuminate\Http\Request;

class CervezaController extends Controller
{
    # devuelve las cervezas con stock mayor a 0
    public function index(){
        $cervezas = Cerveza::where('stock', '>', 0)->get();
        return response()->json($cervezas, 200);
    } 
}
