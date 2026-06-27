<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Días de retención de ubicaciones GPS
    |--------------------------------------------------------------------------
    |
    | Define cuántos días se conservan los registros de ubicación antes de
    | ser eliminados automáticamente por el comando `rastreo:limpiar`.
    |
    */
    'retencion_dias' => env('RASTREO_RETENCION_DIAS', 30),

    /*
    |--------------------------------------------------------------------------
    | Tamaño de lote para inserciones masivas
    |--------------------------------------------------------------------------
    |
    | Cantidad de registros que se insertan por lote en las operaciones
    | de guardado masivo de ubicaciones.
    |
    */
    'lote_insercion' => env('RASTREO_LOTE_INSERCION', 100),
];
