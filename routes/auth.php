<?php

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
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


    /*
    |--------------------------------------------------------------------------
    | Cuenta pendiente de aprobación
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/registro/pendiente',
        'auth.pending-approval'
    )->name('register.pending');


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

});