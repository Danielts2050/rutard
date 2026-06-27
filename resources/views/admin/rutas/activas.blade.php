@extends('layouts.admin')

@section('title', 'Rutas Activas')

@section('content')
<h1 class="mb-4"><i class="bi bi-play-circle"></i> Rutas Activas</h1>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Chofer</th>
                    <th>Vehículo</th>
                    <th>Inicio</th>
                    <th>Lat / Lng Inicio</th>
                    <th>Duración</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rutas as $ruta)
                    <tr>
                        <td>{{ $ruta->id }}</td>
                        <td>{{ $ruta->chofer?->name ?? '—' }}</td>
                        <td>{{ $ruta->vehiculo?->placa ?? '—' }}</td>
                        <td>{{ $ruta->hora_inicio?->format('d/m/Y H:i') }}</td>
                        <td>
                            <small>
                                {{ number_format($ruta->latitud_inicio, 5) }},
                                {{ number_format($ruta->longitud_inicio, 5) }}
                            </small>
                        </td>
                        <td>
                            @if ($ruta->hora_inicio)
                                {{ $ruta->hora_inicio->diffForHumans(now(), true) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.rutas.detalle', $ruta) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-map"></i> Ver mapa
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No hay rutas activas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $rutas->links() }}
@endsection
