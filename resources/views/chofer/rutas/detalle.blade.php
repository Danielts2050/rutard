@extends('layouts.chofer')
@section('title', 'Detalle de Ruta')
@push('styles')
<style>
    #mapa-detalle { height: 400px; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
    .leaflet-container { background: var(--bg-body); }
</style>
@endpush

@section('content')
<div class="mb-6">
    <a href="{{ route('chofer.rutas.historial') }}" class="text-green" style="display: inline-block; margin-bottom: 1rem; font-size: 0.875rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><polyline points="15 18 9 12 15 6"/></svg>
        Historial
    </a>

    <div class="section-header">
        <h1 class="section-title">Detalle de Ruta</h1>
        @php
            $inicio = \Carbon\Carbon::parse($ruta->hora_inicio);
            $fin = $ruta->hora_fin ? \Carbon\Carbon::parse($ruta->hora_fin) : null;
        @endphp
        @if($fin)
            <span class="badge badge-green"><span class="badge-dot green"></span> Completada</span>
        @else
            <span class="badge badge-yellow"><span class="badge-dot yellow"></span> En curso</span>
        @endif
    </div>

    <div class="detail-grid">
        <div class="detail-card">
            <div class="label">Vehiculo</div>
            <div class="value">{{ $ruta->vehiculo->placa ?? 'N/A' }}</div>
        </div>
        <div class="detail-card">
            <div class="label">Inicio</div>
            <div class="value font-mono" style="font-size:0.8125rem;">{{ $inicio->format('d/m/Y H:i:s') }}</div>
        </div>
        <div class="detail-card">
            <div class="label">Fin</div>
            <div class="value font-mono" style="font-size:0.8125rem;">{{ $fin?->format('d/m/Y H:i:s') ?? '-' }}</div>
        </div>
        <div class="detail-card">
            <div class="label">Duracion</div>
            <div class="value text-green">{{ $fin ? $inicio->diff($fin)->format('%H:%I') . ' h' : '-' }}</div>
        </div>
    </div>

    <div class="map-container mt-4">
        <div id="mapa-detalle"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var map = L.map('mapa-detalle').setView([18.4861, -69.9312], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    var positions = [
        @foreach($ruta->ubicaciones as $ub)
            [{{ $ub->latitud }}, {{ $ub->longitud }}],
        @endforeach
    ];

    if (positions.length > 1) {
        L.polyline(positions, { color: '#4ade80', weight: 3, opacity: 0.8 }).addTo(map);
        L.circleMarker(positions[0], { radius: 6, fillColor: '#4ade80', color: '#fff', weight: 2, fillOpacity: 1 }).addTo(map).bindPopup('Inicio');
        L.circleMarker(positions[positions.length - 1], { radius: 6, fillColor: '#ef4444', color: '#fff', weight: 2, fillOpacity: 1 }).addTo(map).bindPopup('Fin');
        map.fitBounds(L.latLngBounds(positions), { padding: [30, 30] });
    } else if (positions.length === 1) {
        L.circleMarker(positions[0], { radius: 6, fillColor: '#4ade80', color: '#fff', weight: 2, fillOpacity: 1 }).addTo(map);
        map.setView(positions[0], 15);
    }
})();
</script>
@endpush
