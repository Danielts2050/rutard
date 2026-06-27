<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ruta #{{ $ruta->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 1em 0; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .info { display: flex; justify-content: space-between; margin: 1em 0; }
        .info div { flex: 1; }
        .info strong { display: block; color: #666; font-size: 10px; }
        .info span { font-size: 14px; }
    </style>
</head>
<body>
    <h1>Reporte de Ruta #{{ $ruta->id }}</h1>

    <div class="info">
        <div><strong>Chofer</strong><span>{{ $ruta->chofer?->name ?? '—' }}</span></div>
        <div><strong>Vehículo</strong><span>{{ $ruta->vehiculo?->placa ?? '—' }}</span></div>
        <div><strong>Inicio</strong><span>{{ $ruta->hora_inicio?->format('d/m/Y H:i:s') ?? '—' }}</span></div>
        <div><strong>Fin</strong><span>{{ $ruta->hora_fin?->format('d/m/Y H:i:s') ?? '—' }}</span></div>
        <div><strong>Duración</strong><span>{{ $ruta->duracion_minutos ? "{$ruta->duracion_minutos} min" : '—' }}</span></div>
    </div>

    <h3>Puntos de ubicación ({{ $ruta->ubicaciones->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Latitud</th>
                <th>Longitud</th>
                <th>Velocidad</th>
                <th>Fecha/Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ruta->ubicaciones->sortBy('fecha_hora') as $i => $u)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $u->latitud }}</td>
                    <td>{{ $u->longitud }}</td>
                    <td>{{ $u->velocidad ?? '—' }}</td>
                    <td>{{ $u->fecha_hora?->format('d/m/Y H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align:center;color:#999;margin-top:2em;">
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </p>
</body>
</html>
