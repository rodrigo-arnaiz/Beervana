<div class="modal fade" id="detalleModal{{ $cerveza->id }}" tabindex="-1" aria-labelledby="detalleLabel{{ $cerveza->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content detalle-modal shadow-lg">
      <!-- Encabezado -->
      <div class="modal-header bg-white border-bottom-0">
        <h4 class="modal-title text-base text-[#422b0d] tracking-wide" id="detalleLabel{{ $cerveza->id }}">
          <strong>Detalle {{ $cerveza->nombre }} </strong>
        </h4>
      </div>

      <!-- Cuerpo del modal -->
      <div class="modal-body bg-white p-4">
        <div class="flex flex-col md:flex-row gap-6">
          <div class="beer-image-container">
            @if($cerveza->imagen)
              <img src="{{ $cerveza->imagen }}"
                   alt="{{ $cerveza->nombre }}" 
                   class="beer-image">
            @else
              <div class="beer-image-placeholder">
                Sin imagen
              </div>
            @endif
          </div>
          <div class="beer-details">
            <div class="space-y-3">
              <p><strong>Nombre:</strong> {{ $cerveza->nombre }}</p>
              <p><strong>Marca:</strong> {{ $cerveza->marca->nombre }}</p>
              <p><strong>Estilo:</strong> {{ $cerveza->estilo->nombre }}</p>
              <p><strong>Graduación alcohólica:</strong> {{ $cerveza->graduacion }}%</p>
              <p><strong>Nivel de amargor:</strong> {{ $cerveza->ibu }}</p>
              <p><strong>Descripción:</strong> {{ $cerveza->descripcion }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Pie del modal -->
      <div class="modal-footer bg-white border-top-0">
        <button type="button"
            class="close-button"
            data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>