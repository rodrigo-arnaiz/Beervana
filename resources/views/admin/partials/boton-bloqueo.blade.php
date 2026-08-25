{{-- Bloquear o desbloquear una cuenta. Espera: $usuario.
     Solo se incluye cuando quien mira tiene permiso.

     El confirm no es adorno: bloquear le borra los tokens a la persona, así que
     si está comprando en ese momento se queda sin sesión de golpe. --}}
@if ($usuario->estaBloqueado())
    <form method="POST" action="{{ route('usuarios.desbloquear', $usuario) }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-success text-nowrap"
                title="Devolverle el acceso a {{ $usuario->name }}">
            <i class="fas fa-lock-open"></i> Desbloquear
        </button>
    </form>
@else
    <form method="POST" action="{{ route('usuarios.bloquear', $usuario) }}"
          onsubmit="return confirm('{{ $usuario->name }} no va a poder iniciar sesión ni pagar. Se le cierran las sesiones abiertas. ¿Bloquear?')">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-warning text-nowrap"
                title="Impedir que {{ $usuario->name }} inicie sesión">
            <i class="fas fa-lock"></i> Bloquear
        </button>
    </form>
@endif
