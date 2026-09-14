<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();

        // El bloqueo se revisa en cada request y no solo al entrar: si se
        // bloquea a alguien que ya está adentro, tiene que quedar afuera en el
        // click siguiente y no cuando se le venza la sesión.
        if ($usuario->estaBloqueado()) {
            return $this->afuera($request, 'Tu cuenta está bloqueada.');
        }

        if ($usuario->esAdmin()) {
            return $next($request);
        }

        // Es del personal pero no admin: tiene adónde ir sin cerrarle la sesión
        if ($usuario->esPersonal()) {
            return redirect($usuario->paginaInicial())
                ->with('error', 'Esa sección es solo para administradores.');
        }

        // Un cliente no tiene ninguna pantalla del panel para ver
        return $this->afuera($request, 'Acceso denegado.');
    }

    /* Cierra la sesión antes de mandar a /login */
    private function afuera(Request $request, string $motivo): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', $motivo);
    }
}
