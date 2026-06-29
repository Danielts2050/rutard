@extends('layouts.admin')

@section('title', 'Vehículos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-truck"></i> Vehículos</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Capacidad</th>
                    <th>Estado</th>
                    <th>Chofer</th>
                    <th>Rutas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vehicles as $vehicle)
                    <tr>
                        <td><strong>{{ $vehicle->placa }}</strong></td>
                        <td>{{ $vehicle->marca }}</td>
                        <td>{{ $vehicle->modelo }}</td>
                        <td>{{ $vehicle->anio }}</td>
                        <td>{{ $vehicle->capacidad }}</td>
                        <td>
                            <span class="badge bg-{{ $vehicle->estado === 'activo' ? 'success' : ($vehicle->estado === 'mantenimiento' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($vehicle->estado) }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.vehicles.assign', $vehicle) }}" class="d-flex align-items-center gap-2">
                                @csrf
                                <select name="chofer_id" class="form-select form-select-sm" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                                    <option value="">Sin chofer</option>
                                    @foreach($choferes as $ch)
                                        <option value="{{ $ch->id }}" {{ $vehicle->chofer_id == $ch->id ? 'selected' : '' }}>
                                            {{ $ch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>{{ $vehicle->rutas()->count() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No hay vehículos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $vehicles->links() }}
@endsection
