<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cerveza;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {


        $cervezas = Cerveza::all();

        return view('admin.dashboard', compact('cervezas'));
    }
}
