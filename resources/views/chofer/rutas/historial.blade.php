@extends('layouts.chofer')
@section('title', 'Historial')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold">Historial de Rutas</h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Vehículo</th>
                    <th class="px-4 py-3 text-right">Duración</th>
                    <th class="px-4 py-3 text-right">Min</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($rutas as $r)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($r->hora_inicio)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $r->vehiculo->placa ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-right font-mono">
                        @php
                            $inicio = \Carbon\Carbon::parse($r->hora_inicio);
                            $fin = $r->hora_fin ? \Carbon\Carbon::parse($r->hora_fin) : null;
                        @endphp
                        {{ $fin ? $inicio->diff($fin)->format('%H:%I:%S') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right">{{ $r->duracion_minutos ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('chofer.rutas.detalle', $r) }}" class="text-blue-600 hover:underline">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay rutas finalizadas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $rutas->links() }}
</div>
@endsection
