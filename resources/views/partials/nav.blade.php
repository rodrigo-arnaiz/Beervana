<nav class="navbar navbar-expand-lg navbar-light bg-gradient-custom shadow-sm py-3" style="background-color: rgba(219, 174, 126, 0.5);">
    <div class="container-fluid position-relative">

        <!-- Botón menú lateral mobile -->
        <button class="btn btn-outline-dark d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Título centrado SOLO visible en mobile -->
        <div class="d-lg-none position-absolute top-50 start-50 translate-middle">
            <a href="{{ route('home') }}" class="text-dark text-decoration-none">
                <img src="{{ asset('assets/beervana_nav_logo.png') }}" alt="Beervana" style="height: 40px;">
            </a>
        </div>

        <!-- Logout -->
        <div class="ms-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button title="Cerrar sesión" type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</nav>
