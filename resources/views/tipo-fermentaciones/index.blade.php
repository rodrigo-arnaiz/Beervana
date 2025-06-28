@extends('layouts.admin')

@section('content')
        <h2 class="titulo-panel">Listado de Fermentaciones</h2>
        <a href="{{ route('tipo-fermentaciones.create') }}" class="btn btn-detail-custom mb-3">
            <i class="fas fa-plus"></i> Nuevo Tipo de Fermentación
        </a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-custom align-middle text-center">
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
                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $tipo->nombre }}">{{ $tipo->nombre }}</td>
                            <td style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $tipo->levadura }}">{{ $tipo->levadura }}</td>
                            <td>{{ $tipo->temperatura }}</td>
                            <td>{{ $tipo->tiempo }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $tipo->descripcion }}">{{ $tipo->descripcion }}</td>
                            <td>
                                <a href="{{ route('tipo-fermentaciones.edit', $tipo->id) }}" class="btn btn-sm btn-edit-custom" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-delete-custom" data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar" data-id="{{ $tipo->id }}"
                                    data-nombre="{{ $tipo->nombre }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-detail-custom" data-bs-toggle="modal"
                                    data-bs-target="#detalleModal{{ $tipo->id }}" title="Ver Detalle">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>
                            <!-- Modal Detalle -->
                            <div class="modal fade" id="detalleModal{{ $tipo->id }}" tabindex="-1"
                                aria-labelledby="detalleLabel{{ $tipo->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content detalle-modal shadow-lg">
                                        <div class="modal-header bg-white border-bottom-0">
                                            <h4 class="modal-title" id="detalleLabel{{ $tipo->id }}">
                                                <strong>Detalle {{ $tipo->nombre }}</strong>
                                            </h4>
                                        </div>
                                        <div class="modal-body bg-white p-4">
                                            <div class="flex flex-col md:flex-row gap-6">
                                                <div class="beer-details">
                                                    <div class="space-y-3">
                                                        <p><strong>Nombre:</strong> {{ $tipo->nombre }}</p>
                                                        <p><strong>Levadura:</strong> {{ $tipo->levadura }}</p>
                                                        <p><strong>Temperatura:</strong> {{ $tipo->temperatura }}</p>
                                                        <p><strong>Tiempo:</strong> {{ $tipo->tiempo }}</p>
                                                        <p><strong>Descripción:</strong> {{ $tipo->descripcion }}</p>
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

        <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger-custom text-white">
                        <h5 class="modal-title" id="modalEliminarLabel">¿Eliminar tipo de fermentación?</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar el tipo de fermentación <strong id="nombreFermentacion"></strong>?
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

    {{-- Script para el modal --}}
    <script>
        const modalEliminar = document.getElementById('modalEliminar');
        modalEliminar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');

            modalEliminar.querySelector('#nombreFermentacion').textContent = nombre;
            modalEliminar.querySelector('#formEliminar').action = `/tipo-fermentaciones/${id}`;
        });
    </script>
@endsection
