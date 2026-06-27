<?php

namespace App\Models;

use Database\Factories\RutaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruta extends Model
{
    /** @use HasFactory<RutaFactory> */
    use HasFactory;
    protected $fillable = [
        'chofer_id',
        'vehiculo_id',
        'hora_inicio',
        'latitud_inicio',
        'longitud_inicio',
        'hora_fin',
        'latitud_fin',
        'longitud_fin',
        'duracion_minutos',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
            'latitud_inicio' => 'float',
            'longitud_inicio' => 'float',
            'latitud_fin' => 'float',
            'longitud_fin' => 'float',
        ];
    }

    public function chofer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chofer_id');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehiculo_id');
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class);
    }
}
