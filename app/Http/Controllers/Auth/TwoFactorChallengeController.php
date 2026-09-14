<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthSessionService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Mostrar challenge
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): View|RedirectResponse {
        if (! $this->hasValidPendingLogin($request)) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'La verificación expiró. Inicia sesión nuevamente.'
                );
        }


        $user = User::query()
            ->find(
                $request->session()->get(
                    'two_factor.login.user_id'
                )
            );


        /*
         * Si el usuario dejó de existir o 2FA
         * fue desactivado durante el proceso,
         * reiniciamos el login.
         */
        if (
            ! $user ||
            ! $user->hasTwoFactorEnabled()
        ) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login');
        }


        return view(
            'auth.two-factor-challenge'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar challenge
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        TwoFactorAuthService $twoFactor,
        AuthSessionService $authSession
    ): RedirectResponse {
        /*
         * Verificar primero que exista
         * un login pendiente vigente.
         */
        if (! $this->hasValidPendingLogin($request)) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'La verificación expiró. Inicia sesión nuevamente.'
                );
        }


        $userId = (int) $request
            ->session()
            ->get(
                'two_factor.login.user_id'
            );


        $user = User::query()
            ->find($userId);


        if (
            ! $user ||
            ! $user->hasTwoFactorEnabled()
        ) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login');
        }


        /*
        |--------------------------------------------------------------------------
        | Rate limiting
        |--------------------------------------------------------------------------
        |
        | Evita probar códigos indefinidamente.
        |
        */

        $rateLimitKey =
            'two-factor-login:'
            . $userId
            . '|'
            . $request->ip();


        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                5
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $rateLimitKey
            );


            throw ValidationException::withMessages([
                'code' =>
                    "Demasiados intentos. Intenta nuevamente en {$seconds} segundos.",
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Método de verificación
        |--------------------------------------------------------------------------
        */

        $mode = $request->input(
            'mode',
            'authenticator'
        );


        if ($mode === 'recovery') {
            $valid = $this->verifyRecoveryCode(
                $request,
                $userId
            );
        } else {
            $valid = $this->verifyAuthenticatorCode(
                $request,
                $user,
                $twoFactor
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Código incorrecto
        |--------------------------------------------------------------------------
        */

        if (! $valid) {
            RateLimiter::hit(
                $rateLimitKey,
                60
            );


            if ($mode === 'recovery') {
                throw ValidationException::withMessages([
                    'recovery_code' =>
                        'El código de recuperación no es válido.',
                ]);
            }


            throw ValidationException::withMessages([
                'code' =>
                    'El código de verificación no es válido.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verificación correcta
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $rateLimitKey
        );


        $remember = (bool) $request
            ->session()
            ->get(
                'two_factor.login.remember',
                false
            );


        /*
         * Volvemos a consultar al usuario porque
         * un recovery code pudo modificar su modelo.
         */
        $user = User::query()
            ->findOrFail($userId);


        return $authSession->complete(
            $request,
            $user,
            $remember
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar código TOTP
    |--------------------------------------------------------------------------
    */

    private function verifyAuthenticatorCode(
        Request $request,
        User $user,
        TwoFactorAuthService $twoFactor
    ): bool {
        $validated = $request->validate([
            'mode' => [
                'required',
                'in:authenticator',
            ],

            'code' => [
                'required',
                'digits:6',
            ],
        ], [
            'code.required' =>
                'Ingresa el código de verificación.',

            'code.digits' =>
                'El código debe contener 6 dígitos.',
        ]);


        return $twoFactor->verifyCode(
            $user->two_factor_secret,
            $validated['code']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar código de recuperación
    |--------------------------------------------------------------------------
    */

    private function verifyRecoveryCode(
        Request $request,
        int $userId
    ): bool {
        $validated = $request->validate([
            'mode' => [
                'required',
                'in:recovery',
            ],

            'recovery_code' => [
                'required',
                'string',
                'max:50',
            ],
        ], [
            'recovery_code.required' =>
                'Ingresa un código de recuperación.',
        ]);


        /*
         * Los generamos en mayúsculas.
         */
        $recoveryCode = strtoupper(
            trim(
                $validated['recovery_code']
            )
        );


        /*
         * Bloqueamos la fila mientras consumimos
         * el código para evitar que dos peticiones
         * utilicen el mismo recovery code.
         */
        return DB::transaction(
            function () use (
                $userId,
                $recoveryCode
            ): bool {
                $user = User::query()
                    ->whereKey($userId)
                    ->lockForUpdate()
                    ->first();


                if (
                    ! $user ||
                    ! $user->hasTwoFactorEnabled()
                ) {
                    return false;
                }


                $codes =
                    $user->two_factor_recovery_codes
                    ?? [];


                foreach (
                    $codes as $index => $hash
                ) {
                    if (
                        Hash::check(
                            $recoveryCode,
                            $hash
                        )
                    ) {
                        /*
                         * Un recovery code solamente
                         * puede utilizarse una vez.
                         */
                        unset(
                            $codes[$index]
                        );


                        $user->two_factor_recovery_codes =
                            array_values($codes);


                        $user->save();


                        /*
                         * Registrar uso del código,
                         * pero nunca almacenar su valor.
                         */
                        DB::table('bitacora_auditoria')
                            ->insert([
                                'id_usuario' =>
                                    $user->id_usuario,

                                'accion' =>
                                    'CODIGO_RECUPERACION_USADO',

                                'modulo' =>
                                    'AUTENTICACION',

                                'descripcion' =>
                                    'El usuario inició sesión utilizando un código de recuperación 2FA.',

                                'fecha_hora' =>
                                    now(),
                            ]);


                        return true;
                    }
                }


                return false;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login pendiente válido
    |--------------------------------------------------------------------------
    */

    private function hasValidPendingLogin(
        Request $request
    ): bool {
        $userId = $request
            ->session()
            ->get(
                'two_factor.login.user_id'
            );


        $expiresAt = $request
            ->session()
            ->get(
                'two_factor.login.expires_at'
            );


        if (
            ! $userId ||
            ! $expiresAt
        ) {
            return false;
        }


        return now()->timestamp
            <= (int) $expiresAt;
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar login pendiente
    |--------------------------------------------------------------------------
    */

    private function clearPendingLogin(
        Request $request
    ): void {
        $request->session()->forget([
            'two_factor.login.user_id',
            'two_factor.login.remember',
            'two_factor.login.expires_at',
        ]);
    }
}