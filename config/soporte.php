<?php

/*
|--------------------------------------------------------------------------
| Contacto de soporte (Centro de ayuda)
|--------------------------------------------------------------------------
|
| Datos del proveedor del sistema (quien da el soporte). No se editan
| desde la aplicación: ni el administrador de la empresa puede cambiar
| quién le brinda el servicio. Solo el proveedor los cambia, aquí o con
| variables de entorno en el servidor (Render › Environment).
|
*/

return [
    'equipo' => env('SOPORTE_EQUIPO', 'Team Track-IT'),
    'integrantes' => env('SOPORTE_INTEGRANTES', 'Jorge Novelo y Alan Colli'),
    'correo' => env('SOPORTE_CORREO', 'trackit.ayuda@gmail.com'),
    'telefono' => env('SOPORTE_TELEFONO', ''),
    'horario' => env('SOPORTE_HORARIO', '24/7'),
];
