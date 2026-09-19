<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

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
         * El middleware active.session utiliza
         * este valor para el límite de 24 horas.
         */
        $request->session()->put(
            'auth_started_at',
            now()->timestamp
        );


        /*
         * Generar alerta de inicio de sesión.
         *
         * Si la generación de la notificación falla,
         * el inicio de sesión NO debe bloquearse.
         */
        $this->createLoginSecurityNotification(
            $request,
            $user
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


    /*
    |--------------------------------------------------------------------------
    | Crear notificación de inicio de sesión
    |--------------------------------------------------------------------------
    */

    private function createLoginSecurityNotification(
        Request $request,
        User $user
    ): void {
        try {
            /*
             * Obtener la preferencia del usuario.
             *
             * Si todavía no existe una fila, se considera
             * activada porque ese es el valor predeterminado
             * definido para las preferencias de seguridad.
             */
            $preferences = DB::table(
                'preferencias_seguridad'
            )
                ->where(
                    'id_usuario',
                    $user->id_usuario
                )
                ->select(
                    'alerta_inicio_sesion'
                )
                ->first();


            $loginAlertEnabled =
                $preferences === null
                    ? true
                    : (bool) $preferences->alerta_inicio_sesion;


            /*
             * El usuario decidió no recibir
             * alertas de inicio de sesión.
             */
            if (! $loginAlertEnabled) {
                return;
            }


            /*
             * Obtener información básica del acceso.
             */
            $device = $this->formatDevice(
                $request->userAgent()
            );


            $ipAddress =
                filled($request->ip())
                    ? $request->ip()
                    : 'IP no disponible';


            /*
             * Destino de la notificación.
             *
             * Guardamos una URL relativa para que funcione
             * correctamente tanto en desarrollo como producción.
             */
            $destinationUrl =
                route(
                    'ajustes.index',
                    [
                        'section' =>
                            'seguridad',
                    ],
                    false
                )
                . '#sesiones';


            /*
             * Registrar notificación.
             */
            DB::table('notificaciones')
                ->insert([
                    'id_usuario' =>
                        $user->id_usuario,

                    'tipo' =>
                        'SEGURIDAD',

                    'titulo' =>
                        'Nuevo inicio de sesión',

                    'mensaje' =>
                        "Se inició sesión en tu cuenta desde {$device}. IP: {$ipAddress}.",

                    'url_destino' =>
                        $destinationUrl,

                    'leida' =>
                        false,

                    'fecha_creacion' =>
                        now(),

                    'fecha_lectura' =>
                        null,
                ]);
        } catch (Throwable $exception) {
            /*
             * Una falla al generar la notificación
             * nunca debe impedir que el usuario acceda.
             *
             * report() permitirá que Laravel registre
             * el error en sus logs.
             */
            report($exception);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener nombre legible del dispositivo
    |--------------------------------------------------------------------------
    */

    private function formatDevice(
        ?string $userAgent
    ): string {
        if (! filled($userAgent)) {
            return 'Dispositivo desconocido';
        }


        $userAgent =
            mb_strtolower(
                $userAgent
            );


        /*
         * Sistema operativo.
         */
        $operatingSystem = match (true) {
            str_contains(
                $userAgent,
                'windows'
            ) =>
                'Windows',

            str_contains(
                $userAgent,
                'android'
            ) =>
                'Android',

            str_contains(
                $userAgent,
                'iphone'
            ),
            str_contains(
                $userAgent,
                'ipad'
            ),
            str_contains(
                $userAgent,
                'ipod'
            ) =>
                'iOS',

            str_contains(
                $userAgent,
                'macintosh'
            ),
            str_contains(
                $userAgent,
                'mac os x'
            ) =>
                'macOS',

            str_contains(
                $userAgent,
                'linux'
            ) =>
                'Linux',

            default =>
                'Dispositivo',
        };


        /*
         * Navegador.
         *
         * El orden importa porque Edge y Opera
         * también contienen "Chrome" en el User-Agent.
         */
        $browser = match (true) {
            str_contains(
                $userAgent,
                'edg/'
            ),
            str_contains(
                $userAgent,
                'edge/'
            ) =>
                'Edge',

            str_contains(
                $userAgent,
                'opr/'
            ),
            str_contains(
                $userAgent,
                'opera'
            ) =>
                'Opera',

            str_contains(
                $userAgent,
                'samsungbrowser'
            ) =>
                'Samsung Internet',

            str_contains(
                $userAgent,
                'chrome/'
            ),
            str_contains(
                $userAgent,
                'crios/'
            ) =>
                'Chrome',

            str_contains(
                $userAgent,
                'firefox/'
            ),
            str_contains(
                $userAgent,
                'fxios/'
            ) =>
                'Firefox',

            str_contains(
                $userAgent,
                'safari/'
            ) =>
                'Safari',

            default =>
                'Navegador',
        };


        return "{$operatingSystem} · {$browser}";
    }
}