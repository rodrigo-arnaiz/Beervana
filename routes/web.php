<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoFermentacionController;
use App\Http\Controllers\EstiloController;
use App\Http\Controllers\CervezaController;

//Crea todas las rutas siguiendo cierta convencion
Route::resource('marcas', MarcaController::class);
Route::resource('tipo-fermentaciones', TipoFermentacionController::class);
Route::resource('estilos', EstiloController::class);
Route::resource('cervezas', CervezaController::class);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->name('home');
