<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExportController extends Controller
{
    public function index(): View
    {
        return view('admin.exports.index');
    }

    public function pdf(Request $request, ?Ruta $ruta = null): Response|RedirectResponse
    {
        if (!$ruta && $request->has('ruta_id')) {
            $ruta = Ruta::findOrFail($request->ruta_id);
        }

        $ruta->load('chofer', 'vehiculo', 'ubicaciones');
        $pdf = Pdf::loadView('admin.exports.pdf', compact('ruta'));
        return $pdf->download("ruta-{$ruta->id}.pdf");
    }

    public function excel(Request $request): Response
    {
        $query = Ruta::with('chofer', 'vehiculo')->where('estado', 'finalizada');

        if ($request->filled('fecha_desde')) {
            $query->whereDate('hora_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('hora_inicio', '<=', $request->fecha_hasta);
        }

        $rutas = $query->latest('hora_inicio')->get();

        $writer = new Writer();
        $filename = "rutas-export-" . now()->format('Y-m-d-His') . ".xlsx";
        $filepath = storage_path("app/temp/{$filename}");

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer->openToFile($filepath);

        $header = Row::fromValues(['ID', 'Chofer', 'Vehículo', 'Inicio', 'Fin', 'Duración (min)', 'Distancia (km)']);
        $writer->addRow($header);

        foreach ($rutas as $ruta) {
            $row = Row::fromValues([
                $ruta->id,
                $ruta->chofer?->name ?? '—',
                $ruta->vehiculo?->placa ?? '—',
                $ruta->hora_inicio?->format('d/m/Y H:i'),
                $ruta->hora_fin?->format('d/m/Y H:i'),
                $ruta->duracion_minutos,
                '—',
            ]);
            $writer->addRow($row);
        }

        $writer->close();

        return response()->download($filepath, $filename)->deleteFileAfterSend();
    }
}
