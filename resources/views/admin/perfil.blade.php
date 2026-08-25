@extends('layouts.admin')

@section('title', 'Mi perfil')

@php
    $etiquetaRol = [
        'empleado' => ['texto' => 'Empleado', 'clase' => 'bg-info text-dark'],
        'admin' => ['texto' => 'Administrador', 'clase' => 'bg-dark'],
    ];
@endphp

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-1">Mi perfil</h1>
    <p class="text-muted">Tus datos de acceso al panel.</p>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="badge {{ $etiquetaRol[$usuario->rol]['clase'] }}">
                            {{ $etiquetaRol[$usuario->rol]['texto'] }}
                        </span>
                        @if ($usuario->esSuperAdmin())
                            <span class="badge bg-warning text-dark">Super admin</span>
                        @endif
                        {{-- El rol no se edita acá: no es un dato personal sino un
                             permiso, y nadie se lo asigna a sí mismo. --}}
                        <small class="text-muted fst-italic">Tu rol lo asigna un administrador.</small>
                    </div>

                    <form method="POST" action="{{ route('perfil.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" required value="{{ old('name', $usuario->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" required value="{{ old('email', $usuario->email) }}">
                            <div class="form-text">Es el usuario con el que iniciás sesión.</div>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr>

                        <h2 class="h6 mt-4">Cambiar contraseña</h2>
                        <p class="text-muted small">
                            Dejá los tres campos vacíos si no querés cambiarla.
                        </p>

                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control @error('password_actual') is-invalid @enderror"
                                   id="password_actual" name="password_actual" autocomplete="current-password">
                            <div class="form-text">
                                Se pide para que una sesión olvidada abierta en el mostrador no
                                alcance para quedarse con tu cuenta.
                            </div>
                            @error('password_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña nueva</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" autocomplete="new-password" minlength="8">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Repetir contraseña nueva</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" autocomplete="new-password" minlength="8">
                        </div>

                        <button type="submit" class="btn btn-dark">Guardar cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
