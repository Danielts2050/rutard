<?php

namespace App\Console\Commands;

use App\Services\AlertaService;
use Illuminate\Console\Command;

class EvaluarAlertas extends Command
{
    protected $signature = 'alertas:evaluar';
    protected $description = 'Evalúa todas las condiciones de alertas (detenido, velocidad, salida ruta, mantenimiento)';

    public function handle(AlertaService $alertaService): int
    {
        $this->info('Evaluando condiciones de alertas...');
        $alertaService->evaluarTodas();
        $this->info('Evaluación completada.');

        return Command::SUCCESS;
    }
}
