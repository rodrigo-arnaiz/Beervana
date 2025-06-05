@extends('layouts.admin')

@section('content')
   <div class="row mb-4">
    <!-- Estadísticas rápidas -->
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Cervezas</h5>
                <p class="card-text fs-4">120</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Stock Disponible</h5>
                <p class="card-text fs-4">1.540 u.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pedidos Pendientes</h5>
                <p class="card-text fs-4">8</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Stock Crítico</h5>
                <p class="card-text fs-4">5 ítems</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de resumen de stock -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Resumen de Stock de Cervezas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Stock</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cervezas as $index => $cervezas)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $cervezas->nombre }}</td>
                            <td>{{ $cervezas->marca->nombre}}</td>
                            <td>
                                @if ($cervezas->stock <= 10)
                                    <span class="badge bg-danger">{{ $cervezas->stock }} bajo</span>
                                @elseif ($cervezas->stock <= 30)
                                    <span class="badge bg-warning text-dark">{{ $cervezas->stock }}</span>
                                @else
                                    <span class="badge bg-success">{{ $cervezas->stock }}</span>
                                @endif
                            </td>
                            <td>${{ number_format($cervezas->precio, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay cervezas en stock.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ranking de cervezas más vendidas -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Top 5 Cervezas Más Vendidas</h5>
    </div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between">IPA Roja <span class="badge bg-primary">320 ventas</span></li>
        <li class="list-group-item d-flex justify-content-between">Golden Ale <span class="badge bg-primary">290 ventas</span></li>
        <li class="list-group-item d-flex justify-content-between">Negra Stout <span class="badge bg-primary">250 ventas</span></li>
        <li class="list-group-item d-flex justify-content-between">Honey <span class="badge bg-primary">200 ventas</span></li>
        <li class="list-group-item d-flex justify-content-between">Scotch <span class="badge bg-primary">180 ventas</span></li>
    </ul>
</div>

<!-- Gráfico de stock (placeholder) -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Gráfico de Stock por Marca</h5>
    </div>
    <div class="card-body text-center">
        <img src="https://via.placeholder.com/600x250?text=Gráfico+de+Stock+por+Marca" alt="Grafico Stock" class="img-fluid">
    </div>
</div>

<!-- Alertas del sistema -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">Alertas del Sistema</h5>
    </div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item text-danger">Cerveza "APA Lupulada" con stock en 0.</li>
        <li class="list-group-item text-danger">Error al generar reporte de ventas del mes.</li>
        <li class="list-group-item text-warning">Actualizar precios antes de fin de mes.</li>
    </ul>
</div>

<!-- Actividades recientes -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Actividades Recientes</h5>
    </div>
    <div class="card-body">
        <ul class="list-unstyled">
            <li>🕒 10:32 - Se actualizó el stock de "Golden Ale".</li>
            <li>🕒 09:47 - Se registró nuevo pedido de 15 unidades.</li>
            <li>🕒 08:15 - Admin modificó los precios de la línea "Stout".</li>
        </ul>
    </div>
</div>
@endsection
