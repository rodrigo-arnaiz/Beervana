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
                        <form action="{{ route('marcas.destroy', $marca) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar esta marca?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center my-3">
        {{ $marcas->links() }}
    </div>
@endsection
