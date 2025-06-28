@extends('layouts.admin')

@section('content')

   <div class="row mb-4">
    <!-- Estadísticas rápidas -->
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total tipos de Cervezas</h5>
                <p class="card-text fs-4">{{ $totalCervezas }}</p>
            </div>
        </div>
    </div>


    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Stock Disponible</h5>
                <p class="card-text fs-4">{{ $totalStock }} u.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div title="pedidos pendientes de entrega" class="card-body">
                <h5 class="card-title">Pedidos Pendientes</h5>
                <p class="card-text fs-4"> {{ $pedidosPendientes }} </p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div title="El Stock que se encuentra por debajo del umbral pre-definido ( < 10 unidades)" class="card-body">
                <h5 class="card-title">Stock Crítico</h5>
                <p class="card-text fs-4">{{ $totalStockCritico }} ítems</p>
            </div>
        </div>
    </div>
</div>


<!-- Gráfico de facturación por cerveza -->
<div class="card mb-4" style="min-height: 400px;">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Facturación por Cerveza</h5>
    </div>
    <div class="card-body">
        <canvas id="facturacionChart" height="400"></canvas>
    </div>
</div>

<!-- Ranking de cervezas más vendidas -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Top 5 Cervezas Más Vendidas</h5>
    </div>
    <ul class="list-group list-group-flush">
        @forelse ($topCervezas as $cerveza)
            <li class="list-group-item d-flex justify-content-between">
                {{ $cerveza->nombre }}
                <span class="badge bg-primary">{{ $cerveza->total_vendido }} ventas</span>
            </li>
        @empty
            <li class="list-group-item text-center text-muted">No hay ventas registradas aún.</li>
        @endforelse
    </ul>
</div>

<!-- Gráfico de stock por marca -->
<div class="card mb-4" style="min-height: 400px;">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Gráfico de Stock por Marca</h5>
    </div>
    <div class="card-body">
        <canvas id="stockChart" height="400"></canvas>
    </div>
</div>

<!-- Alertas del sistema -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">Alertas del Sistema</h5>
    </div>
    <ul class="list-group list-group-flush">
        @forelse ($alertas as $alerta)
            <li class="list-group-item text-{{ $alerta['tipo'] }}">
                {{ $alerta['mensaje'] }}
            </li>
        @empty
            <li class="list-group-item text-muted">No hay alertas en el sistema.</li>
        @endforelse
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');

    const stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($stockPorMarca->keys()) !!},
            datasets: [{
                label: 'Stock total',
                data: {!! json_encode($stockPorMarca->values()) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    });

    const ctxFacturacion = document.getElementById('facturacionChart').getContext('2d');
    //Facturacion por cerveza
    const facturacionChart = new Chart(ctxFacturacion, {
        type: 'bar',
        data: {
            labels: {!! json_encode($facturacionPorCerveza->pluck('cerveza')) !!},
            datasets: [{
                label: 'Total Facturado ($)',
                data: {!! json_encode($facturacionPorCerveza->pluck('total_facturado')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y', // horizontal bar chart
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

@endsection
