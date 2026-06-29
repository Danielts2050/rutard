@extends('layouts.chofer')
@section('title', 'Ruta Activa')
@push('styles')
<style>
    #mapa-activo { height: calc(100vh - 310px); min-height: 350px; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
    .leaflet-container { background: var(--bg-body); }
</style>
@endpush

@section('content')
<div class="gps-header">
    <div>
        <h1>Ruta Activa</h1>
        <p>
            {{ $ruta->vehiculo->placa ?? 'N/A' }} &middot;
            Inicio: {{ \Carbon\Carbon::parse($ruta->hora_inicio)->format('H:i:s') }}
        </p>
    </div>
    <div class="gps-timer">
        <div class="time" id="timer">00:00:00</div>
        <div class="label">Tiempo transcurrido</div>
    </div>
</div>

<div class="stats-row">
    <div class="stat-box">
        <div class="value" id="speed">0.0</div>
        <div class="label">km/h</div>
    </div>
    <div class="stat-box">
        <div class="value" id="distancia">0.0</div>
        <div class="label">km</div>
    </div>
    <div class="stat-box">
        <div class="value" id="precision">0</div>
        <div class="label">&plusmn;m</div>
    </div>
</div>

<div class="map-container mt-4">
    <div id="mapa-activo"></div>
</div>

<div class="map-controls">
    <button id="btn-finalizar" onclick="confirmarFinalizar()" class="btn btn-danger btn-block">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
        Finalizar Ruta
    </button>
    <button id="btn-recenter" onclick="recentrar()" class="btn btn-green btn-block">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v4m0 12v4m10-10h-4M2 12h4"/></svg>
        Centrar
    </button>
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
(function() {
    var rutaId = {{ $ruta->id }};
    var startTime = new Date("{{ \Carbon\Carbon::parse($ruta->hora_inicio)->format('Y/m/d H:i:s') }}").getTime();

    var map = L.map('mapa-activo').setView([18.4861, -69.9312], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    var userMarker = L.circleMarker([18.4861, -69.9312], {
        radius: 8, fillColor: '#4ade80', color: '#fff', weight: 2, fillOpacity: 1
    }).addTo(map);

    var accuracyCircle = L.circle([18.4861, -69.9312], { radius: 10, color: '#4ade80', fillOpacity: 0.08 });
    var pathLine = L.polyline([], { color: '#4ade80', weight: 3, opacity: 0.8 }).addTo(map);
    var positions = [];
    var totalKm = 0;

    function updatePosition(pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        var accuracy = pos.coords.accuracy || 10;
        var speed = pos.coords.speed || 0;

        userMarker.setLatLng([lat, lng]);
        accuracyCircle.setLatLng([lat, lng]).setRadius(accuracy);
        positions.push([lat, lng]);
        pathLine.setLatLngs(positions);
        document.getElementById('speed').textContent = (speed * 3.6).toFixed(1);
        document.getElementById('precision').textContent = Math.round(accuracy);

        if (positions.length > 1) {
            var prev = positions[positions.length - 2];
            var d = haversine(prev[0], prev[1], lat, lng);
            totalKm += d;
            document.getElementById('distancia').textContent = totalKm.toFixed(1);
        }

        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch('/chofer/rutas/' + rutaId + '/ubicaciones', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
            body: JSON.stringify({ latitud: lat, longitud: lng })
        }).catch(function() {});
    }

    navigator.geolocation.watchPosition(updatePosition, function() {}, {
        enableHighAccuracy: true, maximumAge: 5000, timeout: 10000
    });

    function haversine(lat1, lon1, lat2, lon2) {
        var R = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    window.recentrar = function() { map.setView(userMarker.getLatLng(), 15); };

    window.confirmarFinalizar = function() {
        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('latitud_fin').value = pos.coords.latitude;
            document.getElementById('longitud_fin').value = pos.coords.longitude;
            document.getElementById('km_recorridos').value = totalKm.toFixed(2);
            document.getElementById('finalizar-form').submit();
        }, function() {
            var p = userMarker.getLatLng();
            document.getElementById('latitud_fin').value = p.lat;
            document.getElementById('longitud_fin').value = p.lng;
            document.getElementById('km_recorridos').value = totalKm.toFixed(2);
            document.getElementById('finalizar-form').submit();
        }, { enableHighAccuracy: true, timeout: 5000 });
    };

    function updateTimer() {
        var elapsed = Math.floor((Date.now() - startTime) / 1000);
        var h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
        var m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
        var s = String(elapsed % 60).padStart(2, '0');
        document.getElementById('timer').textContent = h + ':' + m + ':' + s;
    }
    setInterval(updateTimer, 1000);
    updateTimer();

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
})();
</script>
@endpush
