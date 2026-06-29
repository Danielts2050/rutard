@extends('layouts.chofer')
@section('title', 'Ruta Activa')
@push('styles')
<style>#mapa-activo { height: calc(100vh - 240px); min-height: 400px; }</style>
@endpush

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold">Ruta Activa</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Vehículo: {{ $ruta->vehiculo->placa ?? 'N/A' }} |
                Inicio: {{ \Carbon\Carbon::parse($ruta->hora_inicio)->format('H:i:s') }}
            </p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-mono font-bold" id="timer">00:00:00</div>
            <div class="text-sm text-gray-500">tiempo transcurrido</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-2 text-center text-sm">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
            <div class="text-lg font-bold" id="speed">0.0</div>
            <div class="text-gray-500">km/h</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
            <div class="text-lg font-bold" id="distancia">0.0</div>
            <div class="text-gray-500">km</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
            <div class="text-lg font-bold" id="precision">0</div>
            <div class="text-gray-500">±m</div>
        </div>
    </div>

    <div id="mapa-activo" class="rounded-lg border border-gray-300 dark:border-gray-600"></div>

    <div class="grid grid-cols-2 gap-4">
        <button id="btn-finalizar" onclick="confirmarFinalizar()"
                class="w-full py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">
            Finalizar Ruta
        </button>
        <button id="btn-recenter" onclick="recentrar()"
                class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
            📍 Centrar
        </button>
    </div>
</div>

<form id="finalizar-form" method="POST" action="{{ route('chofer.rutas.finalizar', $ruta) }}" class="hidden">
    @csrf
    <input type="hidden" name="latitud_fin" id="latitud_fin">
    <input type="hidden" name="longitud_fin" id="longitud_fin">
    <input type="hidden" name="km_recorridos" id="km_recorridos">
</form>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const rutaId = {{ $ruta->id }};
const startTime = new Date("{{ \Carbon\Carbon::parse($ruta->hora_inicio)->format('Y/m/d H:i:s') }}").getTime();

const map = L.map('mapa-activo').setView([18.4861, -69.9312], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '© OpenStreetMap'
}).addTo(map);

const userMarker = L.circleMarker([18.4861, -69.9312], {
    radius: 8, fillColor: '#3b82f6', color: '#fff', weight: 2, fillOpacity: 1
}).addTo(map);

let accuracyCircle = L.circle([18.4861, -69.9312], { radius: 10, color: '#3b82f6', fillOpacity: 0.1 });
let pathLine = L.polyline([], { color: '#3b82f6', weight: 3 }).addTo(map);
let positions = [];
let totalKm = 0;

function updatePosition(pos) {
    const { latitude: lat, longitude: lng, accuracy, speed } = pos.coords;
    userMarker.setLatLng([lat, lng]);
    accuracyCircle.setLatLng([lat, lng]).setRadius(accuracy || 10);
    positions.push([lat, lng]);
    pathLine.setLatLngs(positions);
    document.getElementById('speed').textContent = (speed * 3.6).toFixed(1);
    document.getElementById('precision').textContent = Math.round(accuracy || 0);

    if (positions.length > 1) {
        const prev = positions[positions.length - 2];
        const d = haversine(prev[0], prev[1], lat, lng);
        totalKm += d;
        document.getElementById('distancia').textContent = totalKm.toFixed(1);
    }

    fetch('{{ route('chofer.rutas.ubicaciones', $ruta) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ latitud: lat, longitud: lng })
    }).catch(() => {});
}

const watchId = navigator.geolocation.watchPosition(updatePosition, () => {}, {
    enableHighAccuracy: true, maximumAge: 5000, timeout: 10000
});

function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function recentrar() {
    const pos = userMarker.getLatLng();
    map.setView(pos, 15);
}

function confirmarFinalizar() {
    navigator.geolocation.getCurrentPosition((pos) => {
        document.getElementById('latitud_fin').value = pos.coords.latitude;
        document.getElementById('longitud_fin').value = pos.coords.longitude;
        document.getElementById('km_recorridos').value = totalKm.toFixed(2);
        document.getElementById('finalizar-form').submit();
    }, () => {
        document.getElementById('latitud_fin').value = userMarker.getLatLng().lat;
        document.getElementById('longitud_fin').value = userMarker.getLatLng().lng;
        document.getElementById('km_recorridos').value = totalKm.toFixed(2);
        document.getElementById('finalizar-form').submit();
    }, { enableHighAccuracy: true, timeout: 5000 });
}

function updateTimer() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
    const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
    const s = String(elapsed % 60).padStart(2, '0');
    document.getElementById('timer').textContent = `${h}:${m}:${s}`;
}
setInterval(updateTimer, 1000);
updateTimer();

// Load existing ubicaciones as path
@if($ruta->ubicaciones->count() > 1)
    positions = [
        @foreach($ruta->ubicaciones as $ub)
            [{{ $ub->latitud }}, {{ $ub->longitud }}],
        @endforeach
    ];
    pathLine.setLatLngs(positions);
    userMarker.setLatLng(positions[positions.length - 1]);
    map.setView(positions[positions.length - 1], 15);
@endif
</script>
@endpush
