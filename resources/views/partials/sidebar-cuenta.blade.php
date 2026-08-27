{{-- Pie del sidebar: quién está trabajando y cómo salir.
     Espera: $yo (usuario actual), $etiquetaRol.

     Está en un partial porque el sidebar existe dos veces (escritorio y
     offcanvas mobile) y son copias literales: cuando el bloque vivía duplicado,
     los cambios se aplicaban a una sola de las dos y la versión mobile quedaba
     atrasada sin que se notara. --}}
<div class="sidebar-cuenta mt-auto">
    <a href="{{ route('perfil.edit') }}" class="sidebar-cuenta-usuario" title="Ver y editar mis datos">
        <i class="fas fa-user-circle fa-lg"></i>
        <span class="sidebar-cuenta-datos">
            <span class="d-block text-truncate">{{ $yo?->name }}</span>
            <small class="d-block sidebar-cuenta-rol">
                {{ $etiquetaRol }}
                @if ($yo?->esSuperAdmin())
                    · Super admin
                @endif
            </small>
        </span>
    </a>

    <form method="POST" action="{{ route('logout') }}" class="mt-2">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-light w-100">
            <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
        </button>
    </form>
</div>
