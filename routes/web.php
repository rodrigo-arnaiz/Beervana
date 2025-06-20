<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoFermentacionController;
use App\Http\Controllers\EstiloController;
use App\Http\Controllers\CervezaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\AdminMiddleware;

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

/* Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
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
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas por autenticación
|--------------------------------------------------------------------------
|
| Este bloque puede reactivarse cuando se quiera requerir login nuevamente.
|
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('marcas', MarcaController::class);
    Route::resource('tipo-fermentaciones', TipoFermentacionController::class)
        ->parameters(['tipo-fermentaciones' => 'tipoFermentacion']);
    Route::resource('estilos', EstiloController::class);
    Route::resource('cervezas', CervezaController::class)->parameters([
        'cervezas' => 'cerveza',
    ]);
});
