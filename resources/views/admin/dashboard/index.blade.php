@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" rel="stylesheet">
@endpush

@section('content')
<h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-truck"></i> Vehículos</h5>
                <p class="display-6 mb-0">{{ $totalVehiculos }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people"></i> Choferes</h5>
                <p class="display-6 mb-0">{{ $totalChoferes }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-play-circle"></i> Rutas Activas</h5>
                <p class="display-6 mb-0">{{ $rutasActivas }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> Total Rutas</h5>
                <p class="display-6 mb-0">{{ $totalRutas }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart"></i> Rutas por día (últimos 7 días)</div>
            <div class="card-body">
                <canvas id="chartRutas" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Vehículos por estado</div>
            <div class="card-body">
                <canvas id="chartVehiculos" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bell"></i> Alertas recientes</span>
        <a href="{{ route('admin.alertas.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
    </div>
    <div class="card-body p-0">
        @php $alertasRecientes = \App\Models\Alerta::latest('fecha_alerta')->take(5)->get(); @endphp
        @forelse ($alertasRecientes as $alerta)
            <div class="border-bottom p-2 {{ $alerta->leida ? '' : 'bg-light' }}">
                <div class="d-flex justify-content-between">
                    <div>
                        @if (!$alerta->leida)
                            <span class="badge bg-danger rounded-pill" style="width:6px;height:6px;padding:0;">&nbsp;</span>
                        @endif
                        <strong>{{ $alerta->titulo }}</strong>
                        <span class="badge bg-secondary">{{ str_replace('_', ' ', $alerta->tipo) }}</span>
                        <small class="text-secondary ms-2">{{ $alerta->fecha_alerta->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-3 text-center text-secondary">Sin alertas recientes.</div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history"></i> Últimas rutas</div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Chofer</th>
                    <th>Vehículo</th>
                    <th>Inicio</th>
                    <th>Estado</th>
                    <th>Duración</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ultimasRutas as $ruta)
                    <tr>
                        <td>{{ $ruta->id }}</td>
                        <td>{{ $ruta->chofer?->name ?? '—' }}</td>
                        <td>{{ $ruta->vehiculo?->placa ?? '—' }}</td>
                        <td>{{ $ruta->hora_inicio?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $ruta->estado === 'activa' ? 'success' : 'secondary' }}">
                                {{ ucfirst($ruta->estado) }}
                            </span>
                        </td>
                        <td>{{ $ruta->duracion_minutos ? "{$ruta->duracion_minutos} min" : '—' }}</td>
                        <td>
                            <a href="{{ route('admin.rutas.detalle', $ruta) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-map"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Sin rutas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('chartRutas'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($rutasPorDia->pluck('fecha')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
            datasets: [{
                label: 'Rutas',
                data: {!! json_encode($rutasPorDia->pluck('total')) !!},
                backgroundColor: '#0d6efd'
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('chartVehiculos'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($vehiculosPorEstado->pluck('estado')->map(fn($e) => ucfirst($e))) !!},
            datasets: [{
                data: {!! json_encode($vehiculosPorEstado->pluck('total')) !!},
                backgroundColor: ['#198754', '#ffc107', '#6c757d']
            }]
        },
        options: { responsive: true }
    });
});
</script>
@endpush
