@extends('layouts.admin')

@section('title', 'Mostrador')

@php
    // Un pedido con poco tiempo se atiende primero: se marca en rojo para que
    // salte a la vista sin tener que leer todas las fechas.
    $urgencia = fn ($minutos) => $minutos < 120 ? 'text-danger fw-bold' : 'text-muted';

    $restante = function ($minutos) {
        if ($minutos <= 0) return 'vencido';
        $horas = intdiv($minutos, 60);
        $mins = $minutos % 60;
        if ($horas >= 24) return intdiv($horas, 24) . 'd ' . ($horas % 24) . 'h';
        return $horas > 0 ? "{$horas}h {$mins}m" : "{$mins}m";
    };
@endphp

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-1">Mostrador</h1>
    <p class="text-muted">Cobro presencial de pedidos.</p>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ── Buscar por el código que trae el cliente ───────────────────────── --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('mostrador.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="codigo" class="form-label">Código del comprobante</label>
                    <input type="text" name="codigo" id="codigo" value="{{ $buscado }}"
                           class="form-control form-control-lg text-uppercase"
                           style="font-family: monospace; letter-spacing: 2px;"
                           placeholder="BV-XXXXXX" autofocus autocomplete="off">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark btn-lg w-100" type="submit">Buscar</button>
                </div>
            </form>

            @if ($sinResultado)
                <div class="alert alert-warning mt-3 mb-0">
                    No hay ningún pedido con el código <strong>{{ strtoupper($buscado) }}</strong>.
                </div>
            @endif
        </div>
    </div>

    {{-- ── El pedido encontrado ───────────────────────────────────────────── --}}
    @if ($encontrado)
        @php $vigente = $encontrado->estaVigente(); @endphp
        <div class="card mb-4 border-dark">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span>Pedido {{ $encontrado->codigo }}</span>
                <span class="badge bg-light text-dark">{{ strtoupper($encontrado->estado) }}</span>
            </div>
            <div class="card-body">
                {{-- Verificar la identidad antes de cobrar: el código dice QUÉ
                     pedido es, no QUIÉN lo trae. --}}
                <div class="alert alert-info">
                    <strong>A nombre de:</strong> {{ $encontrado->user->name }}
                    ({{ $encontrado->user->email }})<br>
                    <small>Pedí un documento y verificá que coincida antes de entregar.</small>
                </div>

                <p class="mb-1">
                    <strong>Entrega:</strong>
                    {{ $encontrado->metodo_entrega === 'retiro' ? 'Retiro en el local' : 'Envío a domicilio' }}
                </p>
                @if ($vigente)
                    <p class="{{ $urgencia($encontrado->minutosRestantes()) }}">
                        Reserva vigente · quedan {{ $restante($encontrado->minutosRestantes()) }}
                    </p>
                @endif

                <table class="table table-sm">
                    <thead>
                        <tr><th>Producto</th><th class="text-end">Cant.</th><th class="text-end">P. unit.</th><th class="text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($encontrado->items as $item)
                            <tr>
                                <td>{{ $item->cerveza->nombre }}</td>
                                <td class="text-end">{{ $item->cantidad }}</td>
                                <td class="text-end">${{ number_format($item->precio_unitario, 2) }}</td>
                                <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- El importe que vale es este, NO el que dice el papel del
                     cliente: ese se puede editar antes de imprimir. --}}
                <div class="text-end">
                    <div class="h3 mb-0">A COBRAR: ${{ number_format($encontrado->precio_total, 2) }}</div>
                    <small class="text-muted">Cobrá este importe, no el impreso en el comprobante.</small>
                </div>

                @if ($encontrado->estado === \App\Models\Pedido::PAGADO)
                    <div class="alert alert-secondary mt-3 mb-0">
                        Ya fue cobrado. Factura N.º {{ $encontrado->factura?->id }}
                        @if ($encontrado->factura?->medio_pago === 'mercadopago')
                            <span class="badge bg-info text-dark ms-1">Pagado con Mercado Pago</span>
                        @endif
                        <div class="small mt-1">No cobres nada: entregá el pedido.</div>
                    </div>
                @elseif (! $vigente)
                    <div class="alert alert-danger mt-3 mb-0">
                        Este pedido no se puede cobrar (estado: {{ $encontrado->estado }}).
                    </div>
                @else
                    <div class="d-grid gap-2 mt-3">
                        <form method="POST" action="{{ route('mostrador.cobrar', $encontrado->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg w-100"
                                    onclick="return confirm('¿Cobrar ${{ number_format($encontrado->precio_total, 2) }} en efectivo a {{ $encontrado->user->name }}?')">
                                Cobrar en efectivo y emitir factura
                            </button>
                        </form>

                        {{-- El cliente paga con el celular ahí mismo: se genera el
                             cobro y la pantalla siguiente muestra el QR y espera la
                             confirmación de MP. La factura sale sola cuando acredita. --}}
                        <form method="POST" action="{{ route('mostrador.mp.crear', $encontrado->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-lg w-100">
                                Cobrar con Mercado Pago
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Los que avisaron que vienen ────────────────────────────────────── --}}
    <h2 class="h5 mt-4">
        Esperando pago en el local
        <span class="badge bg-warning text-dark">{{ $confirmados->count() }}</span>
    </h2>
    <p class="text-muted small">El cliente confirmó que viene a pagarlo.</p>
    @include('admin.partials.tabla-pedidos', ['pedidos' => $confirmados, 'vacio' => 'Nadie avisó que viene todavía.'])

    {{-- ── Los que todavía no decidieron nada ─────────────────────────────── --}}
    <h2 class="h5 mt-4">
        Sin confirmar
        <span class="badge bg-secondary">{{ $pendientes->count() }}</span>
    </h2>
    <p class="text-muted small">
        Reservan stock pero el cliente no eligió cómo pagar. Si vencen, las unidades se liberan solas.
    </p>
    @include('admin.partials.tabla-pedidos', ['pedidos' => $pendientes, 'vacio' => 'No hay pedidos sin confirmar.'])
</div>
@endsection
