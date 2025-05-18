
@extends('layouts.admin')

@section('content')
    <h2 class="mb-4">Listado de Cervezas</h2>

    <a href="{{ route('cervezas.create') }}" class="btn btn-primary mb-3">
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
                @foreach($marcas as $marca)
                    <option value="{{ $marca->id }}"
                        {{ request('marca_id') == $marca->id ? 'selected' : '' }}>
                        {{ $marca->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="estilo_id" class="form-label">Estilo</label>
            <select name="estilo_id" id="estilo_id" class="form-select">
                <option value="">— Todos —</option>
                @foreach($estilos as $estilo)
                    <option value="{{ $estilo->id }}"
                        {{ request('estilo_id') == $estilo->id ? 'selected' : '' }}>
                        {{ $estilo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary me-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('cervezas.index') }}" class="btn btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Estilo</th>
                <th>Graduación</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cervezas as $cerveza)
                <tr>
                    <td>{{ $cerveza->id }}</td>
                    <td>{{ $cerveza->nombre }}</td>
                    <td>{{ $cerveza->marca->nombre ?? '-' }}</td>
                    <td>{{ $cerveza->estilo->nombre ?? '-' }}</td>
                    <td>{{ $cerveza->graduacion }}%</td>
                    <td>${{ number_format($cerveza->precio, 2) }}</td>
                    <td>
                        <img src="{{ asset($cerveza->imagen) }}" alt="Imagen de {{ $cerveza->nombre }}" style="width: 60px; height: auto;">
                    </td>
                    <td>
                        <a href="{{ route('cervezas.edit', $cerveza) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('cervezas.destroy', $cerveza) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Estás seguro de eliminar esta cerveza?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $cervezas->links() }}
    </div>
@endsection
