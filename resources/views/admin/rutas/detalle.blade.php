@extends('layouts.admin')

@section('title', "Ruta #{$ruta->id}")

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" rel="stylesheet">
<style>
    #map { height: 500px; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-map"></i> Ruta #{{ $ruta->id }}</h1>
    <div>
        <a href="{{ route('admin.exports.pdf', $ruta) }}" class="btn btn-danger">
            <i class="bi bi-filetype-pdf"></i> Exportar PDF
        </a>
        <a href="{{ route('admin.rutas.historial') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-secondary">Chofer</small>
                <p class="mb-0 fw-bold">{{ $ruta->chofer?->name ?? '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-secondary">Vehículo</small>
                <p class="mb-0 fw-bold">{{ $ruta->vehiculo?->placa ?? '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-secondary">Inicio</small>
                <p class="mb-0 fw-bold">{{ $ruta->hora_inicio?->format('d/m/Y H:i:s') ?? '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-secondary">Duración</small>
                <p class="mb-0 fw-bold">{{ $ruta->duracion_minutos ? "{$ruta->duracion_minutos} min" : '—' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-geo-alt"></i> Recorrido ({{ $puntos->count() }} puntos)</span>
        <span class="badge bg-{{ $ruta->estado === 'activa' ? 'success' : 'secondary' }}">
            {{ ucfirst($ruta->estado) }}
        </span>
    </div>
    <div class="card-body">
        <div id="map"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const puntos = @json($puntos);

    if (puntos.length === 0) {
        document.getElementById('map').innerHTML = '<div class="alert alert-info mb-0">Sin puntos de ubicación registrados.</div>';
        return;
    }

    const map = L.map('map').setView([puntos[0].latitud, puntos[0].longitud], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const coords = puntos.map(p => [p.latitud, p.longitud]);

    L.polyline(coords, { color: '#0d6efd', weight: 3 }).addTo(map);

    L.marker(coords[0]).addTo(map)
        .bindPopup(`<b>Inicio</b><br>${puntos[0].fecha_hora}`);

    L.marker(coords[coords.length - 1]).addTo(map)
        .bindPopup(`<b>Fin</b><br>${puntos[puntos.length - 1].fecha_hora}`);

    map.fitBounds(coords);
});
</script>
@endpush
