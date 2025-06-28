<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estilo;

class EstiloController extends Controller
{
    public function index()
    {
        return response()->json(Estilo::orderBy('nombre')->get());
    }
}






    

