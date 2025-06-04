<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\CarritoController;


// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // 📦 Carrito de compras (usuario común)
    Route::get('/carrito', [CarritoController::class, 'ver']);
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
    Route::delete('/carrito/quitar/{cerveza}', [CarritoController::class, 'quitar']);
    Route::post('/carrito/comprar', [CarritoController::class, 'comprar']);
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::post('/facturas/{id}/pagar', [FacturaController::class, 'pagar']);

    // 🔓 Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});