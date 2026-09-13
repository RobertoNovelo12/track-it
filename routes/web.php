<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Inicio
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Búsqueda global de equipos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/buscar-equipos',
        fn () => view('equipos.search')
    )
        ->name('equipos.search');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Equipos tecnológicos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos',
        [EquipoController::class, 'index']
    )->name('equipos.index');


    /*
    |--------------------------------------------------------------------------
    | Crear equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos/crear',
        fn () => view('equipos.create')
    )->name('equipos.create');


    /*
    |--------------------------------------------------------------------------
    | Ver detalle de equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos/{equipo}',
        function (int $equipo) {

            return view('equipos.show', [
                'equipoId' => $equipo,
            ]);

        }
    )
        ->whereNumber('equipo')
        ->name('equipos.show');

    /*
    |--------------------------------------------------------------------------
    | Editar equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos/{equipo}/editar',
        function (int $equipo) {

            return view('equipos.edit', [
                'equipoId' => $equipo,
            ]);

        }
    )
        ->whereNumber('equipo')
        ->name('equipos.edit');

});


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';