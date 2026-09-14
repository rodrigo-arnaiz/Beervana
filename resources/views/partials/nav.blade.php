<nav class="navbar navbar-expand-lg navbar-light bg-gradient-custom shadow-sm py-3" style="background-color: rgba(219, 174, 126, 0.5);">
    <div class="container-fluid position-relative">

        <!-- Botón menú lateral mobile -->
        <button class="btn btn-outline-dark d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Título centrado SOLO visible en mobile -->
        <div class="d-lg-none position-absolute top-50 start-50 translate-middle">
            {{-- paginaInicial y no route('home'): /home es solo para admins, y
                 al empleado lo mandaba a rebotar antes de llegar a algún lado. --}}
            <a href="{{ auth()->user()?->paginaInicial() ?? route('login') }}" class="text-dark text-decoration-none">
                <img src="{{ asset('assets/beervana_nav_logo.png') }}" alt="Beervana" style="height: 40px;">
            </a>
        </div>

        {{-- La cuenta y el logout viven al pie del sidebar (partials/sidebar-cuenta).
             Acá no se repiten: dos botones de cerrar sesión en la misma pantalla
             es una decisión de más para algo que no tiene alternativas. --}}
    </div>
</nav>
