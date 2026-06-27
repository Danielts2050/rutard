<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    */
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY', ''),
        'enabled' => env('FCM_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros por defecto para alertas
    |--------------------------------------------------------------------------
    | Estos valores se usan si no existen registros en alert_configs.
    */
    'defaults' => [
        'minutos_detenido' => 5,
        'velocidad_maxima' => 100,
        'radio_geocerca_km' => 2,
        'dias_mantenimiento' => 7,
        'intervalo_evaluacion_minutos' => 2,
    ],
];
