@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1 class="mb-4">Estilos</h1>
        <a href="{{ route('estilos.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Nuevo Estilo
        </a>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form method="GET" action="{{ route('estilos.index') }}" class="row g-3 mb-4 align-items-end">
            <div class="col-md-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="{{ request('nombre') }}">
            </div>
            <div class="col-md-4">
                <label for="tipo_fermentacion_id" class="form-label">Tipo de Fermentación</label>
                <select name="tipo_fermentacion_id" id="tipo_fermentacion_id" class="form-select">
                    <option value="">— Todos —</option>
                    @foreach ($tipoFermentaciones as $tipo)
                        <option value="{{ $tipo->id }}"
                            {{ request('tipo_fermentacion_id') == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('estilos.index') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Tipo Fermentación</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estilos as $estilo)
                    <tr>
                        <td>{{ $estilo->id }}</td>
                        <td>{{ $estilo->nombre }}</td>
                        <td>{{ $estilo->tipoFermentacion->nombre ?? '' }}</td>
                        <td>{{ $estilo->descripcion }}</td>
                        <td>
                            <a href="{{ route('estilos.edit', $estilo->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            {{-- <form action="{{ route('estilos.destroy', $estilo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este estilo?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                        </form> --}}
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                data-bs-target="#modalEliminar" data-id="{{ $estilo->id }}"
                                data-nombre="{{ $estilo->nombre }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center my-3">
            {{ $estilos->links() }}
        </div>
    </div>
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">¿Eliminar estilo?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar el estilo <strong id="nombreEstilo"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
    const modalEliminar = document.getElementById('modalEliminar');
    modalEliminar.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');

        const nombreSpan = modalEliminar.querySelector('#nombreEstilo');
        const form = modalEliminar.querySelector('#formEliminar');

        // Mostrar el nombre en el modal
        nombreSpan.textContent = nombre;

        // Setear la acción del formulario
        form.action = `/estilos/${id}`;
    });
</script>
@endsection
