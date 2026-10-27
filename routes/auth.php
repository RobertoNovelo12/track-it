<?php

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CambioContrasenaController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Rutas para invitados
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login',
        [AuthenticatedSessionController::class, 'create']
    )->name('login');

    Route::post(
        '/login',
        [AuthenticatedSessionController::class, 'store']
    );

    /*
    |--------------------------------------------------------------------------
    | Challenge 2FA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/two-factor-challenge',
        [TwoFactorChallengeController::class, 'create']
    )->name('two-factor.challenge');


    Route::post(
        '/two-factor-challenge',
        [TwoFactorChallengeController::class, 'store']
    )->name('two-factor.verify');


    /*
    | Registro, recuperar contraseña y «registro pendiente»: ocultos (404)
    | mientras auth.acceso_publico esté apagado. Las rutas se conservan.
    */
    Route::middleware(\App\Http\Middleware\AccesoPublicoAuth::class)->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Registro
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/registro',
            [RegisteredUserController::class, 'create']
        )->name('register');

        Route::post(
            '/registro',
            [RegisteredUserController::class, 'store']
        );


        /*
        |--------------------------------------------------------------------------
        | Recuperar contraseña
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/olvide-mi-contrasena',
            [PasswordResetLinkController::class, 'create']
        )->name('password.request');

        Route::post(
            '/olvide-mi-contrasena',
            [PasswordResetLinkController::class, 'store']
        )->name('password.email');

        Route::get(
            '/olvide-mi-contrasena/preguntas',
            [PasswordResetLinkController::class, 'preguntas']
        )->name('password.preguntas');

        Route::post(
            '/olvide-mi-contrasena/preguntas',
            [PasswordResetLinkController::class, 'verificar']
        )->name('password.verificar');

        Route::get(
            '/olvide-mi-contrasena/enviada',
            [PasswordResetLinkController::class, 'solicitada']
        )->name('password.solicitada');


        /*
        |--------------------------------------------------------------------------
        | Cuenta pendiente de aprobación
        |--------------------------------------------------------------------------
        */

        Route::view(
            '/registro/pendiente',
            'auth.pending-approval'
        )->name('register.pending');

    });


    Route::view(
        '/pending-approval',
        'auth.pending-approval'
    )->name('pending.approval');


    /*
    |--------------------------------------------------------------------------
    | Sesión caducada
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/sesion-caducada',
        'auth.session-expired'
    )->name('session.expired');

});


/*
|--------------------------------------------------------------------------
| Rutas autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post(
        '/logout',
        [AuthenticatedSessionController::class, 'destroy']
    )->name('logout');

    /*
     * Cambio obligatorio de la contraseña temporal
     * (ver App\Http\Middleware\ExigirCambioContrasena).
     */
    Route::get(
        '/cambiar-contrasena',
        [CambioContrasenaController::class, 'create']
    )->name('password.cambiar');

    Route::post(
        '/cambiar-contrasena',
        [CambioContrasenaController::class, 'store']
    )->name('password.cambiar.guardar');

});