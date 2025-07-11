@extends('layouts.admin')

@section('content')
    <h2 class="titulo-panel">Nueva Cerveza</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Errores:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cervezas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('cervezas.partials.form')

        <button type="submit" class="btn btn-solid"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('cervezas.index') }}" class="btn btn-cremita"><i class="fas fa-ban"></i> Cancelar</a>
    </form>
@endsection
