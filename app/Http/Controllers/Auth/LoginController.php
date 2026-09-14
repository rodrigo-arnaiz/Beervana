<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        // Una cuenta bloqueada no entra al panel aunque sea personal: el
        // bloqueo tiene que valer en las dos puertas, no solo en la tienda.
        if ($user && $user->estaBloqueado()) {
            Auth::logout();
            Session::flash('error', 'Tu cuenta está bloqueada. Contactate con un administrador.');
            return '/login';
        }

        if ($user && $user->esPersonal()) {
            return $user->paginaInicial();
        }

        Auth::logout();
        Session::flash('error', 'Acceso denegado. No tenés permisos para entrar al panel.');
        return '/login';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
