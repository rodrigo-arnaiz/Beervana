@php
    // Se muestra el rol junto al nombre porque en el mostrador se comparte la
    // máquina: conviene ver de un vistazo con qué cuenta se está cobrando, que
    // es la que queda guardada en la factura.
    $yo = auth()->user();
    $etiquetaRolSidebar = [
        'empleado' => 'Empleado',
        'admin' => 'Administrador',
    ][$yo?->rol] ?? '';
@endphp

<!-- Sidebar fijo en escritorio -->

<nav id="sidebar" class="d-none d-lg-flex flex-column sidebar bg-dark text-white">
    <div class="container-fluid">
<div class="sidebar-header d-flex justify-content-start align-items-center py-2" style="margin-top: 10px; margin-bottom: -8px;">
    <a href="{{ $yo?->paginaInicial() ?? route('login') }}" class="text-decoration-none">
        <img src="{{ asset('assets/beervana_side_logo.png') }}" alt="Beervana" style="height: 28px;">
    </a>
</div>
    </div>
    <hr>
    <div class="sidebar-body nav flex-column">
        {{-- El mostrador y los cobros propios los ve todo el personal. El resto
             del panel es solo para admins: mostrar links que llevan a un
             redirect es peor que no mostrarlos. --}}
        <li>
            <a class="nav-link" href="{{ route('mostrador.index') }}"><i class="fas fa-cash-register"></i> Mostrador</a>
        </li>
        <li>
            <a class="nav-link" href="{{ route('mis-cobros.index') }}"><i class="fas fa-receipt"></i> Mis cobros</a>
        </li>

        @if ($yo?->esAdmin())
        <hr class="my-2">
        <li>
            <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li>
            <a class="nav-link" href="{{ route('cervezas.index') }}"> <i class="fas fa-beer"></i> Cervezas</a>
        </li>

        <li>
            <a class="nav-link" href="{{ route('marcas.index') }}"><i class="fas fa-industry"></i> Marcas</a>
        </li>
        <li>
            <a class="nav-link" href="{{ route('tipo-fermentaciones.index') }}"><i class="fas fa-flask"></i>
                Fermentaciones</a>
        </li>
        <li>
            <a class="nav-link" href="{{ route('estilos.index') }}"><i class="fas fa-tags"></i> Estilos</a>
        </li>
        <li>
            <a class="nav-link" href="{{ route('usuarios.index') }}"><i class="fas fa-users"></i> Usuarios</a>
        </li>
        @endif
    </div>

    {{-- Pie: la cuenta con la que se está trabajando y la salida.
         mt-auto lo empuja abajo de todo; funciona porque el nav es d-lg-flex
         flex-column (con d-lg-block, Bootstrap pisa el display y no anda). --}}
    @include('partials.sidebar-cuenta', ['yo' => $yo, 'etiquetaRol' => $etiquetaRolSidebar])
</nav>


<!-- Sidebar tipo offcanvas en mobile -->
<div class="offcanvas offcanvas-start sidebar bg-dark text-white" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
        <a href="{{ $yo?->paginaInicial() ?? route('login') }}" class="text-light text-decoration-none">
            <h5 class="mb-0">Beervana</h5>
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <nav class="nav flex-column">
            <li>
                <a class="nav-link" href="{{ route('mostrador.index') }}"><i class="fas fa-cash-register"></i> Mostrador</a>
            </li>
            <li>
                <a class="nav-link" href="{{ route('mis-cobros.index') }}"><i class="fas fa-receipt"></i> Mis cobros</a>
            </li>

            @if ($yo?->esAdmin())
            <hr class="my-2">
            <li>
                <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li>
                <a class="nav-link" href="{{ route('cervezas.index') }}"> <i class="fas fa-beer"></i> Cervezas</a>
            </li>

            <li>
                <a class="nav-link" href="{{ route('marcas.index') }}"><i class="fas fa-industry"></i> Marcas</a>
            </li>
            <li>
                <a class="nav-link" href="{{ route('tipo-fermentaciones.index') }}"><i class="fas fa-flask"></i>
                    Fermentaciones</a>
            </li>
            <li>
                <a class="nav-link" href="{{ route('estilos.index') }}"><i class="fas fa-tags"></i> Estilos</a>
            </li>
            <li>
                <a class="nav-link" href="{{ route('usuarios.index') }}"><i class="fas fa-users"></i> Usuarios</a>
            </li>
            @endif
        </nav>

        @include('partials.sidebar-cuenta', ['yo' => $yo, 'etiquetaRol' => $etiquetaRolSidebar])
    </div>
</div>
