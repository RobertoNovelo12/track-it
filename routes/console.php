<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
| Papelera: lo que cumple 30 días se elimina definitivamente.
*/
Schedule::command('trackit:vaciar-papelera')
    ->dailyAt('03:30')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping()
    ->onOneServer();
