@extends('layouts.admin')

@section('title', 'Usuarios')

@php
    $etiquetaRol = [
        'cliente' => ['texto' => 'Cliente', 'clase' => 'bg-secondary'],
        'empleado' => ['texto' => 'Empleado', 'clase' => 'bg-info text-dark'],
        'admin' => ['texto' => 'Administrador', 'clase' => 'bg-dark'],
    ];
    $yo = auth()->user();
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
        <div>
            <h1 class="h3 mb-1">Usuarios</h1>
            <p class="text-muted mb-0">Clientes y personal con acceso al panel.</p>
        </div>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modal-crear">
            <i class="fas fa-user-plus me-1"></i> Agregar usuario
        </button>
    </div>

    @if (session('exito'))
        <div class="alert alert-success mt-3">{{ session('exito') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 align-items-end mb-4 mt-2">
        <div class="col-md-4">
            <label for="q" class="form-label">Buscar por nombre o email</label>
            <input type="text" name="q" id="q" value="{{ $buscado }}" class="form-control"
                   placeholder="matías / matias@..." autocomplete="off">
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100" type="submit">Buscar</button>
        </div>
        @if ($buscado !== '')
            <div class="col-md-2">
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
            </div>
        @endif
    </form>

    {{-- ── Personal: los que entran al panel ──────────────────────────────── --}}
    <h2 class="h5">
        Personal <span class="badge bg-dark">{{ $personal->count() }}</span>
    </h2>
    <p class="text-muted small">
        Empleados y administradores. Un empleado solo ve el mostrador y sus cobros;
        un administrador además maneja el catálogo y los roles.
    </p>

    @if ($personal->isEmpty())
        <p class="text-muted fst-italic">No hay resultados.</p>
    @else
        <div class="table-responsive mb-5">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol actual</th>
                        <th style="width: 230px;">Cambiar rol</th>
                        <th style="width: 190px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personal as $usuario)
                        @php
                            $motivoRol = $yo->motivoNoPuedeCambiarRol($usuario);
                            $motivoEditar = $yo->motivoNoPuedeEditar($usuario);
                            $motivoBloquear = $yo->motivoNoPuedeBloquear($usuario);
                            $motivoEliminar = $yo->motivoNoPuedeEliminar($usuario);
                        @endphp
                        <tr @class(['table-warning' => $usuario->estaBloqueado()])>
                            <td>
                                {{ $usuario->name }}
                                @if ($usuario->esSuperAdmin())
                                    <span class="badge bg-warning text-dark ms-1">Super admin</span>
                                @endif
                                @if ($usuario->id === $yo->id)
                                    <span class="badge bg-light text-dark ms-1">Vos</span>
                                @endif
                                @if ($usuario->estaBloqueado())
                                    <span class="badge bg-danger ms-1"
                                          title="Bloqueado el {{ $usuario->bloqueado_en->format('d/m/Y H:i') }}">
                                        Bloqueado
                                    </span>
                                @endif
                                @if ($usuario->cobros_count)
                                    <div class="text-muted small">{{ $usuario->cobros_count }} cobro(s) registrado(s)</div>
                                @endif
                            </td>
                            <td><small>{{ $usuario->email }}</small></td>
                            <td>
                                <span class="badge {{ $etiquetaRol[$usuario->rol]['clase'] }}">
                                    {{ $etiquetaRol[$usuario->rol]['texto'] }}
                                </span>
                            </td>
                            <td>
                                @if ($motivoRol)
                                    {{-- El selector no aparece siquiera: el backend igual lo
                                         rechaza, pero ofrecer algo que va a fallar confunde. --}}
                                    <small class="text-muted fst-italic">{{ $motivoRol }}</small>
                                @else
                                    @include('admin.partials.form-rol', ['usuario' => $usuario])
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @unless ($motivoEditar)
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal" data-bs-target="#modal-editar-{{ $usuario->id }}"
                                                title="Editar datos de {{ $usuario->name }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    @endunless

                                    @unless ($motivoBloquear)
                                        @include('admin.partials.boton-bloqueo', ['usuario' => $usuario])
                                    @endunless

                                    @if ($motivoEliminar)
                                        {{-- Se muestra apagado y con el motivo al pasar el mouse:
                                             si el botón desapareciera quedaría la duda de si existe. --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                                title="{{ $motivoEliminar }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}"
                                              onsubmit="return confirm('Se va a eliminar la cuenta de {{ $usuario->name }}. Esto no se puede deshacer. ¿Seguir?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Eliminar cuenta">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if ($motivoEditar && $motivoBloquear)
                                    <small class="text-muted fst-italic d-block mt-1">{{ $motivoEditar }}</small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Clientes: sin acceso al panel ──────────────────────────────────── --}}
    <h2 class="h5">
        Clientes <span class="badge bg-secondary">{{ $clientes->total() }}</span>
    </h2>
    <p class="text-muted small">
        Se registran solos desde la tienda y no cambian de rol: para que alguien atienda el
        local hay que crearle una cuenta de personal. Tampoco se eliminan, porque sus pedidos
        y facturas son ventas reales; si hay que cortarles el acceso, se bloquean.
    </p>

    @if ($clientes->isEmpty())
        <p class="text-muted fst-italic">No hay resultados.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Registrado</th>
                        <th>Estado</th>
                        <th style="width: 170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $usuario)
                        @php $motivoBloquear = $yo->motivoNoPuedeBloquear($usuario); @endphp
                        <tr @class(['table-warning' => $usuario->estaBloqueado()])>
                            <td>{{ $usuario->name }}</td>
                            <td><small>{{ $usuario->email }}</small></td>
                            <td><small>{{ $usuario->created_at?->format('d/m/Y') }}</small></td>
                            <td>
                                @if ($usuario->estaBloqueado())
                                    <span class="badge bg-danger">Bloqueado</span>
                                    <div class="text-muted small">
                                        desde el {{ $usuario->bloqueado_en->format('d/m/Y H:i') }}
                                    </div>
                                @else
                                    <span class="badge bg-success">Activo</span>
                                @endif
                            </td>
                            <td>
                                @if ($motivoBloquear)
                                    <small class="text-muted fst-italic">{{ $motivoBloquear }}</small>
                                @else
                                    @include('admin.partials.boton-bloqueo', ['usuario' => $usuario])
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $clientes->links() }}
    @endif
</div>

{{-- ── Modales ────────────────────────────────────────────────────────────
     Van fuera de las tablas: un <div> suelto dentro de un <tbody> es HTML
     inválido y el navegador lo saca de la tabla por su cuenta, con lo que el
     modal termina en cualquier lado. --}}
@include('admin.partials.modal-usuario', ['modo' => 'crear', 'usuario' => null])

@foreach ($personal as $usuario)
    @unless ($yo->motivoNoPuedeEditar($usuario))
        @include('admin.partials.modal-usuario', ['modo' => 'editar', 'usuario' => $usuario])
    @endunless
@endforeach

<script>
    // Si la validación falló, la respuesta fue un redirect y el modal quedó
    // cerrado con los errores adentro. Se reabre el que corresponde para que la
    // persona vea qué campo corregir en vez de una pantalla sin explicación.
    document.querySelectorAll('.modal[data-abrir="1"]').forEach(function (el) {
        new bootstrap.Modal(el).show();
    });
</script>
@endsection
