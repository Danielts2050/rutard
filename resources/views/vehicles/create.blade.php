@extends('layouts.admin')

@section('title', 'Nuevo vehículo')

@section('content')
<h1>Nuevo vehículo</h1>

<form action="{{ route('vehicles.store') }}" method="POST">
    @csrf

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="placa" class="form-label">Placa</label>
            <input type="text" name="placa" id="placa" class="form-control @error('placa') is-invalid @enderror"
                   value="{{ old('placa') }}" maxlength="10" required>
            @error('placa') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror"
                   value="{{ old('marca') }}" required>
            @error('marca') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror"
                   value="{{ old('modelo') }}" required>
            @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <label for="anio" class="form-label">Año</label>
            <input type="number" name="anio" id="anio" class="form-control @error('anio') is-invalid @enderror"
                   value="{{ old('anio', date('Y')) }}" min="2000" max="{{ date('Y') + 1 }}" required>
            @error('anio') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="capacidad" class="form-label">Capacidad (pasajeros)</label>
            <input type="number" name="capacidad" id="capacidad" class="form-control @error('capacidad') is-invalid @enderror"
                   value="{{ old('capacidad') }}" min="1" max="99" required>
            @error('capacidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                <option value="activo" {{ old('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                <option value="mantenimiento" {{ old('estado') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
            </select>
            @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="chofer_id" class="form-label">Chofer</label>
            <select name="chofer_id" id="chofer_id" class="form-select @error('chofer_id') is-invalid @enderror">
                <option value="">— Sin asignar —</option>
                @foreach ($choferes as $chofer)
                    <option value="{{ $chofer->id }}" {{ old('chofer_id') == $chofer->id ? 'selected' : '' }}>
                        {{ $chofer->name }} ({{ $chofer->email }})
                    </option>
                @endforeach
            </select>
            @error('chofer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
