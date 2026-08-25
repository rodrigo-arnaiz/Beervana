<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Corta el paso a las cuentas bloqueadas.
 *
 * Al bloquear ya se borran los tokens, tambien toma accion inmediata es decir la persona queda afuera en el momento (con sesion activa). 
 * Aunque se emita un token por otra vía, o queda uno vivo por un error, el bloqueo sigue valiendo en cada request en vez de depender de
 * que el borrado de tokens haya salido bien.
 * 
 */
class BloqueadoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->estaBloqueado()) {
            $request->user()->tokens()->delete();

            return response()->json([
                'error' => 'Tu cuenta está bloqueada. Contactate con la tienda.',
            ], 403);
        }

        return $next($request);
    }
}
