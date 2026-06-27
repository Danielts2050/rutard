@extends('layouts.admin')

@section('title', $vehicle->placa)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ $vehicle->placa }}</h1>
    <div>
        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th class="w-25">Placa</th>
                        <td>{{ $vehicle->placa }}</td>
                    </tr>
                    <tr>
                        <th>Marca</th>
                        <td>{{ $vehicle->marca }}</td>
                    </tr>
                    <tr>
                        <th>Modelo</th>
                        <td>{{ $vehicle->modelo }}</td>
                    </tr>
                    <tr>
                        <th>Año</th>
                        <td>{{ $vehicle->anio }}</td>
                    </tr>
                    <tr>
                        <th>Capacidad</th>
                        <td>{{ $vehicle->capacidad }} pasajeros</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <span class="badge bg-{{ $vehicle->estado === 'activo' ? 'success' : ($vehicle->estado === 'mantenimiento' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($vehicle->estado) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Chofer</th>
                        <td>{{ $vehicle->chofer?->name ?? '— Sin asignar —' }}</td>
                    </tr>
                    <tr>
                        <th>Registrado</th>
                        <td>{{ $vehicle->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
