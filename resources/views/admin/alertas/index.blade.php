@extends('layouts.admin')

@section('title', 'Historial de Alertas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-bell"></i> Historial de Alertas</h1>
    <form method="POST" action="{{ route('admin.alertas.marcar-todas-leidas') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-success btn-sm">
            <i class="bi bi-check-all"></i> Marcar todas leídas
        </button>
    </form>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t }}" {{ request('tipo') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" name="q" class="form-control" placeholder="Título o mensaje" value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="no_leidas" class="form-select">
                    <option value="">Todas</option>
                    <option value="1" {{ request('no_leidas') === '1' ? 'selected' : '' }}>No leídas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
                <a href="{{ route('admin.alertas.index') }}" class="btn btn-outline-secondary ms-1"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse ($alertas as $alerta)
            <div class="border-bottom p-3 {{ $alerta->leida ? '' : 'bg-light' }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            @if (!$alerta->leida)
                                <span class="badge bg-danger rounded-pill" style="width:8px;height:8px;padding:0;">&nbsp;</span>
                            @endif
                            <strong>{{ $alerta->titulo }}</strong>
                            <span class="badge bg-secondary">{{ str_replace('_', ' ', $alerta->tipo) }}</span>
                        </div>
                        <p class="mb-1 text-secondary">{{ $alerta->mensaje }}</p>
                        <small class="text-muted">
                            {{ $alerta->fecha_alerta->format('d/m/Y H:i:s') }}
                            @if ($alerta->user)
                                &middot; {{ $alerta->user->name }}
                            @endif
                            @if ($alerta->vehicle)
                                &middot; {{ $alerta->vehicle->placa }}
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-1">
                        @if (!$alerta->leida)
                            <form method="POST" action="{{ route('admin.alertas.marcar-leida', $alerta) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marcar leída">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.alertas.destroy', $alerta) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar esta alerta?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-secondary">No hay alertas registradas.</div>
        @endforelse
    </div>
</div>

{{ $alertas->links() }}
@endsection
