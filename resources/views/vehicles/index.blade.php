@extends('layouts.admin')

@section('title', 'Vehículos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Vehículos</h1>
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">Nuevo vehículo</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Año</th>
                <th>Capacidad</th>
                <th>Estado</th>
                <th>Chofer</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->placa }}</td>
                    <td>{{ $vehicle->marca }}</td>
                    <td>{{ $vehicle->modelo }}</td>
                    <td>{{ $vehicle->anio }}</td>
                    <td>{{ $vehicle->capacidad }}</td>
                    <td>
                        <span class="badge bg-{{ $vehicle->estado === 'activo' ? 'success' : ($vehicle->estado === 'mantenimiento' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($vehicle->estado) }}
                        </span>
                    </td>
                    <td>{{ $vehicle->chofer?->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ $vehicle->placa }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay vehículos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $vehicles->links() }}
@endsection
