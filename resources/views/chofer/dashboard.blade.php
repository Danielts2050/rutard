@extends('layouts.chofer')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    @if($rutaActiva)
        <div class="active-banner">
            <div>
                <h2>Ruta Activa</h2>
                <p>
                    Vehículo: {{ $rutaActiva->vehiculo->placa ?? 'N/A' }}<br>
                    Iniciada: {{ \Carbon\Carbon::parse($rutaActiva->hora_inicio)->format('d/m/Y H:i') }}
                </p>
            </div>
            <a href="{{ route('chofer.rutas.activa', $rutaActiva) }}" class="btn btn-warning">
                Ir a Ruta Activa
            </a>
        </div>
    @endif

    <div class="grid-3">
        <div class="stat-card">
            <div class="stat-number blue">{{ $stats['rutas_hoy'] }}</div>
            <div class="stat-label">Rutas hoy</div>
        </div>
        <div class="stat-card">
            <div class="stat-number green">{{ $stats['km_hoy'] }}</div>
            <div class="stat-label">Minutos hoy</div>
        </div>
        <div class="stat-card">
            <div class="stat-number purple">{{ $stats['rutas_semana'] }}</div>
            <div class="stat-label">Rutas esta semana</div>
        </div>
    </div>

    <div class="grid-2" style="margin-top: 1.5rem;">
        <a href="{{ route('chofer.rutas.iniciar') }}" class="action-card">
            <div class="icon-green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <div class="action-text">Iniciar Ruta</div>
            </div>
        </a>
        <a href="{{ route('chofer.rutas.historial') }}" class="action-card">
            <div class="icon-blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <div class="action-text">Historial</div>
            </div>
        </a>
    </div>
</div>
@endsection
