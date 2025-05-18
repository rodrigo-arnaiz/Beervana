@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-4">Tipos de Fermentación</h1>
    <a href="{{ route('tipo-fermentaciones.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Nuevo Tipo de Fermentación
    </a>
    @if(session('success'))
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
                @foreach($tiposFermentacion as $tipo)
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
                        <form action="{{ route('tipo-fermentaciones.destroy', $tipo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este tipo de fermentación?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
