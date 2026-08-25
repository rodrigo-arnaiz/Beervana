<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\CervezaController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\EstiloController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\PedidoController;



// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//para filtros
Route::get('/marcas', [MarcaController::class, 'index']);
Route::get('/estilos', [EstiloController::class, 'index']);

// Costo del envío, para que el carrito no lo tenga hardcodeado
Route::get('/costo-envio', [ConfiguracionController::class, 'envio']);

// Rutas protegidas
// no.bloqueado va acá y no en cada ruta: una cuenta bloqueada no tiene que
// poder hacer nada, ni comprar ni mirar su carrito.
Route::middleware(['auth:sanctum', 'no.bloqueado'])->group(function () {

    // 📦 Carrito de compras (usuario común)
    Route::get('/cervezas', [CervezaController::class, 'index']);
    Route::get('/carrito', [CarritoController::class, 'ver']);
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
    Route::delete('/carrito/quitar/{cerveza}', [CarritoController::class, 'quitar']);
    // 🧾 Pedidos: comprar reserva stock, pagar emite la factura
    Route::get('/pedidos', [PedidoController::class, 'index']);
    // {codigo} y no {id}: el id es secuencial y se puede recorrer a mano
    Route::get('/pedidos/{codigo}', [PedidoController::class, 'show']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::post('/pedidos/{codigo}/cancelar', [PedidoController::class, 'cancelar']);
    // El cliente avisa que va a pagarlo en el local. No cobra nada.
    Route::post('/pedidos/{codigo}/confirmar', [PedidoController::class, 'confirmar']);

    // 💵 Mostrador: el cobro es presencial, así que registrarlo es acción del
    // personal. Si el cliente pudiera llamarlo, marcaría su propio pedido como
    // pagado y se llevaría la mercadería sin pagar. El control de is_admin está
    // dentro del controlador para responder 403 en JSON.
    Route::get('/mostrador/pedido', [PedidoController::class, 'buscarPorCodigo']);
    Route::post('/mostrador/pedidos/{id}/cobrar', [PedidoController::class, 'pagar']);

    // Historial de compras: solo ventas concretadas
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::get('/facturas/{id}', [FacturaController::class, 'show']);
    Route::post('/carrito/sincronizar', [CarritoController::class, 'sincronizar']);
    Route::post('/carrito/limpiar', [CarritoController::class, 'vaciar']);


    // 🔓 Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
