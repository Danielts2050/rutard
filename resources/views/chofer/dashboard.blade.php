@extends('layouts.chofer')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    @if($rutaActiva)
        <div class="active-banner">
            <div>
                <h2>Ruta Activa</h2>
                <p>
                    Vehiculo: {{ $rutaActiva->vehiculo->placa ?? 'N/A' }} &middot;
                    Iniciada: {{ \Carbon\Carbon::parse($rutaActiva->hora_inicio)->format('d/m/Y H:i') }}
                </p>
            </div>
            <a href="{{ route('chofer.rutas.activa', $rutaActiva) }}" class="btn btn-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Ir a Ruta Activa
            </a>
        </div>
    @endif

    <div class="grid-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            </div>
            <div>
                <div class="stat-number">{{ $stats['rutas_hoy'] }}</div>
                <div class="stat-label">Rutas hoy</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="stat-number">{{ $stats['km_hoy'] }} <span class="text-sm text-gray">min</span></div>
                <div class="stat-label">Tiempo hoy</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            </div>
            <div>
                <div class="stat-number">{{ $stats['rutas_semana'] }}</div>
                <div class="stat-label">Rutas semana</div>
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top: 1.25rem;">
        <a href="{{ route('chofer.rutas.iniciar') }}" class="action-card">
            <div class="icon-circle green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div class="action-text">Iniciar Ruta</div>
        </a>
        <a href="{{ route('chofer.rutas.historial') }}" class="action-card">
            <div class="icon-circle blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            </div>
            <div class="action-text">Historial</div>
        </a>
    </div>
</div>
@endsection
