<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <!-- Botón para mobile -->
        <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Título centrado en mobile -->
        <div class="d-lg-none position-absolute top-50 start-50 translate-middle-x">
            <a href="{{ route('home') }}" class="text-dark text-decoration-none">
                <h5 class="mb-0">Beervana</h5>
            </a>
        </div>


        <!-- Logout a la derecha -->
        <div class="ms-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>
