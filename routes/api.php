<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\Api\ApiCarritoController;
use App\Http\Controllers\Api\ApiVentaController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/carrito', [ApiCarritoController::class, 'index']);
    Route::post('/carrito/agregar', [ApiCarritoController::class, 'agregar']);
    Route::post('/carrito/eliminar', [ApiCarritoController::class, 'eliminar']);
    Route::post('/venta', [ApiVentaController::class, 'procesarVenta']);
});

