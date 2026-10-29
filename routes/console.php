<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Notificaciones de mantenimientos próximos y vencidos
|--------------------------------------------------------------------------
|
| Requiere que el programador de tareas de Laravel esté en ejecución:
| en desarrollo "php artisan schedule:work"; en servidor, una tarea
| cada minuto con "php artisan schedule:run".
|
*/

/*
| Papelera: lo que cumple 30 días se elimina definitivamente.
*/
Schedule::command('trackit:vaciar-papelera')
    ->dailyAt('03:30')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('notificaciones:mantenimientos')
    ->dailyAt('07:00')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping()
    ->onOneServer();
