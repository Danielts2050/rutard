@extends('layouts.chofer')
@section('title', 'Iniciar Ruta')
@push('styles')
<style>#mapa-inicio { height: 400px; }</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Iniciar Nueva Ruta</h1>

    <form id="iniciar-ruta-form" method="POST" action="{{ route('chofer.rutas.store') }}">
        @csrf
        <input type="hidden" name="latitud_inicio" id="latitud_inicio">
        <input type="hidden" name="longitud_inicio" id="longitud_inicio">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Vehículo</label>
                <select name="vehicle_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Seleccionar vehículo</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->placa }} - {{ $v->marca }} {{ $v->modelo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tu Ubicación</label>
                <div id="mapa-inicio" class="rounded-lg border border-gray-300 dark:border-gray-600"></div>
                <p id="ubicacion-status" class="text-sm text-gray-500 mt-1">Obteniendo ubicación...</p>
            </div>

            <button type="submit" id="btn-iniciar" disabled
                    class="w-full py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50">
                Iniciar Ruta
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('mapa-inicio').setView([18.4861, -69.9312], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '© OpenStreetMap'
}).addTo(map);

const marker = L.marker([18.4861, -69.9312], { draggable: true }).addTo(map);
const statusEl = document.getElementById('ubicacion-status');
const btn = document.getElementById('btn-iniciar');
const latInput = document.getElementById('latitud_inicio');
const lngInput = document.getElementById('longitud_inicio');

function setPosition(lat, lng) {
    marker.setLatLng([lat, lng]);
    map.setView([lat, lng], 15);
    latInput.value = lat;
    lngInput.value = lng;
    btn.disabled = false;
    statusEl.textContent = 'Ubicación lista';
}

marker.on('dragend', () => {
    const pos = marker.getLatLng();
    setPosition(pos.lat, pos.lng);
});

navigator.geolocation.getCurrentPosition(
    (pos) => setPosition(pos.coords.latitude, pos.coords.longitude),
    () => { statusEl.textContent = 'Usa el marcador para señalar tu ubicación'; btn.disabled = false; },
    { enableHighAccuracy: true, timeout: 10000 }
);
</script>
@endpush
