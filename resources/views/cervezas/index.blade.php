
@extends('layouts.admin')

@section('content')
    <h2 class="mb-4">Listado de Cervezas</h2>

    <a href="{{ route('cervezas.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Nueva Cerveza
    </a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Estilo</th>
                <th>Graduación</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cervezas as $cerveza)
                <tr>
                    <td>{{ $cerveza->nombre }}</td>
                    <td>{{ $cerveza->marca->nombre ?? '-' }}</td>
                    <td>{{ $cerveza->estilo->nombre ?? '-' }}</td>
                    <td>{{ $cerveza->graduacion }}%</td>
                    <td>${{ number_format($cerveza->precio, 2) }}</td>
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
@endsection
