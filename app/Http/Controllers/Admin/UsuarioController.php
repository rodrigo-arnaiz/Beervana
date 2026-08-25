<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Administración de usuarios.
 *
 * Se listan por separado los clientes (que no entran al panel) del personal
 * (empleados y administradores), porque son dos poblaciones distintas con
 * operaciones distintas: al personal se lo crea, edita y elimina; a los
 * clientes solo se los bloquea.
 *
 * Quién puede hacer qué vive en el modelo User, no acá: la vista necesita las
 * mismas respuestas para decidir qué botones mostrar, y con la regla en un solo
 * lugar no hay forma de que pantalla y backend opinen distinto.
 */
class UsuarioController extends Controller
{
    /**
     * Mensajes en castellano para los campos de estas pantallas.
     *
     * Van acá y no en lang/es porque la app corre con locale 'en': cambiarlo
     * traduciría de golpe la validación de todo el panel, que hoy está en
     * inglés. Esto alcanza para que los modales no mezclen los dos idiomas.
     */
    private const MENSAJES = [
        'name.required' => 'El nombre es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'Ese email no tiene un formato válido.',
        'email.unique' => 'Ya hay una cuenta con ese email.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
        'password.confirmed' => 'Las dos contraseñas no coinciden.',
        'rol.required' => 'Elegí un rol.',
        'rol.in' => 'Ese rol no se puede asignar desde acá.',
    ];

    public function index(Request $request)
    {
        $buscado = trim((string) $request->query('q'));

        $filtrar = fn ($query) => $buscado === ''
            ? $query
            : $query->where(function ($q) use ($buscado) {
                $q->where('name', 'ilike', "%{$buscado}%")
                  ->orWhere('email', 'ilike', "%{$buscado}%");
            });

        return view('admin.usuarios', [
            'buscado' => $buscado,
            'personal' => $filtrar(
                User::whereIn('rol', User::ROLES_PERSONAL)->withCount('cobros')
            )->orderByDesc('super_admin')->orderBy('rol')->orderBy('name')->get(),
            'clientes' => $filtrar(
                User::where('rol', User::ROL_CLIENTE)
            )->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    /**
     * Alta de una cuenta de personal.
     *
     * El rol se limita a ROLES_PERSONAL: crear un "cliente" desde el panel no
     * tiene sentido porque los clientes se registran solos en la tienda.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol' => ['required', Rule::in(User::ROLES_PERSONAL)],
        ], self::MENSAJES);

        $usuario = User::create($datos);

        return back()->with('exito', "Se creó la cuenta de {$usuario->name} como {$usuario->rol}.");
    }

    /**
     * Cambia nombre, email y (opcionalmente) contraseña.
     *
     * El rol no se toca acá: tiene su propia acción porque las reglas de quién
     * puede cambiarlo son distintas de las de editar los datos.
     */
    public function update(Request $request, User $usuario)
    {
        $quienEdita = $request->user();

        if (! $quienEdita->puedeEditarA($usuario)) {
            return back()->with('error', $quienEdita->motivoNoPuedeEditar($usuario));
        }

        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            // nullable: dejar el campo vacío significa "no la cambies", que es
            // lo habitual cuando solo se corrige un nombre mal escrito.
            'password' => 'nullable|string|min:8|confirmed',
        ], self::MENSAJES);

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return back()->with('exito', "Se actualizaron los datos de {$usuario->name}.");
    }

    /** Cambia el rol de un usuario, si quien lo pide tiene permiso. */
    public function cambiarRol(Request $request, User $usuario)
    {
        $datos = $request->validate([
            'rol' => ['required', Rule::in(User::ROLES_PERSONAL)],
        ]);

        $quienCambia = $request->user();

        if (! $quienCambia->puedeCambiarRolDe($usuario)) {
            return back()->with(
                'error',
                $quienCambia->motivoNoPuedeCambiarRol($usuario) ?? 'No tenés permiso para cambiar ese rol.'
            );
        }

        if ($usuario->rol === $datos['rol']) {
            return back()->with('error', "{$usuario->name} ya tiene el rol {$datos['rol']}.");
        }

        $anterior = $usuario->rol;
        $usuario->update(['rol' => $datos['rol']]);

        return back()->with('exito', "{$usuario->name} pasó de {$anterior} a {$datos['rol']}.");
    }

    /** Baja lógica: corta el acceso sin perder el historial de compras. */
    public function bloquear(Request $request, User $usuario)
    {
        $quienBloquea = $request->user();

        if (! $quienBloquea->puedeBloquearA($usuario)) {
            return back()->with('error', $quienBloquea->motivoNoPuedeBloquear($usuario));
        }

        if ($usuario->estaBloqueado()) {
            return back()->with('error', "{$usuario->name} ya estaba bloqueado.");
        }

        $usuario->bloquear();

        return back()->with('exito', "Se bloqueó la cuenta de {$usuario->name}. Ya no puede iniciar sesión.");
    }

    public function desbloquear(Request $request, User $usuario)
    {
        $quienDesbloquea = $request->user();

        // Misma autoridad que para bloquear: quien puede cortar el acceso puede
        // devolverlo. Que uno de los dos fuera más fácil que el otro sería raro.
        if (! $quienDesbloquea->puedeBloquearA($usuario)) {
            return back()->with('error', $quienDesbloquea->motivoNoPuedeBloquear($usuario));
        }

        if (! $usuario->estaBloqueado()) {
            return back()->with('error', "{$usuario->name} no está bloqueado.");
        }

        $usuario->desbloquear();

        return back()->with('exito', "Se desbloqueó la cuenta de {$usuario->name}.");
    }

    /** Borrado real. Solo el super admin y solo sobre empleados sin historial. */
    public function destroy(Request $request, User $usuario)
    {
        $quienElimina = $request->user();

        if (! $quienElimina->puedeEliminarA($usuario)) {
            return back()->with('error', $quienElimina->motivoNoPuedeEliminar($usuario));
        }

        $nombre = $usuario->name;
        $usuario->tokens()->delete();
        $usuario->delete();

        return back()->with('exito', "Se eliminó la cuenta de {$nombre}.");
    }
}
