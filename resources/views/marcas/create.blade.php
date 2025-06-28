@extends('layouts.admin')

@section('content')
    <h2 class="titulo-panel">Nueva Marca</h2>

    <form action="{{ route('marcas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Marca</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
            @error('nombre')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-solid"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('marcas.index') }}" class="btn btn-cremita"><i class="fas fa-ban"></i> Cancelar</a>
    </form>
@endsection
