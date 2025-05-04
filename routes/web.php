<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoFermentacionController;
use App\Http\Controllers\EstiloController;
use App\Http\Controllers\CervezaController;
use App\Http\Controllers\HomeController;

// desactiva el registro si no lo necesitas
Auth::routes(['register' => false]);

// redirige a login si va a la raíz y no está autenticado
Route::get('/', function () {
    return redirect('/home');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::resource('marcas', MarcaController::class);
    Route::resource('tipo-fermentaciones', TipoFermentacionController::class);
    Route::resource('estilos', EstiloController::class);
    Route::resource('cervezas', CervezaController::class);
});
