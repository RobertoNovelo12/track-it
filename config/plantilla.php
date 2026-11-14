<?php

/*
|--------------------------------------------------------------------------
| Plantilla de la instalación
|--------------------------------------------------------------------------
|
| Por defecto se usa la plantilla guardada en configuracion_instalacion,
| que se elige en el asistente de configuración inicial (al restablecer).
| TRACKIT_PLANTILLA (opcional) la fija para un despliegue: si existe,
| tiene prioridad y el asistente ya no pregunta. Valores: hoteles, escuelas.
|
*/

return [
    'forzada' => env('TRACKIT_PLANTILLA'),
];