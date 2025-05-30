@extends('layouts.admin')

@section('content')
    <h2>Editar Marca</h2>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form action="{{ route('marcas.update', $marca) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Marca</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $marca->nombre) }}" required>
            @error('nombre')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-primary">Actualizar</button> 
        <a href="{{ route('marcas.index') }}" class="btn btn-secondary">Cancelar </a>
    </form>
@endsection
