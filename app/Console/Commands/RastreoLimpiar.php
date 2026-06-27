<?php

namespace App\Console\Commands;

use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RastreoLimpiar extends Command
{
    protected $signature = 'rastreo:limpiar
                            {--dias= : Días de retención (sobrescribe config)}';

    protected $description = 'Elimina ubicaciones GPS más antiguas que los días configurados';

    public function handle(): void
    {
        $dias = (int) ($this->option('dias') ?: config('rastreo.retencion_dias', 30));

        $fechaLimite = Carbon::now()->subDays($dias);
        $eliminados = Ubicacion::where('fecha_hora', '<', $fechaLimite)->delete();

        $this->info("Ubicaciones eliminadas: {$eliminados} (anteriores a {$fechaLimite->toDateTimeString()})");
    }
}
