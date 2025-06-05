@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1 class="mb-4">Tipos de Fermentación</h1>
        <a href="{{ route('tipo-fermentaciones.create') }}" class="btn btn-detail-custom mb-3">
            <i class="fas fa-plus"></i> Nuevo Tipo de Fermentación
        </a>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Levadura</th>
                    <th>Temperatura</th>
                    <th>Tiempo</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tiposFermentacion as $tipo)
                    <tr>
                        <td>{{ $tipo->id }}</td>
                        <td>{{ $tipo->nombre }}</td>
                        <td>{{ $tipo->levadura }}</td>
                        <td>{{ $tipo->temperatura }}</td>
                        <td>{{ $tipo->tiempo }}</td>
                        <td>{{ $tipo->descripcion }}</td>
                        <td>
                            <a href="{{ route('tipo-fermentaciones.edit', $tipo->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            {{-- <form action="{{ route('tipo-fermentaciones.destroy', $tipo->id) }}" method="POST"
                                class="d-inline"
                                onsubmit="return confirm('¿Estás seguro de eliminar este tipo de fermentación?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                            </form> --}}
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                data-bs-target="#modalEliminar" data-id="{{ $tipo->id }}"
                                data-nombre="{{ $tipo->nombre }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">¿Eliminar tipo de fermentacion?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar el tipo de fermentacion <strong id="nombreFermentacion"></strong>?
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
        modalEliminar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');

            const nombreSpan = modalEliminar.querySelector('#nombreFermentacion');
            const form = modalEliminar.querySelector('#formEliminar');

            // Mostrar el nombre en el modal
            nombreSpan.textContent = nombre;

            // Setear la acción del formulario
            form.action = `/tipo-fermentaciones/${id}`;
        });
    </script>
@endsection
