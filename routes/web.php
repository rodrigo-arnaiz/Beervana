<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoFermentacionController;
use App\Http\Controllers\EstiloController;
use App\Http\Controllers\CervezaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MostradorController;
use App\Http\Controllers\Admin\MisCobrosTotalesController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PerfilController;
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

// El mostrador lo usa quien atiende: empleados y admins. No exige ser admin
// porque cobrar no requiere poder editar el catálogo.
Route::middleware(['auth', 'personal'])->group(function () {
    Route::get('/mostrador', [MostradorController::class, 'index'])->name('mostrador.index');
    Route::post('/mostrador/pedidos/{id}/cobrar', [MostradorController::class, 'cobrar'])->name('mostrador.cobrar');
    Route::get('/mis-cobros', [MisCobrosTotalesController::class, 'index'])->name('mis-cobros.index');

    // Los datos propios: no exige ser admin porque un empleado tiene que poder
    // cambiarse la contraseña sin pedirle permiso a nadie.
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Manejar roles es cosa de administradores: va en el grupo 'admin' y no
    // en 'personal', porque un empleado cobra pero no reparte permisos.
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{usuario}/rol', [UsuarioController::class, 'cambiarRol'])->name('usuarios.rol');
    Route::post('/usuarios/{usuario}/bloquear', [UsuarioController::class, 'bloquear'])->name('usuarios.bloquear');
    Route::post('/usuarios/{usuario}/desbloquear', [UsuarioController::class, 'desbloquear'])->name('usuarios.desbloquear');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('marcas', MarcaController::class);
    Route::resource('tipo-fermentaciones', TipoFermentacionController::class)
        ->parameters(['tipo-fermentaciones' => 'tipoFermentacion']);
    Route::resource('estilos', EstiloController::class);
    Route::resource('cervezas', CervezaController::class)->parameters([
        'cervezas' => 'cerveza',
    ]);
});
