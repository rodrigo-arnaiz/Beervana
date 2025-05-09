<!-- resources/views/partials/sidebar.blade.php -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h3>Beervana</h3>
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
                            <a href="{{ route('cervezas.index') }}"><i class="fas fa-file"></i> Listado</a>
                        </li>
                        <li>
                        <a href="{{ route('marcas.index') }}"><i class="fas fa-industry"></i> Marcas</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-file"></i> Page 3</a>
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
