<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertConfig extends Model
{
    protected $table = 'alert_configs';

    protected $fillable = [
        'clave',
        'nombre',
        'valor',
        'tipo',
        'descripcion',
    ];

    public static function valor(string $clave, mixed $default = null): mixed
    {
        $config = static::where('clave', $clave)->first();
        if (!$config) {
            return $default;
        }

        return match ($config->tipo) {
            'integer' => (int) $config->valor,
            'float'   => (float) $config->valor,
            'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
            default   => $config->valor,
        };
    }
}
