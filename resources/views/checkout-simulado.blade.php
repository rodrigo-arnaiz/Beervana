<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago simulado · Beervana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f2ee; min-height: 100vh; display: flex; align-items: center; }
        .caja { max-width: 26rem; margin: 2rem auto; }
        /* Deliberadamente distinto del checkout real de Mercado Pago: esto no
           tiene que poder confundirse nunca con una pantalla de pago de verdad. */
        .franja-simulacion {
            background: repeating-linear-gradient(45deg, #6c757d, #6c757d 12px, #5a6268 12px, #5a6268 24px);
            color: #fff;
            font-size: .78rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-align: center;
            padding: .5rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="caja w-100 px-3">
        <div class="card shadow-sm">
            <div class="franja-simulacion">Pago simulado — no es Mercado Pago</div>

            <div class="card-body">
                <p class="text-muted small mb-1">Pedido</p>
                <p class="fw-bold mb-3" style="font-family: monospace;">{{ $pedido->codigo }}</p>

                <p class="text-muted small mb-1">Total</p>
                <p class="display-6 fw-bold mb-4">${{ number_format($pago->monto, 2, ',', '.') }}</p>

                <ul class="list-unstyled small text-muted border-top pt-3 mb-4">
                    @foreach ($pedido->items as $item)
                        <li class="d-flex justify-content-between">
                            <span>{{ $item->cantidad }} × {{ $item->cerveza->nombre ?? 'Producto' }}</span>
                            <span>${{ number_format($item->subtotal, 2, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($yaResuelto)
                    @if ($pago->estaAprobado())
                        <div class="alert alert-success mb-0">
                            <strong>Pago aprobado.</strong>
                            <div class="small mt-1">
                                Ya podés volver: la pantalla del mostrador se actualizó sola.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger mb-0">
                            <strong>Pago rechazado.</strong>
                            <div class="small mt-1">
                                El pedido sigue reservado. Se puede generar otro cobro.
                            </div>
                        </div>
                    @endif
                @else
                    <p class="small text-muted">
                        Elegí cómo querés que responda el pago. Es lo que en el modo real
                        decide el nombre del titular de la tarjeta de prueba.
                    </p>

                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('checkout.simulado.resolver', ['codigo' => $pedido->codigo]) }}">
                            @csrf
                            <input type="hidden" name="resultado" value="aprobar">
                            <button type="submit" class="btn btn-success btn-lg w-100">Pagar (aprobado)</button>
                        </form>

                        <form method="POST" action="{{ route('checkout.simulado.resolver', ['codigo' => $pedido->codigo]) }}">
                            @csrf
                            <input type="hidden" name="resultado" value="rechazar">
                            <button type="submit" class="btn btn-outline-danger w-100">Simular un rechazo</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <p class="text-center text-muted small mt-3">
            Esta pantalla solo existe con <code>MERCADOPAGO_MODO=simulado</code>.
            Con credenciales reales, acá va el checkout de Mercado Pago.
        </p>
    </div>
</body>
</html>
