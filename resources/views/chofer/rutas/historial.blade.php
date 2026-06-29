@extends('layouts.chofer')
@section('title', 'Historial')

@section('content')
<div class="mb-6">
    <h1 class="font-bold" style="font-size: 1.5rem; margin-bottom: 1.5rem;">Historial de Rutas</h1>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Veh&iacute;culo</th>
                    <th class="text-right">Duraci&oacute;n</th>
                    <th class="text-right">Min</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rutas as $r)
                @php
                    $inicio = \Carbon\Carbon::parse($r->hora_inicio);
                    $fin = $r->hora_fin ? \Carbon\Carbon::parse($r->hora_fin) : null;
                @endphp
                <tr>
                    <td>{{ $inicio->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->vehiculo->placa ?? 'N/A' }}</td>
                    <td class="text-right font-mono">{{ $fin ? $inicio->diff($fin)->format('%H:%I:%S') : '-' }}</td>
                    <td class="text-right">{{ $r->duracion_minutos ?? '-' }}</td>
                    <td class="text-right">
                        <a href="{{ route('chofer.rutas.detalle', $r) }}" class="text-blue">Ver</a>
                    </td>
                </tr>
                @empty
                <tr class="table-empty">
                    <td colspan="5">No hay rutas finalizadas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $rutas->links() }}
    </div>
</div>
@endsection
