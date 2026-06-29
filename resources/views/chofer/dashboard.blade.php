@extends('layouts.chofer')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    @if($rutaActiva)
        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">Ruta Activa</h2>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        Vehículo: {{ $rutaActiva->vehiculo->placa ?? 'N/A' }}<br>
                        Iniciada: {{ \Carbon\Carbon::parse($rutaActiva->hora_inicio)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <a href="{{ route('chofer.rutas.activa', $rutaActiva) }}"
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Ir a Ruta Activa
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-blue-600">{{ $stats['rutas_hoy'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Rutas hoy</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-green-600">{{ number_format($stats['km_hoy'], 1) }} km</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Kilómetros hoy</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-purple-600">{{ $stats['rutas_semana'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Rutas esta semana</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('chofer.rutas.iniciar') }}"
           class="flex items-center justify-center p-8 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition text-center">
            <div>
                <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-lg font-semibold">Iniciar Ruta</span>
            </div>
        </a>
        <a href="{{ route('chofer.rutas.historial') }}"
           class="flex items-center justify-center p-8 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition text-center">
            <div>
                <svg class="w-12 h-12 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-lg font-semibold">Historial</span>
            </div>
        </a>
    </div>
</div>
@endsection
