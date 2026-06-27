@extends('layouts.admin')

@section('title', 'Historial de Rutas')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
@endpush

@section('content')
<h1 class="mb-4"><i class="bi bi-clock-history"></i> Historial de Rutas</h1>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Chofer</label>
                <input type="text" name="chofer" class="form-control" placeholder="Nombre" value="{{ request('chofer') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vehículo (placa)</label>
                <input type="text" name="vehiculo" class="form-control" placeholder="Placa" value="{{ request('vehiculo') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
                <a href="{{ route('admin.rutas.historial') }}" class="btn btn-outline-secondary ms-1"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Chofer</th>
                    <th>Vehículo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Duración</th>
                    <th>Puntos GPS</th>
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
                        <td>{{ $ruta->hora_fin?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $ruta->duracion_minutos ? "{$ruta->duracion_minutos} min" : '—' }}</td>
                        <td>{{ $ruta->ubicaciones()->count() }}</td>
                        <td>
                            <a href="{{ route('admin.rutas.detalle', $ruta) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-map"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No se encontraron rutas finalizadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $rutas->links() }}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr('input[type="date"]', {
        locale: 'es',
        dateFormat: 'Y-m-d',
        allowInput: true
    });
});
</script>
@endpush
