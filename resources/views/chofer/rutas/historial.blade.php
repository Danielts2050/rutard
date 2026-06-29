@extends('layouts.chofer')
@section('title', 'Historial')

@section('content')
<div class="section-header">
    <h1 class="section-title">Historial de Rutas</h1>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Vehiculo</th>
                <th class="text-right">Duracion</th>
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
                <td><span class="badge badge-blue">{{ $r->vehiculo->placa ?? 'N/A' }}</span></td>
                <td class="text-right font-mono">{{ $fin ? $inicio->diff($fin)->format('%H:%I:%S') : '-' }}</td>
                <td class="text-right">{{ $r->duracion_minutos ?? '-' }}</td>
                <td class="text-right">
                    <a href="{{ route('chofer.rutas.detalle', $r) }}" class="text-green" style="font-weight:500;">Ver</a>
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
@endsection
