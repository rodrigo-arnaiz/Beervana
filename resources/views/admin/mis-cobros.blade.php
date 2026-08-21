@extends('layouts.admin')

@section('title', 'Mis cobros')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-1">Mis cobros</h1>
    <p class="text-muted">Ventas que registraste en el mostrador.</p>

    <form method="GET" action="{{ route('mis-cobros.index') }}" class="row g-2 align-items-end mb-4">
        <div class="col-md-3">
            <label for="desde" class="form-label">Desde</label>
            <input type="date" name="desde" id="desde" class="form-control"
                   value="{{ $desde->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label for="hasta" class="form-label">Hasta</label>
            <input type="date" name="hasta" id="hasta" class="form-control"
                   value="{{ $hasta->format('Y-m-d') }}">
        </div>

        {{-- El admin puede mirar la caja de cualquiera; el empleado solo la suya --}}
        @if ($esAdmin)
            <div class="col-md-3">
                <label for="cobrador" class="form-label">Cobrado por</label>
                <select name="cobrador" id="cobrador" class="form-select">
                    @foreach ($personal as $persona)
                        <option value="{{ $persona->id }}" @selected($persona->id === $cobradorId)>
                            {{ $persona->name }} ({{ $persona->rol }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2">
            <button class="btn btn-dark w-100" type="submit">Filtrar</button>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="text-muted small text-uppercase">Cobros</div>
                <div class="h4 mb-0">{{ $facturas->count() }}</div>
            </div>
            <div class="text-end">
                <div class="text-muted small text-uppercase">Total cobrado</div>
                <div class="h3 mb-0">${{ number_format($total, 2) }}</div>
            </div>
        </div>
    </div>

    @if ($facturas->isEmpty())
        <p class="text-muted fst-italic">No registraste cobros en este período.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Factura</th>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Fecha y hora</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($facturas as $factura)
                        <tr>
                            <td>N.º {{ str_pad($factura->id, 8, '0', STR_PAD_LEFT) }}</td>
                            <td style="font-family: monospace;">{{ $factura->pedido?->codigo }}</td>
                            <td>{{ $factura->user->name }}</td>
                            <td>
                                <small>
                                    @foreach ($factura->pedido?->items ?? [] as $item)
                                        {{ $item->cantidad }}× {{ $item->cerveza->nombre }}@if (! $loop->last)<br>@endif
                                    @endforeach
                                </small>
                            </td>
                            <td><small>{{ $factura->created_at->format('d/m/Y H:i') }}</small></td>
                            <td class="text-end fw-bold">${{ number_format($factura->precio_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">${{ number_format($total, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
