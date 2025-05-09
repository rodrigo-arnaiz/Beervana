<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoFermentacionController;
use App\Http\Controllers\EstiloController;
use App\Http\Controllers\CervezaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;

// desactiva el registro si no lo necesitas
Auth::routes(['register' => false]);

// redirige a login si va a la raíz y no está autenticado
Route::get('/', function () {
    return redirect('/dashboard');
});




/* 
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
}); */

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('marcas', MarcaController::class);
    Route::resource('tipo-fermentaciones', TipoFermentacionController::class);
    Route::resource('estilos', EstiloController::class);
    Route::resource('cervezas', CervezaController::class);
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
