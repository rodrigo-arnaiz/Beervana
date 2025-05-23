<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cerveza->nombre ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="precio" class="form-label">Precio</label>
    <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio', $cerveza->precio ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="graduacion" class="form-label">Graduación (%)</label>
    <input type="number" step="0.1" name="graduacion" class="form-control" value="{{ old('graduacion', $cerveza->graduacion ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="tipo_envase" class="form-label">Tipo de Envase</label>
    <select name="tipo_envase" class="form-select" required>
        <option value="">— Seleccionar —</option>
        <option value="Lata" {{ old('tipo_envase', $cerveza->tipo_envase ?? '') == 'Lata' ? 'selected' : '' }}>Lata</option>
        <option value="Botella" {{ old('tipo_envase', $cerveza->tipo_envase ?? '') == 'Botella' ? 'selected' : '' }}>Botella</option>
    </select>
</div>

<div class="mb-3">
    <label for="capacidad" class="form-label">Capacidad</label>
    <input type="text" name="capacidad" class="form-control" value="{{ old('capacidad', $cerveza->capacidad ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="ibu" class="form-label">IBU</label>
    <input type="number" name="ibu" class="form-control" value="{{ old('ibu', $cerveza->ibu ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="imagen" class="form-label">Imagen</label>
    <input type="file" name="imagen" class="form-control" accept="image/*">
</div>

<div class="mb-3">
    <label for="stock" class="form-label">Stock</label>
    <input type="number" name="stock" class="form-control" value="{{ old('stock', $cerveza->stock ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control">{{ old('descripcion', $cerveza->descripcion ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="marca_id" class="form-label">Marca</label>
    <select name="marca_id" class="form-select" required>
        <option value="">Seleccione una marca</option>
        @foreach ($marcas as $marca)
            <option value="{{ $marca->id }}" {{ old('marca_id', $cerveza->marca_id ?? '') == $marca->id ? 'selected' : '' }}>
                {{ $marca->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="estilo_id" class="form-label">Estilo</label>
    <select name="estilo_id" class="form-select" required>
        <option value="">Seleccione un estilo</option>
        @foreach ($estilos as $estilo)
            <option value="{{ $estilo->id }}" {{ old('estilo_id', $cerveza->estilo_id ?? '') == $estilo->id ? 'selected' : '' }}>
                {{ $estilo->nombre }}
            </option>
        @endforeach
    </select>
</div>