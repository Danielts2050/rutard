@extends('layouts.chofer')
@section('title', 'Iniciar Ruta')
@push('styles')
<style>#mapa-inicio { height: 400px; border-radius: 0.5rem; border: 1px solid #d1d5db; }</style>
@endpush

@section('content')
<div class="container" style="max-width: 42rem;">
    <h1 class="font-bold" style="font-size: 1.5rem; margin-bottom: 1.5rem;">Iniciar Nueva Ruta</h1>

    <form id="iniciar-ruta-form" method="POST" action="{{ route('chofer.rutas.store') }}">
        @csrf
        <input type="hidden" name="latitud_inicio" id="latitud_inicio">
        <input type="hidden" name="longitud_inicio" id="longitud_inicio">

        <div class="card" style="padding: 1.5rem;">
            <div class="form-group">
                <label for="vehicle_id" class="form-label">Vehículo</label>
                <select name="vehicle_id" id="vehicle_id" required class="form-select">
                    <option value="">Seleccionar vehículo</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->placa }} - {{ $v->marca }} {{ $v->modelo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tu Ubicación</label>
                <div id="mapa-inicio"></div>
                <p id="ubicacion-status" class="text-sm text-gray mt-1">Obteniendo ubicación...</p>
            </div>

            <button type="submit" id="btn-iniciar" disabled
                    class="btn btn-success btn-block">
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
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap'
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
        statusEl.textContent = 'Ubicación lista';
    }

    marker.on('dragend', function() {
        var pos = marker.getLatLng();
        setPosition(pos.lat, pos.lng);
    });

    navigator.geolocation.getCurrentPosition(
        function(pos) { setPosition(pos.coords.latitude, pos.coords.longitude); },
        function() {
            statusEl.textContent = 'Usa el marcador para señalar tu ubicación';
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
})();
</script>
@endpush
