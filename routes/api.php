<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\CervezaController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\EstiloController;



// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//para filtros
Route::get('/marcas', [MarcaController::class, 'index']);
Route::get('/estilos', [EstiloController::class, 'index']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // 📦 Carrito de compras (usuario común)
    Route::get('/cervezas', [CervezaController::class, 'index']);
    Route::get('/carrito', [CarritoController::class, 'ver']);
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
    Route::delete('/carrito/quitar/{cerveza}', [CarritoController::class, 'quitar']);
    Route::post('/carrito/generar-factura', [CarritoController::class, 'generarFactura']);
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::post('/factura/{id}/pagar', [FacturaController::class, 'pagar']);
    Route::post('/carrito/sincronizar', [CarritoController::class, 'sincronizar']);
    

    // 🔓 Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
