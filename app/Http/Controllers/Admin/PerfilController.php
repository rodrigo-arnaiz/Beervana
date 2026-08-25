<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Los datos propios de quien está logueado.
 *
 * Existe aparte de UsuarioController porque no requiere ser administrador: un
 * empleado no puede entrar a /usuarios pero sí tiene que poder corregir su
 * nombre o cambiarse la contraseña.
 */
class PerfilController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.perfil', ['usuario' => $request->user()]);
    }

    public function update(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            // La contraseña actual se pide solo si se está cambiando: sin eso,
            // una sesión abierta y olvidada en la máquina del mostrador alcanza
            // para quedarse con la cuenta.
            'password_actual' => 'required_with:password|nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            // En castellano y no vía lang/es porque la app corre con locale
            // 'en' y cambiarlo traduciría la validación de todo el panel.
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Ese email no tiene un formato válido.',
            'email.unique' => 'Ya hay una cuenta con ese email.',
            'password_actual.required_with' => 'Para cambiar la contraseña tenés que ingresar la actual.',
            'password.min' => 'La contraseña nueva tiene que tener al menos 8 caracteres.',
            'password.confirmed' => 'Las dos contraseñas nuevas no coinciden.',
        ]);

        if (filled($datos['password'] ?? null)) {
            if (! Hash::check($datos['password_actual'], $usuario->password)) {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation', 'password_actual']))
                    ->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
            }

            $usuario->password = $datos['password'];
        }

        $usuario->name = $datos['name'];
        $usuario->email = $datos['email'];
        $usuario->save();

        return back()->with('exito', 'Tus datos quedaron actualizados.');
    }
}
