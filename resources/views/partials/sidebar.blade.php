<!-- Sidebar fijo en escritorio -->

<nav id="sidebar" class="d-none d-lg-block sidebar bg-dark text-white">
    <div class="container-fluid">
<div class="sidebar-header d-flex justify-content-start align-items-center py-2" style="margin-top: 10px; margin-bottom: -8px;">
    <a href="{{ route('home') }}" class="text-decoration-none">
        <img src="{{ asset('assets/beervana_side_logo.png') }}" alt="Beervana" style="height: 28px;">
    </a>
</div>
    </div>  
    <hr>
    <div class="sidebar-body nav flex-column">
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
    </div>
</nav>


<!-- Sidebar tipo offcanvas en mobile -->
<div class="offcanvas offcanvas-start sidebar bg-dark text-white" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
        <a href="{{ route('home') }}" class="text-light text-decoration-none">
            <h5 class="mb-0">Beervana</h5>
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="nav flex-column">
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
        </nav>
    </div>
</div>
