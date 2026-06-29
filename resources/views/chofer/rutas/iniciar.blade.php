@extends('layouts.chofer')
@section('title', 'Iniciar Ruta')
@push('styles')
<style>
    #mapa-inicio { height: 400px; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
    .leaflet-container { background: var(--bg-body); }
</style>
@endpush

@section('content')
<div style="max-width: 42rem; margin: 0 auto;">
    <div class="section-header">
        <h1 class="section-title">Iniciar Nueva Ruta</h1>
    </div>

    <form id="iniciar-ruta-form" method="POST" action="{{ route('chofer.rutas.store') }}">
        @csrf
        <input type="hidden" name="latitud_inicio" id="latitud_inicio">
        <input type="hidden" name="longitud_inicio" id="longitud_inicio">

        <div class="card">
            <div class="form-group">
                <label for="vehicle_id" class="form-label">Vehiculo</label>
                <select name="vehicle_id" id="vehicle_id" required class="form-select">
                    <option value="">Seleccionar vehiculo</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->placa }} - {{ $v->marca }} {{ $v->modelo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tu Ubicacion</label>
                <div id="mapa-inicio"></div>
                <p id="ubicacion-status" class="text-sm text-gray mt-1">Obteniendo ubicacion...</p>
            </div>

            <button type="submit" id="btn-iniciar" disabled class="btn btn-green btn-block" style="margin-top: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Iniciar Ruta
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var map = L.map('mapa-inicio').setView([18.4861, -69.9312], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    var marker = L.marker([18.4861, -69.9312], { draggable: true }).addTo(map);
    var statusEl = document.getElementById('ubicacion-status');
    var btn = document.getElementById('btn-iniciar');
    var latInput = document.getElementById('latitud_inicio');
    var lngInput = document.getElementById('longitud_inicio');

    function setPosition(lat, lng) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 15);
        latInput.value = lat;
        lngInput.value = lng;
        btn.disabled = false;
        statusEl.textContent = 'Ubicacion lista';
        statusEl.style.color = '#4ade80';
    }

    marker.on('dragend', function() {
        var pos = marker.getLatLng();
        setPosition(pos.lat, pos.lng);
    });

    navigator.geolocation.getCurrentPosition(
        function(pos) { setPosition(pos.coords.latitude, pos.coords.longitude); },
        function() {
            statusEl.textContent = 'Arrastra el marcador para senalar tu ubicacion';
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
})();
</script>
@endpush
