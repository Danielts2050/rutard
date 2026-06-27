@extends('layouts.admin')

@section('title', 'Exportar datos')

@section('content')
<h1 class="mb-4"><i class="bi bi-download"></i> Exportar datos</h1>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-filetype-pdf text-danger"></i> Exportar ruta individual (PDF)</div>
            <div class="card-body">
                <p class="text-secondary">Descarga un reporte en PDF de una ruta específica con todos los detalles, mapa y puntos de ubicación.</p>
                <form action="{{ route('admin.exports.pdf.post') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="ruta_id" class="form-control" placeholder="ID de la ruta" min="1" required>
                        <button type="submit" class="btn btn-danger">Generar PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-spreadsheet text-success"></i> Exportar historial (Excel)</div>
            <div class="card-body">
                <p class="text-secondary">Descarga un archivo Excel con todas las rutas finalizadas, filtrable por rango de fechas.</p>
                <form action="{{ route('admin.exports.excel') }}" method="GET" class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success w-100 mt-2">
                            <i class="bi bi-download"></i> Descargar Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
