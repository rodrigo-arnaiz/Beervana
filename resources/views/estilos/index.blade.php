@extends('layouts.admin')

@section('content')
        <h2 class="titulo-panel">Estilos</h2>
        <a href="{{ route('estilos.create') }}" class="btn btn-detail-custom mb-3">
            <i class="fas fa-plus"></i> Nuevo Estilo
        </a>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filtros --}}
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
                        <option value="{{ $tipo->id }}" {{ request('tipo_fermentacion_id') == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-detail-custom me-2">Filtrar</button>
                <a href="{{ route('estilos.index') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-custom align-middle text-center">
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
                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $estilo->nombre }}">
                                {{ $estilo->nombre }}
                            </td>
                            <td style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $estilo->tipoFermentacion->nombre ?? '' }}">
                                {{ $estilo->tipoFermentacion->nombre ?? '' }}
                            </td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $estilo->descripcion }}">
                                {{ $estilo->descripcion }}
                            </td>
                            <td>
                                <a href="{{ route('estilos.edit', $estilo->id) }}" class="btn btn-sm btn-edit-custom" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button type="button" class="btn btn-sm btn-delete-custom" data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar" data-id="{{ $estilo->id }}"
                                    data-nombre="{{ $estilo->nombre }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-detail-custom" data-bs-toggle="modal"
                                    data-bs-target="#detalleModal{{ $estilo->id }}" title="Ver Detalle">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>
                            
                            <div class="modal fade" id="detalleModal{{ $estilo->id }}" tabindex="-1"
                                aria-labelledby="detalleLabel{{ $estilo->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content detalle-modal shadow-lg">
                                        <div class="modal-header bg-white border-bottom-0">
                                            <h4 class="modal-title" id="detalleLabel{{ $estilo->id }}">
                                                <strong>Detalle del Estilo</strong>
                                            </h4>
                                        </div>
                                        <div class="modal-body bg-white p-4">
                                            <div class="flex flex-col md:flex-row gap-6">
                                                <div class="beer-details">
                                                    <div class="space-y-3">
                                                        <p><strong>Nombre:</strong> {{ $estilo->nombre }}</p>
                                                        <p><strong>Tipo de Fermentación:</strong> {{ $estilo->tipoFermentacion->nombre ?? 'Sin definir' }}</p>
                                                        <p><strong>Descripción:</strong> {{ $estilo->descripcion }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-white border-top-0">
                                            <button type="button" class="close-button" data-bs-dismiss="modal">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center my-3">
            {{ $estilos->links() }}
        </div>


    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger-custom text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">¿Eliminar estilo?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar el estilo <strong id="nombreEstilo"></strong>?
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

            modalEliminar.querySelector('#nombreEstilo').textContent = nombre;
            modalEliminar.querySelector('#formEliminar').action = `/estilos/${id}`;
        });
    </script>
@endsection
