@extends('layouts.chofer')
@section('title', 'Detalle de Ruta')
@push('styles')
<style>#mapa-detalle { height: 400px; }</style>
@endpush

@section('content')
<div class="space-y-4">
    <a href="{{ route('chofer.rutas.historial') }}" class="text-blue-600 hover:underline">&larr; Historial</a>

    <h1 class="text-2xl font-bold">Detalle de Ruta</h1>

    @php
        $inicio = \Carbon\Carbon::parse($ruta->hora_inicio);
        $fin = $ruta->hora_fin ? \Carbon\Carbon::parse($ruta->hora_fin) : null;
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Vehículo</div>
            <div class="font-semibold">{{ $ruta->vehiculo->placa ?? 'N/A' }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Inicio</div>
            <div class="font-semibold">{{ $inicio->format('d/m/Y H:i:s') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Fin</div>
            <div class="font-semibold">{{ $fin?->format('d/m/Y H:i:s') ?? '-' }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Duración</div>
            <div class="font-semibold">{{ $fin ? $inicio->diff($fin)->format('%H:%I') . ' h' : '-' }}</div>
        </div>
    </div>

    <div id="mapa-detalle" class="rounded-lg border border-gray-300 dark:border-gray-600"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('mapa-detalle').setView([18.4861, -69.9312], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const positions = [
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
</script>
@endpush
