@extends('layouts.admin')

@section('content')
    <h2 class="mb-4">Editar Cerveza</h2>

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

    <form action="{{ route('cervezas.update', $cerveza) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('cervezas.partials.edit_form', ['cerveza' => $cerveza])

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('cervezas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@endsection
