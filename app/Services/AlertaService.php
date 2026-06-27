<?php

namespace App\Services;

use App\Models\AlertConfig;
use App\Models\Alerta;
use App\Models\Ruta;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertaService
{
    public function __construct(
        private readonly FcmService $fcm
    ) {}

    public function evaluarTodas(): void
    {
        Ruta::where('estado', 'activa')
            ->with('chofer', 'vehiculo')
            ->chunk(50, function ($rutas) {
                foreach ($rutas as $ruta) {
                    try {
                        $this->evaluarRuta($ruta);
                    } catch (\Throwable $e) {
                        Log::error("Error evaluando ruta #{$ruta->id}: {$e->getMessage()}");
                    }
                }
            });

        $this->evaluarMantenimiento();
    }

    public function evaluarRuta(Ruta $ruta): void
    {
        $ultima = Ubicacion::where('ruta_id', $ruta->id)
            ->orderByDesc('fecha_hora')
            ->first();

        if (!$ultima) {
            return;
        }

        $this->evaluarVehiculoDetenido($ruta, $ultima);
        $this->evaluarExcesoVelocidad($ruta, $ultima);
        $this->evaluarSalidaRuta($ruta, $ultima);
    }

    public function evaluarRutaFinalizada(Ruta $ruta): void
    {
        $yaAlertada = Alerta::where('ruta_id', $ruta->id)
            ->where('tipo', 'ruta_finalizada')
            ->exists();

        if ($yaAlertada) {
            return;
        }

        $chofer = $ruta->chofer;
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'Administrador'))->first();

        $titulo = "Ruta finalizada #{$ruta->id}";
        $mensaje = "El chofer {$chofer?->name} finalizó la ruta en {$ruta->vehiculo?->placa}. Duración: {$ruta->duracion_minutos} min.";

        $this->crearAlerta('ruta_finalizada', $titulo, $mensaje, $ruta, [
            'duracion_minutos' => $ruta->duracion_minutos,
        ], $admin?->id);

        $this->fcm->sendToUser(
            $admin?->id,
            $titulo,
            $mensaje,
            ['tipo' => 'ruta_finalizada', 'ruta_id' => (string) $ruta->id]
        );
    }

    private function evaluarVehiculoDetenido(Ruta $ruta, Ubicacion $ultima): void
    {
        $minutosDetenido = AlertConfig::valor('minutos_detenido', config('alertas.defaults.minutos_detenido'));

        $haceNMinutos = now()->subMinutes($minutosDetenido);
        $ultimaConMovimiento = Ubicacion::where('ruta_id', $ruta->id)
            ->where('fecha_hora', '>=', $haceNMinutos)
            ->where('velocidad', '>', 0)
            ->exists();

        if (!$ultimaConMovimiento) {
            $yaAlertada = Alerta::where('ruta_id', $ruta->id)
                ->where('tipo', 'vehiculo_detenido')
                ->where('created_at', '>=', now()->subHour())
                ->exists();

            if (!$yaAlertada) {
                $this->crearAlerta(
                    'vehiculo_detenido',
                    'Vehículo detenido',
                    "El vehículo {$ruta->vehiculo?->placa} está detenido desde hace más de {$minutosDetenido} min en {$ultima->latitud}, {$ultima->longitud}.",
                    $ruta,
                    ['latitud' => $ultima->latitud, 'longitud' => $ultima->longitud]
                );
            }
        }
    }

    private function evaluarExcesoVelocidad(Ruta $ruta, Ubicacion $ultima): void
    {
        if (empty($ultima->velocidad)) {
            return;
        }

        $velocidadMaxima = AlertConfig::valor('velocidad_maxima', config('alertas.defaults.velocidad_maxima'));

        if ($ultima->velocidad > $velocidadMaxima) {
            $yaAlertada = Alerta::where('ruta_id', $ruta->id)
                ->where('tipo', 'exceso_velocidad')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$yaAlertada) {
                $this->crearAlerta(
                    'exceso_velocidad',
                    'Exceso de velocidad',
                    "Vehículo {$ruta->vehiculo?->placa} a {$ultima->velocidad} km/h (límite: {$velocidadMaxima} km/h).",
                    $ruta,
                    ['velocidad' => $ultima->velocidad, 'limite' => $velocidadMaxima]
                );
            }
        }
    }

    private function evaluarSalidaRuta(Ruta $ruta, Ubicacion $ultima): void
    {
        $radioKm = AlertConfig::valor('radio_geocerca_km', config('alertas.defaults.radio_geocerca_km'));
        $latInicio = $ruta->latitud_inicio;
        $lngInicio = $ruta->longitud_inicio;

        $distancia = $this->haversine(
            $latInicio, $lngInicio,
            $ultima->latitud, $ultima->longitud
        );

        if ($distancia > $radioKm) {
            $yaAlertada = Alerta::where('ruta_id', $ruta->id)
                ->where('tipo', 'salida_ruta')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();

            if (!$yaAlertada) {
                $this->crearAlerta(
                    'salida_ruta',
                    'Salida de ruta',
                    "El vehículo {$ruta->vehiculo?->placa} está a {$distancia} km del punto de inicio.",
                    $ruta,
                    ['distancia_km' => round($distancia, 2), 'radio_km' => $radioKm]
                );
            }
        }
    }

    private function evaluarMantenimiento(): void
    {
        $diasMantenimiento = AlertConfig::valor('dias_mantenimiento', config('alertas.defaults.dias_mantenimiento'));

        Vehicle::where('estado', 'activo')
            ->whereDoesntHave('rutas', fn($q) => $q->where('created_at', '>=', now()->subDays($diasMantenimiento)))
            ->chunk(50, function ($vehiculos) use ($diasMantenimiento) {
                foreach ($vehiculos as $vehicle) {
                    $yaAlertada = Alerta::where('vehicle_id', $vehicle->id)
                        ->where('tipo', 'proximo_mantenimiento')
                        ->where('created_at', '>=', now()->subDays(1))
                        ->exists();

                    if (!$yaAlertada) {
                        $this->crearAlerta(
                            'proximo_mantenimiento',
                            'Mantenimiento próximo',
                            "El vehículo {$vehicle->placa} requiere mantenimiento (sin rutas en los últimos {$diasMantenimiento} días).",
                            null,
                            ['vehicle_id' => $vehicle->id, 'dias_inactivo' => $diasMantenimiento]
                        );
                    }
                }
            });
    }

    private function crearAlerta(
        string $tipo,
        string $titulo,
        string $mensaje,
        ?Ruta $ruta = null,
        array $metadata = [],
        ?int $userId = null
    ): Alerta {
        if (!$userId) {
            $admin = User::whereHas('role', fn($q) => $q->where('name', 'Administrador'))->first();
            $userId = $admin?->id;
        }

        $alerta = Alerta::create([
            'user_id' => $userId,
            'ruta_id' => $ruta?->id,
            'vehicle_id' => $ruta?->vehiculo_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'metadata' => $metadata,
            'fecha_alerta' => now(),
        ]);

        return $alerta;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
