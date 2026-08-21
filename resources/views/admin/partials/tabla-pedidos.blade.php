{{-- Tabla de pedidos activos para el mostrador.
     Espera: $pedidos (colección) y $vacio (texto cuando no hay ninguno). --}}
@php
    $urgencia = fn ($minutos) => $minutos < 120 ? 'text-danger fw-bold' : 'text-muted';
    $restante = function ($minutos) {
        if ($minutos <= 0) return 'vencido';
        $horas = intdiv($minutos, 60);
        $mins = $minutos % 60;
        if ($horas >= 24) return intdiv($horas, 24) . 'd ' . ($horas % 24) . 'h';
        return $horas > 0 ? "{$horas}h {$mins}m" : "{$mins}m";
    };
@endphp

@if ($pedidos->isEmpty())
    <p class="text-muted fst-italic">{{ $vacio }}</p>
@else
    <div class="table-responsive mb-3">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Productos</th>
                    <th>Entrega</th>
                    <th class="text-end">Total</th>
                    <th>Tiempo restante</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                    <tr>
                        <td style="font-family: monospace; font-weight: bold;">{{ $pedido->codigo }}</td>
                        <td>{{ $pedido->user->name }}</td>
                        <td>
                            <small>
                                @foreach ($pedido->items as $item)
                                    {{ $item->cantidad }}× {{ $item->cerveza->nombre }}@if (! $loop->last)<br>@endif
                                @endforeach
                            </small>
                        </td>
                        <td>
                            <small>{{ $pedido->metodo_entrega === 'retiro' ? 'Retiro' : 'Envío' }}</small>
                        </td>
                        <td class="text-end">${{ number_format($pedido->precio_total, 2) }}</td>
                        <td class="{{ $urgencia($pedido->minutosRestantes()) }}">
                            {{ $restante($pedido->minutosRestantes()) }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('mostrador.index', ['codigo' => $pedido->codigo]) }}"
                               class="btn btn-sm btn-outline-dark">Abrir</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
