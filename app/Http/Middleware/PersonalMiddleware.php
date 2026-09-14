<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/*
 * Deja pasar a empleados y administradores.
 *
 * Separado de AdminMiddleware ya que quien atiende el mostrador necesita
 * cobrar y ver pedidos, pero no tiene por qué poder editar el catálogo ni ver
 * la facturación del negocio.
 *
 */
class PersonalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();

        if ($usuario->esPersonal() && ! $usuario->estaBloqueado()) {
            return $next($request);
        }

        $motivo = $usuario->estaBloqueado()
            ? 'Tu cuenta está bloqueada.'
            : 'Acceso denegado.';

        // guard('web') explícito y no Auth::logout(): el guard por defecto puede
        // no ser el de sesión (Sanctum tiene el suyo, sin logout), y esto es el
        // panel, que siempre se autentica por sesión.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', $motivo);
    }
}
