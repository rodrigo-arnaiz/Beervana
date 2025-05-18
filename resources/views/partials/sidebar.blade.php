<!-- resources/views/partials/sidebar.blade.php -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
    <a href="{{ route('home') }}" class="text-light text-decoration-none d-flex align-items-center">
    <h3>Beervana</h3>
</a>
    </div>
    <ul class="list-unstyled components">
        <li class="active">
            <a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li>
                    <a href="#pageSubmenu" name="Cerveza" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-beer"></i> Cervezas
                    </a>
                    <ul class="collapse list-unstyled" id="pageSubmenu">
                        <li>
                            <a href="{{ route('cervezas.index') }}"><i class="fas fa-file"></i> Listado de cervezas</a>
                        </li>
                        <li>
                            <a href="{{ route('marcas.index') }}"><i class="fas fa-industry"></i> Marcas</a>
                        </li>
                        <li>
                            <a href="{{ route('tipo-fermentaciones.index') }}"><i class="fas fa-flask"></i> Fermentaciones</a>
                        </li>
                        <li>
                            <a href="{{ route('estilos.index') }}"><i class="fas fa-tags"></i> Estilos</a>
                        </li>
                    </ul>
                </li>
        <li>
            <a href="#"><i class="fas fa-users"></i> Usuarios</a>
        </li>
        <li>
            <a href="#"><i class="fas fa-cog"></i> Configuración</a>
        </li>
    </ul>
</nav>
