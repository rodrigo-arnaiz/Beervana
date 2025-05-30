@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Nuevo Tipo de Fermentación</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('tipo-fermentaciones.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
        </div>
        <div class="mb-3">
            <label for="levadura" class="form-label">Levadura</label>
            <input type="text" name="levadura" class="form-control"
                value="{{ old('levadura', $tipoFermentacion->levadura ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="temperatura" class="form-label">Temperatura (°C)</label>
            <input type="text" name="temperatura" class="form-control"
                value="{{ old('temperatura', $tipoFermentacion->temperatura ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="tiempo" class="form-label">Tiempo típico</label>
            <input type="text" name="tiempo" class="form-control"
                value="{{ old('tiempo', $tipoFermentacion->tiempo ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"
                    placeholder="Notas, origen, características…">{{ old('descripcion') }}</textarea>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('tipo-fermentaciones.index') }}" class="btn btn-secondary"><i class="fas fa-ban"></i> Cancelar</a>
    </form>
</div>
@endsection
