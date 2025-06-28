@extends('layouts.admin')

@section('content')
    <h2 class="titulo-panel">Listado de Cervezas</h2>

    <a href="{{ route('cervezas.create') }}" class="btn btn-detail-custom mb-3">
        <i class="fas fa-plus"></i> Nueva Cerveza
    </a>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    {{-- Filtros --}}
    <form method="GET" action="{{ route('cervezas.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="marca_id" class="form-label">Marca</label>
            <select name="marca_id" id="marca_id" class="form-select">
                <option value="">— Todas —</option>
                @foreach ($marcas as $marca)
                    <option value="{{ $marca->id }}" {{ request('marca_id') == $marca->id ? 'selected' : '' }}>
                        {{ $marca->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="estilo_id" class="form-label">Estilo</label>
            <select name="estilo_id" id="estilo_id" class="form-select">
                <option value="">— Todos —</option>
                @foreach ($estilos as $estilo)
                    <option value="{{ $estilo->id }}" {{ request('estilo_id') == $estilo->id ? 'selected' : '' }}>
                        {{ $estilo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-detail-custom me-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('cervezas.index') }}" class="btn btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped table-custom align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Marca</th>
                    <th>Estilo</th>
                    <th>Graduación</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cervezas as $cerveza)
                    <tr>
                        <td class="align-middle">{{ $cerveza->id }}</td>
                        <td class="align-middle">{{ $cerveza->nombre }}</td>
                        <td class="align-middle">{{ $cerveza->marca->nombre ?? '-' }}</td>
                        <td class="align-middle">{{ $cerveza->estilo->nombre ?? '-' }}</td>
                        <td class="align-middle">{{ $cerveza->graduacion }}%</td>
                        <td class="align-middle">${{ number_format($cerveza->precio, 2) }}</td>
                        <td class="align-middle">{{ $cerveza->stock }}</td>
                        <td>
                            <img src="{{ $cerveza->imagen }}" style="width: 100px; height: auto;">
                        </td>
                        <td class="align-middle">
                            <a href="{{ route('cervezas.edit', $cerveza) }}" class="btn btn-sm btn-edit-custom">
                                <i class="fas fa-edit"></i>
                            </a>

                            <button type="button" class="btn btn-sm btn-delete-custom" data-bs-toggle="modal"
                                data-bs-target="#modalEliminar" data-id="{{ $cerveza->id }}"
                                data-nombre="{{ $cerveza->nombre }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-detail-custom" data-bs-toggle="modal"
                                data-bs-target="#detalleModal{{ $cerveza->id }}">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @foreach ($cervezas as $cerveza)
        @includeIf('cervezas.partials.modal_detalle', ['cerveza' => $cerveza])
    @endforeach
    <div class="d-flex justify-content-center">
        {{ $cervezas->links() }}
    </div>

    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger-custom text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">¿Eliminar cerveza?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar la cerveza <strong id="nombreCerveza"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-edit-custom" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete-custom">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const modalEliminar = document.getElementById('modalEliminar');
        modalEliminar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');

            const nombreSpan = modalEliminar.querySelector('#nombreCerveza');
            const form = modalEliminar.querySelector('#formEliminar');

            // Mostrar el nombre en el modal
            nombreSpan.textContent = nombre;

            // Setear la acción del formulario
            form.action = `/cervezas/${id}`;
        });
    </script>
@endsection
