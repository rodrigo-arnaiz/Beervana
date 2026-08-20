<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /** Compra. Se da de alta solo, desde el registro del frontend. */
    public const ROL_CLIENTE = 'cliente';

    /** Atiende y cobra en el mostrador, pero no administra el catálogo. */
    public const ROL_EMPLEADO = 'empleado';

    /** Todo lo anterior más el panel de administración. */
    public const ROL_ADMIN = 'admin';

    public const ROLES = [self::ROL_CLIENTE, self::ROL_EMPLEADO, self::ROL_ADMIN];

    /**
     * Los únicos roles que se reparten desde el panel.
     *
     * Un cliente no se promueve: para trabajar en el local hay que tener una
     * cuenta dada de alta por un administrador. Si alcanzara con promover a
     * cualquiera que se registró, el acceso al mostrador quedaría a un click de
     * distancia de una cuenta que nadie verificó.
     */
    public const ROLES_PERSONAL = [self::ROL_EMPLEADO, self::ROL_ADMIN];

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'super_admin',
        // bloqueado_en NO va acá a propósito: se escribe con forceFill desde
        // bloquear()/desbloquear(), que son las dos únicas puertas. Fuera del
        // fillable, ningún update() masivo puede desbloquear a alguien por
        // arrastrar un campo de más en el request.
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    /** Cobros que registró en el mostrador. */
    public function cobros()
    {
        return $this->hasMany(Factura::class, 'cobrado_por');
    }

    /** Pedidos que hizo como cliente. */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    // ── Rol ──────────────────────────────────────────────────────────────────

    /** Puede usar el mostrador: cobrar y ver los pedidos de todos. */
    public function esPersonal(): bool
    {
        return in_array($this->rol, self::ROLES_PERSONAL, true);
    }

    /** Ademas del mostrador, administra catalogo y ve la facturacion. */
    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esCliente(): bool
    {
        return $this->rol === self::ROL_CLIENTE;
    }

    /**
     * Adónde mandar a esta persona cuando entra al panel.
     *
     * Existe para que haya una sola respuesta: la usan el login, el middleware
     * de admin y el redirect de "ya estás logueado". Cuando cada uno tenía la
     * suya, un empleado terminaba en /home (que es solo para admins), de ahí lo
     * rebotaban a /login, y /login lo devolvía a /home: bucle infinito.
     */
    public function paginaInicial(): string
    {
        if ($this->esAdmin()) {
            return route('home');
        }

        // El empleado no tiene dashboard: su pantalla de trabajo es el mostrador
        return route('mostrador.index');
    }

    /** Su rol es inmutable: ningún administrador puede degradarlo. */
    public function esSuperAdmin(): bool
    {
        return (bool) $this->super_admin;
    }

    // ── Bloqueo ──────────────────────────────────────────────────────────────

    public function estaBloqueado(): bool
    {
        return $this->bloqueado_en !== null;
    }

    /**
     * Baja lógica: la cuenta queda, pero no puede volver a entrar.
     *
     * Los tokens se borran en el mismo movimiento. Sin eso el bloqueo no haría
     * nada hasta que la persona cerrara sesión por su cuenta: el token de
     * Sanctum ya emitido seguiría sirviendo para comprar y pagar.
     */
    public function bloquear(): void
    {
        $this->forceFill(['bloqueado_en' => now()])->save();
        $this->tokens()->delete();
    }

    public function desbloquear(): void
    {
        $this->forceFill(['bloqueado_en' => null])->save();
    }

    // ── Permisos ─────────────────────────────────────────────────────────────
    //
    // Cada permiso se define una sola vez, como el motivo por el que NO se
    // puede. El booleano se deriva de ahí. Con dos implementaciones paralelas
    // (una para decidir y otra para el cartelito) es cuestión de tiempo que
    // digan cosas distintas.

    public function puedeCambiarRolDe(self $otro): bool
    {
        return $this->motivoNoPuedeCambiarRol($otro) === null;
    }

    /**
     * Reglas:
     *   - Solo los administradores reparten roles. Un empleado cobra, no
     *     administra permisos.
     *   - Al super admin no lo toca nadie. Es lo que evita que alguien se quede
     *     con el sistema degradando al resto.
     *   - Nadie cambia su propio rol: degradarse es un tiro en el pie y el
     *     mensaje de error es más claro que la consecuencia.
     *   - Los clientes no entran en el reparto: el personal se da de alta.
     *
     * Un empleado promovido a administrador queda con los mismos permisos que
     * cualquier otro: puede cambiarle el rol a otros admins, salvo al super.
     */
    public function motivoNoPuedeCambiarRol(self $otro): ?string
    {
        if ($otro->esSuperAdmin()) {
            return 'El rol del super administrador es inmutable.';
        }

        if ($this->id === $otro->id) {
            return 'No podés cambiar tu propio rol.';
        }

        if (! $this->esAdmin()) {
            return 'Solo un administrador puede cambiar roles.';
        }

        if (! $otro->esPersonal()) {
            return 'Los clientes no cambian de rol. Para que alguien trabaje en el local hay que crearle una cuenta de personal.';
        }

        return null;
    }

    public function puedeEditarA(self $otro): bool
    {
        return $this->motivoNoPuedeEditar($otro) === null;
    }

    /**
     * Editar nombre, email y contraseña.
     *
     * Al super admin solo lo edita él mismo. Cambiarle el email o la clave es
     * quedarse con su cuenta, así que protegerle el rol y dejar abierto esto
     * sería una puerta de atrás a la misma habitación.
     */
    public function motivoNoPuedeEditar(self $otro): ?string
    {
        if ($this->id === $otro->id) {
            return null; // Los datos propios se editan desde el perfil
        }

        if (! $this->esAdmin()) {
            return 'Solo un administrador puede editar otras cuentas.';
        }

        if ($otro->esSuperAdmin()) {
            return 'Los datos del super administrador solo los cambia él mismo.';
        }

        return null;
    }

    public function puedeBloquearA(self $otro): bool
    {
        return $this->motivoNoPuedeBloquear($otro) === null;
    }

    public function motivoNoPuedeBloquear(self $otro): ?string
    {
        if ($otro->esSuperAdmin()) {
            return 'Al super administrador no se lo puede bloquear.';
        }

        if ($this->id === $otro->id) {
            return 'No podés bloquear tu propia cuenta.';
        }

        if (! $this->esAdmin()) {
            return 'Solo un administrador puede bloquear cuentas.';
        }

        return null;
    }

    public function puedeEliminarA(self $otro): bool
    {
        return $this->motivoNoPuedeEliminar($otro) === null;
    }

    /**
     * Borrado real de la fila. Es la operación más restringida del panel:
     *
     *   - Solo el super admin. Es irreversible y no deja rastro.
     *   - A los administradores no se los borra: primero se los degrada a
     *     empleado. Obliga a dos pasos deliberados en vez de uno.
     *   - A los clientes tampoco: se bloquean. Sus pedidos y facturas están con
     *     onDelete cascade, así que borrarlos se llevaría puesta la historia de
     *     ventas reales.
     *   - Tampoco a quien haya cobrado alguna vez: la factura guarda quién
     *     atendió, y ese dato es medio arqueo de caja. Para esos casos está el
     *     bloqueo, que corta el acceso sin borrar el historial.
     */
    public function motivoNoPuedeEliminar(self $otro): ?string
    {
        if ($this->id === $otro->id) {
            return 'No podés eliminar tu propia cuenta.';
        }

        if (! $this->esSuperAdmin()) {
            return 'Solo el super administrador puede eliminar cuentas.';
        }

        if ($otro->esAdmin()) {
            return 'A un administrador no se lo elimina. Degradalo a empleado primero.';
        }

        if ($otro->esCliente()) {
            return 'Los clientes no se eliminan porque tienen compras asociadas. Bloqueá la cuenta.';
        }

        if ($otro->cobros()->exists()) {
            return 'Tiene cobros registrados y las facturas guardan quién atendió. Bloqueá la cuenta en vez de eliminarla.';
        }

        if ($otro->pedidos()->exists()) {
            return 'Tiene pedidos asociados. Bloqueá la cuenta en vez de eliminarla.';
        }

        return null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'super_admin' => 'boolean',
            'bloqueado_en' => 'datetime',
        ];
    }
}
