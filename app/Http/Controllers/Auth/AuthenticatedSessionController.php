<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
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

    public function store(Request $request): RedirectResponse
    {
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
        | Validar credenciales
        |--------------------------------------------------------------------------
        */

        if (! Auth::attempt(
            [
                $field => $credentials['login'],
                'password' => $credentials['password'],
            ],
            $request->boolean('remember')
        )) {

            throw ValidationException::withMessages([
                'login' =>
                    'Las credenciales no coinciden con nuestros registros.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Obtener usuario autenticado
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();


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

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('pending.approval');
        }


        /*
        |--------------------------------------------------------------------------
        | Cuenta inactiva
        |--------------------------------------------------------------------------
        */

        if ($estado === 'INACTIVO') {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

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

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

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

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' =>
                    'Tu cuenta no está habilitada para iniciar sesión.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Usuario activo
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Guardar inicio absoluto de sesión
        |--------------------------------------------------------------------------
        |
        | Se usa para limitar la sesión a un máximo de 24 horas.
        |
        */

        $request->session()->put(
            'auth_started_at',
            now()->timestamp
        );


        /*
        |--------------------------------------------------------------------------
        | Redirección
        |--------------------------------------------------------------------------
        */

        $response = redirect()->intended(
            route('dashboard')
        );


        /*
        |--------------------------------------------------------------------------
        | Cookie indicadora de sesión previa
        |--------------------------------------------------------------------------
        |
        | No contiene información sensible.
        |
        | Solamente permite distinguir:
        |
        | Nunca inició sesión
        |       vs
        | Tenía una sesión y ésta desapareció.
        |
        | La dejamos 30 días para que sobreviva a la expiración
        | de la sesión de Laravel.
        |
        */

        return $response->withCookie(
            cookie(
                name: 'trackit_auth_session',
                value: '1',
                minutes: 60 * 24 * 30,
                path: '/',
                domain: null,
                secure: false,
                httpOnly: true,
                raw: false,
                sameSite: 'lax'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión manualmente
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();


        /*
         * Muy importante:
         *
         * Al cerrar sesión voluntariamente eliminamos la cookie.
         *
         * De esta forma:
         *
         * Cerrar sesión
         *      ↓
         * Login normal
         *
         * y NO:
         *
         * Cerrar sesión
         *      ↓
         * Sesión caducada
         */

        return redirect('/')
            ->withCookie(
                Cookie::forget(
                    'trackit_auth_session'
                )
            );
    }
}