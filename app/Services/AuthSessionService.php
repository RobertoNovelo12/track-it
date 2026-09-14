<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthSessionService
{
    /*
    |--------------------------------------------------------------------------
    | Completar autenticación
    |--------------------------------------------------------------------------
    */

    public function complete(
        Request $request,
        User $user,
        bool $remember = false
    ): RedirectResponse {
        /*
         * El usuario solamente queda autenticado
         * cuando todos los factores requeridos
         * fueron superados.
         */
        Auth::login(
            $user,
            $remember
        );


        /*
         * Evitar session fixation.
         */
        $request->session()->regenerate();


        /*
         * Eliminar cualquier estado pendiente
         * utilizado durante el challenge 2FA.
         */
        $request->session()->forget([
            'two_factor.login.user_id',
            'two_factor.login.remember',
            'two_factor.login.expires_at',
        ]);


        /*
         * Inicio absoluto de sesión.
         *
         * Tu middleware active.session utiliza
         * este valor para el límite de 24 horas.
         */
        $request->session()->put(
            'auth_started_at',
            now()->timestamp
        );


        /*
         * Respetar la URL originalmente solicitada.
         */
        $response = redirect()->intended(
            route('dashboard')
        );


        /*
         * Indicador de sesión previa.
         *
         * No contiene información sensible.
         */
        return $response->withCookie(
            cookie(
                name: 'trackit_auth_session',
                value: '1',
                minutes: 60 * 24 * 30,
                path: '/',
                domain: null,
                secure: true,
                httpOnly: true,
                raw: false,
                sameSite: 'lax'
            )
        );
    }
}