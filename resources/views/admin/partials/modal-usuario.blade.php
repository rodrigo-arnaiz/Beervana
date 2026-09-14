{{-- Alta y edición de una cuenta de personal, en un modal para no perder de
     vista el listado. Espera:
       $modo     'crear' | 'editar'
       $usuario  la cuenta a editar (null cuando se crea)

     El mismo formulario sirve para los dos casos porque los campos son los
     mismos; lo único que cambia es si la contraseña es obligatoria y si el rol
     se elige acá (al crear) o desde el selector de la fila (al editar). --}}

@php
    $creando = $modo === 'crear';
    $id = $creando ? 'modal-crear' : 'modal-editar-'.$usuario->id;
    $marca = $creando ? 'crear' : 'editar-'.$usuario->id;
    // Sin guion bajo adelante para no mezclarse con los campos que usa el
    // framework (_token, _method), que viajan en el mismo formulario.
    $abierto = old('modal_abierto') === $marca;
@endphp

<div class="modal fade @if ($abierto) was-validated @endif" id="{{ $id }}" tabindex="-1"
     aria-labelledby="{{ $id }}-titulo" aria-hidden="true" data-abrir="{{ $abierto ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
              action="{{ $creando ? route('usuarios.store') : route('usuarios.update', $usuario) }}"
              class="modal-content">
            @csrf
            @unless ($creando)
                @method('PUT')
            @endunless

            {{-- Si la validación falla, la respuesta es un redirect y el modal
                 se cierra. Esta marca viaja en old() y le dice al script cuál
                 volver a abrir, para que los errores se vean donde se escribió. --}}
            <input type="hidden" name="modal_abierto" value="{{ $marca }}">

            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}-titulo">
                    {{ $creando ? 'Agregar usuario' : 'Editar '.$usuario->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @if ($creando)
                    <p class="text-muted small">
                        Las cuentas de personal se crean acá. Los clientes se registran solos
                        desde la tienda y no se promueven.
                    </p>
                @endif

                <div class="mb-3">
                    <label for="{{ $id }}-name" class="form-label">Nombre</label>
                    <input type="text" class="form-control @if ($abierto) @error('name') is-invalid @enderror @endif"
                           id="{{ $id }}-name" name="name" required
                           value="{{ $abierto ? old('name') : ($usuario->name ?? '') }}">
                    @if ($abierto)
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label for="{{ $id }}-email" class="form-label">Email</label>
                    <input type="email" class="form-control @if ($abierto) @error('email') is-invalid @enderror @endif"
                           id="{{ $id }}-email" name="email" required autocomplete="off"
                           value="{{ $abierto ? old('email') : ($usuario->email ?? '') }}">
                    @if ($abierto)
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>

                @if ($creando)
                    <div class="mb-3">
                        <label for="{{ $id }}-rol" class="form-label">Rol</label>
                        <select class="form-select @if ($abierto) @error('rol') is-invalid @enderror @endif"
                                id="{{ $id }}-rol" name="rol" required>
                            @foreach (\App\Models\User::ROLES_PERSONAL as $rol)
                                <option value="{{ $rol }}" @selected(old('rol') === $rol)>{{ ucfirst($rol) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Un empleado ve el mostrador y sus cobros. Un administrador, además, todo el panel.
                        </div>
                        @if ($abierto)
                            @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>
                @endif

                <div class="mb-3">
                    <label for="{{ $id }}-password" class="form-label">
                        Contraseña {{ $creando ? '' : '(dejar vacía para no cambiarla)' }}
                    </label>
                    <input type="password"
                           class="form-control @if ($abierto) @error('password') is-invalid @enderror @endif"
                           id="{{ $id }}-password" name="password" autocomplete="new-password"
                           @required($creando) minlength="8">
                    @if ($abierto)
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-0">
                    <label for="{{ $id }}-password2" class="form-label">Repetir contraseña</label>
                    <input type="password" class="form-control" id="{{ $id }}-password2"
                           name="password_confirmation" autocomplete="new-password"
                           @required($creando) minlength="8">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-dark">
                    {{ $creando ? 'Crear cuenta' : 'Guardar cambios' }}
                </button>
            </div>
        </form>
    </div>
</div>
