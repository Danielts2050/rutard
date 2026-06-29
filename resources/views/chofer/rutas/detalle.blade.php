@extends('layouts.chofer')
@section('title', 'Detalle de Ruta')
@push('styles')
<style>#mapa-detalle { height: 400px; border-radius: 0.5rem; border: 1px solid #d1d5db; }</style>
@endpush

@section('content')
<div class="mb-6">
    <a href="{{ route('chofer.rutas.historial') }}" class="text-blue" style="display: inline-block; margin-bottom: 1rem;">&larr; Historial</a>

    <h1 class="font-bold" style="font-size: 1.5rem; margin-bottom: 1.5rem;">Detalle de Ruta</h1>

    @php
        $inicio = \Carbon\Carbon::parse($ruta->hora_inicio);
        $fin = $ruta->hora_fin ? \Carbon\Carbon::parse($ruta->hora_fin) : null;
    @endphp

    <div class="grid-4">
        <div class="card">
            <div class="text-sm text-gray">Veh&iacute;culo</div>
            <div class="font-semibold">{{ $ruta->vehiculo->placa ?? 'N/A' }}</div>
        </div>
        <div class="card">
            <div class="text-sm text-gray">Inicio</div>
            <div class="font-semibold">{{ $inicio->format('d/m/Y H:i:s') }}</div>
        </div>
        <div class="card">
            <div class="text-sm text-gray">Fin</div>
            <div class="font-semibold">{{ $fin?->format('d/m/Y H:i:s') ?? '-' }}</div>
        </div>
        <div class="card">
            <div class="text-sm text-gray">Duraci&oacute;n</div>
            <div class="font-semibold">{{ $fin ? $inicio->diff($fin)->format('%H:%I') . ' h' : '-' }}</div>
        </div>
    </div>

    <div id="mapa-detalle" class="mt-4"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var map = L.map('mapa-detalle').setView([18.4861, -69.9312], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var positions = [
        @foreach($ruta->ubicaciones as $ub)
            [{{ $ub->latitud }}, {{ $ub->longitud }}],
        @endforeach
    ];

    if (positions.length > 1) {
        L.polyline(positions, { color: '#3b82f6', weight: 3 }).addTo(map);
        L.marker(positions[0]).addTo(map).bindPopup('Inicio');
        L.marker(positions[positions.length - 1]).addTo(map).bindPopup('Fin');
        map.fitBounds(L.latLngBounds(positions));
    } else if (positions.length === 1) {
        L.marker(positions[0]).addTo(map);
        map.setView(positions[0], 15);
    }
})();
</script>
@endpush
