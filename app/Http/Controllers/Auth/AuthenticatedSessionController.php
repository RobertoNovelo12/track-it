<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Mostrar login
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view('auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | Iniciar sesión
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        AuthSessionService $authSession
    ): RedirectResponse {
        $credentials = $request->validate([
            'login' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Usuario o correo
        |--------------------------------------------------------------------------
        */

        $field = filter_var(
            $credentials['login'],
            FILTER_VALIDATE_EMAIL
        )
            ? 'correo'
            : 'username';


        /*
        |--------------------------------------------------------------------------
        | Buscar usuario
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | Ya no utilizamos Auth::attempt() aquí.
        |
        | Auth::attempt() iniciaría la sesión antes
        | de verificar el segundo factor.
        |
        */

        $user = User::query()
            ->where(
                $field,
                $credentials['login']
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Validar credenciales
        |--------------------------------------------------------------------------
        */

        if (
            ! $user ||
            ! Hash::check(
                $credentials['password'],
                $user->getAuthPassword()
            )
        ) {
            throw ValidationException::withMessages([
                'login' =>
                    'Las credenciales no coinciden con nuestros registros.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Obtener estado del usuario
        |--------------------------------------------------------------------------
        */

        $estado = DB::table('estados_usuario')
            ->where(
                'id_estado_usuario',
                $user->id_estado_usuario
            )
            ->value('clave');


        /*
        |--------------------------------------------------------------------------
        | Cuenta pendiente
        |--------------------------------------------------------------------------
        */

        if ($estado === 'PENDIENTE') {
            return redirect()
                ->route('pending.approval');
        }


        /*
        |--------------------------------------------------------------------------
        | Cuenta inactiva
        |--------------------------------------------------------------------------
        */

        if ($estado === 'INACTIVO') {
            throw ValidationException::withMessages([
                'login' =>
                    'Tu cuenta está deshabilitada temporalmente. Contacta a un administrador.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Cuenta dada de baja
        |--------------------------------------------------------------------------
        */

        if ($estado === 'BAJA') {
            throw ValidationException::withMessages([
                'login' =>
                    'Tu cuenta fue dada de baja del sistema.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Estado no reconocido
        |--------------------------------------------------------------------------
        */

        if ($estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'login' =>
                    'Tu cuenta no está habilitada para iniciar sesión.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ¿Requiere 2FA?
        |--------------------------------------------------------------------------
        */

        if ($user->hasTwoFactorEnabled()) {

            /*
             * Rotamos el ID de sesión después de
             * superar correctamente la contraseña.
             *
             * El usuario todavía NO está autenticado.
             */
            $request->session()->regenerate();


            /*
             * Guardamos solamente la información
             * necesaria para completar el challenge.
             */
            $request->session()->put([
                'two_factor.login.user_id' =>
                    $user->id_usuario,

                'two_factor.login.remember' =>
                    $request->boolean('remember'),

                /*
                 * El challenge solamente será válido
                 * durante 10 minutos.
                 */
                'two_factor.login.expires_at' =>
                    now()
                        ->addMinutes(10)
                        ->timestamp,
            ]);


            return redirect()
                ->route('two-factor.challenge');
        }


        /*
        |--------------------------------------------------------------------------
        | Usuario sin 2FA
        |--------------------------------------------------------------------------
        */

        return $authSession->complete(
            $request,
            $user,
            $request->boolean('remember')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión manualmente
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request
    ): RedirectResponse {
        Auth::guard('web')->logout();


        $request->session()->invalidate();

        $request->session()->regenerateToken();


        /*
         * Al cerrar sesión voluntariamente
         * eliminamos la cookie indicadora.
         */
        return redirect('/')
            ->withCookie(
                Cookie::forget(
                    'trackit_auth_session'
                )
            );
    }
}