@extends('layouts.admin')

@section('title', 'Configuración de Alertas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-gear"></i> Configuración de Alertas</h1>
    @if ($configs->isEmpty())
        <form method="POST" action="{{ route('admin.alertas.config.seed') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-download"></i> Cargar valores por defecto
            </button>
        </form>
    @endif
</div>

@if ($configs->isEmpty())
    <div class="alert alert-info">
        No hay configuraciones de alertas. Haz clic en "Cargar valores por defecto" para inicializarlas.
    </div>
@else
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.alertas.config.update') }}">
                @csrf

                @foreach ($configs as $config)
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-bold mb-0">{{ $config->nombre }}</label>
                            <br><small class="text-secondary">{{ $config->descripcion }}</small>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="configs[{{ $loop->index }}][clave]" value="{{ $config->clave }}">
                            @if ($config->tipo === 'boolean')
                                <select name="configs[{{ $loop->index }}][valor]" class="form-select">
                                    <option value="1" {{ $config->valor === '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ $config->valor === '0' ? 'selected' : '' }}>No</option>
                                </select>
                            @else
                                <input type="{{ $config->tipo === 'integer' ? 'number' : ($config->tipo === 'float' ? 'number' : 'text') }}"
                                       name="configs[{{ $loop->index }}][valor]"
                                       class="form-control"
                                       value="{{ $config->valor }}"
                                       {{ $config->tipo === 'float' ? 'step="0.1"' : '' }}
                                       required>
                            @endif
                        </div>
                        <div class="col-md-1">
                            <span class="badge bg-secondary">{{ $config->clave }}</span>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr>
                    @endif
                @endforeach

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar configuraciones
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
