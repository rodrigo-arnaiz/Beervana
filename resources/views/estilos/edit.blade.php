@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Editar Estilo</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('estilos.update', $estilo->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $estilo->nombre) }}" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion', $estilo->descripcion) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="tipo_fermentacion_id" class="form-label">Tipo de Fermentación</label>
            <select name="tipo_fermentacion_id" class="form-select" required>
                <option value="">-- Selecciona un tipo de fermentación --</option>
                @foreach($tipoFermentaciones as $tipo)
                    <option value="{{ $tipo->id }}" {{ old('tipo_fermentacion_id', $estilo->tipo_fermentacion_id) == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('estilos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
