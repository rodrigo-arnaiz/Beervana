<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoFermentacionController;
use App\Http\Controllers\EstiloController;
use App\Http\Controllers\CervezaController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Rutas de autenticación
|--------------------------------------------------------------------------
|
| El registro esta desactivado temporalmente. Login permanece accesible desde la URL.
| Si se desea bloquear el acceso real, sobreescribir LoginController.
|
*/
Auth::routes(['register' => false]);

// Rutas de login (opcional, si se sobreescribe el controlador)
/*
use App\Http\Controllers\Auth\LoginController;
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
*/

/*
|--------------------------------------------------------------------------
| Ruta de raíz
|--------------------------------------------------------------------------
|
| Cuando se accede a la raíz del sitio, se redirige al dashboard sin requerir autenticación.
|
*/
Route::get('/', function () {
    return redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| Rutas públicas (temporalmente sin middleware 'auth')
|--------------------------------------------------------------------------
|
| Estas rutas normalmente estarían protegidas, pero se exponen para poder
| visualizar el frontend en entornos sin acceso a base de datos.
|
*/
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::resource('marcas', MarcaController::class);
Route::resource('tipo-fermentaciones', TipoFermentacionController::class);
Route::resource('estilos', EstiloController::class);
Route::resource('cervezas', CervezaController::class);

/*
|--------------------------------------------------------------------------
| Rutas protegidas por autenticación (comentadas temporalmente)
|--------------------------------------------------------------------------
|
| Este bloque puede reactivarse cuando se quiera requerir login nuevamente.
|
*/
/*
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('marcas', MarcaController::class);
    Route::resource('tipo-fermentaciones', TipoFermentacionController::class);
    Route::resource('estilos', EstiloController::class);
    Route::resource('cervezas', CervezaController::class);
});
*/


