@extends('layouts.admin')

@section('content')
    <h2 class="mb-4">Listado de Marcas</h2>
    <a href="{{ route('marcas.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Nueva Marca
    </a>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($marcas as $marca)
                <tr>
                    <td>{{ $marca->id }}</td>
                    <td>{{ $marca->nombre }}</td>
                    <td>
                        <a href="{{ route('marcas.edit', $marca) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        {{-- <form action="{{ route('marcas.destroy', $marca) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar esta marca?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form> --}}
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#modalEliminar" data-id="{{ $marca->id }}"
                            data-nombre="{{ $marca->nombre }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center my-3">
        {{ $marcas->links() }}
    </div>
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">¿Eliminar marca?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar la marca <strong id="nombreMarca"></strong>?
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

            const nombreSpan = modalEliminar.querySelector('#nombreMarca');
            const form = modalEliminar.querySelector('#formEliminar');

            // Mostrar el nombre en el modal
            nombreSpan.textContent = nombre;

            // Setear la acción del formulario
            form.action = `/marcas/${id}`;
        });
    </script>
@endsection
