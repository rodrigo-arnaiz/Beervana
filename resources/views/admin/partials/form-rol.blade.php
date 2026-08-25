{{-- Selector de rol para una cuenta de personal. Espera: $usuario.
     Solo se incluye cuando quien mira tiene permiso para cambiarlo.

     Las opciones son ROLES_PERSONAL y no ROLES: "cliente" no está porque
     degradar a alguien a cliente lo sacaría de este listado sin poder volver
     (los clientes no se promueven). Para dar de baja a alguien del personal
     están bloquear y eliminar, que dicen lo que hacen. --}}
<form method="POST" action="{{ route('usuarios.rol', $usuario) }}" class="d-flex gap-1">
    @csrf
    @method('PATCH')

    <select name="rol" class="form-select form-select-sm" aria-label="Rol de {{ $usuario->name }}">
        @foreach (\App\Models\User::ROLES_PERSONAL as $rol)
            <option value="{{ $rol }}" @selected($rol === $usuario->rol)>{{ ucfirst($rol) }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-sm btn-outline-dark text-nowrap"
            onclick="return confirm('¿Cambiar el rol de {{ $usuario->name }}?')">
        Guardar
    </button>
</form>
