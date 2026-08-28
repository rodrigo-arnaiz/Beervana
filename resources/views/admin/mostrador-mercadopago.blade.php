@extends('layouts.admin')

@section('title', 'Cobro por Mercado Pago')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">Cobro por Mercado Pago</h1>
                <span class="badge bg-dark fs-6">{{ $pago->pedido->codigo }}</span>
            </div>

            <div class="card shadow-sm">
                <div class="card-body text-center">

                    <p class="text-muted mb-1">Total a cobrar</p>
                    <p class="display-6 fw-bold mb-4">${{ number_format($pago->monto, 2, ',', '.') }}</p>

                    {{-- El estado lo actualiza el script del final --}}
                    <div id="estado-cobro" class="alert alert-warning d-flex align-items-center justify-content-center gap-2">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span id="estado-texto">Esperando que el cliente pague...</span>
                    </div>

                    <div id="zona-qr">
                        @if ($pago->qr_data)
                            {{-- El QR presencial de MP: el mismo que usa cualquier
                                 local. Lleva el monto adentro, así que el cliente
                                 apunta y confirma, sin escribir nada. --}}
                            <p class="mb-2 fw-semibold">Que escanee este código con la app de Mercado Pago</p>

                            <div id="qr" class="d-inline-block bg-white p-3 rounded shadow-sm mb-2"
                                 data-qr="{{ $pago->qr_data }}"></div>

                            <p class="text-muted small mb-4">
                                El monto ya va en el código: no tiene que escribir nada.
                            </p>

                            <details class="text-start small">
                                <summary class="text-muted" style="cursor: pointer;">
                                    ¿Prefiere pagar con tarjeta?
                                </summary>
                                <div class="mt-2">
                                    <p class="text-muted mb-2">
                                        Pasale este link, que abre el checkout de Mercado Pago:
                                    </p>
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="text" class="form-control" id="link-pago"
                                               value="{{ $pago->init_point }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="copiar-link">Copiar</button>
                                    </div>
                                    <a href="{{ $pago->init_point }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-primary">Abrirlo acá</a>
                                </div>
                            </details>
                        @else
                            {{-- Sin caja configurada no hay QR presencial. El cobro
                                 sigue en pie por el link, así que se avisa y ya. --}}
                            <div class="alert alert-secondary text-start small">
                                No hay caja de Mercado Pago configurada, así que no se puede
                                generar el QR. El cobro funciona igual con el link de abajo.
                                Para habilitarlo: <code>php artisan mp:caja</code>
                            </div>

                            <div class="input-group input-group-sm mb-3">
                                <input type="text" class="form-control" id="link-pago"
                                       value="{{ $pago->init_point }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="copiar-link">Copiar</button>
                            </div>

                            <a href="{{ $pago->init_point }}" target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-primary mb-3">
                                Abrir el checkout en otra pestaña
                            </a>
                        @endif
                    </div>

                    <hr>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('mostrador.index') }}" class="btn btn-outline-secondary">
                            Volver al mostrador
                        </a>

                        {{-- Cancelar cierra la espera de nuestro lado. La preferencia
                             sigue viva en MP a propósito: si el cliente ya escaneó y
                             paga igual, ese pago tiene que llegar y procesarse. --}}
                        <form method="POST" action="{{ route('mostrador.mp.cancelar', $pago) }}"
                              onsubmit="return confirm('Se cancela la espera de este cobro. Si el cliente ya escaneó el QR y paga igual, el pago se acredita lo mismo. ¿Seguir?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Cancelar el cobro</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h2 class="h6">Detalle del pedido</h2>
                    <ul class="list-unstyled mb-0 small">
                        @foreach ($pago->pedido->items as $item)
                            <li class="d-flex justify-content-between">
                                <span>{{ $item->cantidad }} × {{ $item->cerveza->nombre ?? 'Producto' }}</span>
                                <span>${{ number_format($item->subtotal, 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                        @if ($pago->pedido->envio > 0)
                            <li class="d-flex justify-content-between">
                                <span>Envío</span>
                                <span>${{ number_format($pago->pedido->envio, 2, ',', '.') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function () {
    // Se dibuja la trama EMVCo que devolvió MP, no una URL. Es lo que hace que
    // la app de Mercado Pago lo reconozca como un cobro y no como un link.
    var contenedor = document.getElementById('qr');
    var trama = contenedor ? contenedor.dataset.qr : null;

    if (trama && window.QRCode) {
        new QRCode(contenedor, {
            text: trama,
            width: 240,
            height: 240,
            // La trama es larga: con corrección alta el código queda tan denso
            // que las cámaras lo leen peor. Media es el punto justo.
            correctLevel: QRCode.CorrectLevel.M,
        });
    }

    var botonCopiar = document.getElementById('copiar-link');

    if (botonCopiar) {
        botonCopiar.addEventListener('click', function () {
            var campo = document.getElementById('link-pago');
            campo.select();
            navigator.clipboard.writeText(campo.value);
            botonCopiar.textContent = 'Copiado';
            setTimeout(function () { botonCopiar.textContent = 'Copiar'; }, 1500);
        });
    }

    // Se consulta el estado cada 3 segundos en vez de esperar el webhook: en
    // local MP no puede alcanzar localhost, así que una pantalla que dependiera
    // del aviso se quedaría esperando para siempre. En producción el webhook
    // llega antes y esto solo confirma lo que ya pasó.
    var caja = document.getElementById('estado-cobro');
    var texto = document.getElementById('estado-texto');
    var zonaQr = document.getElementById('zona-qr');
    var intentos = 0;

    var reloj = setInterval(function () {
        // ~5 minutos y corta: dejar un intervalo golpeando la API de MP para
        // siempre, en una pantalla que alguien olvidó abierta, no ayuda a nadie.
        if (++intentos > 100) {
            clearInterval(reloj);
            texto.textContent = 'Se dejó de consultar. Recargá la página si el cliente sigue pagando.';
            return;
        }

        fetch(@json(route('mostrador.mp.estado', $pago)), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (datos) {
                if (datos.listo) {
                    clearInterval(reloj);
                    caja.className = 'alert alert-success';
                    texto.textContent = '¡Pago acreditado! Ya podés entregar el pedido.';
                    zonaQr.style.display = 'none';
                    setTimeout(function () { window.location.href = datos.volver_a; }, 2500);
                    return;
                }

                if (datos.estado === 'rechazado') {
                    caja.className = 'alert alert-danger';
                    texto.textContent = 'El pago fue rechazado'
                        + (datos.detalle ? ' (' + datos.detalle + ')' : '')
                        + '. El cliente puede volver a intentar.';
                    return;
                }

                if (datos.estado === 'en_proceso') {
                    caja.className = 'alert alert-info';
                    texto.textContent = 'Mercado Pago está revisando el pago. Esperá la confirmación.';
                }
            })
            .catch(function () { /* un error de red suelto no tiene que romper la espera */ });
    }, 3000);
})();
</script>
@endsection
