<?php

/*
|--------------------------------------------------------------------------
| Plantilla de la instalación
|--------------------------------------------------------------------------
|
| Por defecto se usa la plantilla guardada en configuracion_instalacion.
| TRACKIT_PLANTILLA permite que un despliegue use otra (por ejemplo,
| «escuelas» para la versión institución) aunque comparta la misma base.
| Valores: hoteles, escuelas.
|
*/

return [
    'forzada' => env('TRACKIT_PLANTILLA'),
];