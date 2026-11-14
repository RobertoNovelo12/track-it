<?php

namespace App\Livewire;

use App\Services\RestablecerInstanciaService;
use App\Services\TwoFactorAuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
 * Administración > Plantillas > Restablecer instancia.
 * Pasos: contraseña → confirmación 1 → confirmación 2 (escribir RESTABLECER)
 * → código del autenticador. Ver App\Services\RestablecerInstanciaService.
 */
class RestablecerInstancia extends Component
{
    public const FRASE = 'RESTABLECER';

    private const INTENTOS = 5;

    #[Locked]
    public int $paso = 1;

    public string $password = '';

    public bool $entiendo = false;

    public string $frase = '';

    public string $codigo = '';

    #[Locked]
    public bool $passwordOk = false;

    #[Locked]
    public bool $confirmado1 = false;

    #[Locked]
    public bool $confirmado2 = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->esAdministrador(), 403);
    }

    public function verificarPassword(): void
    {
        $this->validate(
            ['password' => ['required', 'string']],
            ['password.required' => 'Escribe tu contraseña.']
        );

        if ($this->bloqueado('password')) {
            return;
        }

        if (! Hash::check($this->password, auth()->user()->getAuthPassword())) {
            RateLimiter::hit($this->llave(), 60);
            $this->password = '';
            $this->addError('password', 'La contraseña no es correcta.');

            return;
        }

        $this->password = '';
        $this->passwordOk = true;
        $this->paso = 2;
    }

    public function confirmar1(): void
    {
        if (! $this->passwordOk) {
            $this->reiniciar();

            return;
        }

        $this->validate(
            ['entiendo' => ['accepted']],
            ['entiendo.accepted' => 'Marca la casilla para confirmar que entiendes lo que se eliminará.']
        );

        $this->confirmado1 = true;
        $this->paso = 3;
    }

    public function confirmar2(): void
    {
        if (! $this->passwordOk || ! $this->confirmado1) {
            $this->reiniciar();

            return;
        }

        if (mb_strtoupper(trim($this->frase)) !== self::FRASE) {
            $this->addError('frase', 'Escribe '.self::FRASE.' para confirmar.');

            return;
        }

        $this->confirmado2 = true;
        $this->paso = 4;
    }

    public function restablecer(): void
    {
        if (! $this->passwordOk || ! $this->confirmado1 || ! $this->confirmado2) {
            $this->reiniciar();

            return;
        }

        $this->validate(
            ['codigo' => ['required', 'digits:6']],
            [
                'codigo.required' => 'Escribe el código de tu aplicación autenticadora.',
                'codigo.digits' => 'El código debe tener 6 dígitos.',
            ]
        );

        if ($this->bloqueado('codigo')) {
            return;
        }

        $usuario = auth()->user();

        if (! $usuario->hasTwoFactorEnabled()) {
            $this->addError('codigo', 'Activa la verificación en dos pasos en Ajustes para poder restablecer.');

            return;
        }

        if (! app(TwoFactorAuthService::class)->verifyCode((string) $usuario->two_factor_secret, $this->codigo)) {
            RateLimiter::hit($this->llave(), 60);
            $this->codigo = '';
            $this->addError('codigo', 'El código no es válido o ya expiró.');

            return;
        }

        RateLimiter::clear($this->llave());

        $credenciales = app(RestablecerInstanciaService::class)->restablecer($usuario->id_usuario);

        // La cuenta ya no existe: cerrar sesión y mostrar las credenciales una sola vez.
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('credenciales_instancia', $credenciales);
        Cookie::queue(Cookie::forget('trackit_auth_session'));

        $this->redirect(route('instancia.restablecida'));
    }

    public function render(): View
    {
        $contar = fn (string $tabla) => DB::table($tabla)->count();

        return view('livewire.restablecer-instancia', [
            'conteos' => [
                'usuarios' => $contar('usuarios'),
                'sedes' => $contar('sedes'),
                'unidades' => $contar('unidades_organizativas'),
                'departamentos' => $contar('departamentos'),
                'equipos' => $contar('equipos'),
            ],
            'tieneDosPasos' => auth()->user()->hasTwoFactorEnabled(),
        ]);
    }

    private function reiniciar(): void
    {
        $this->reset(['password', 'entiendo', 'frase', 'codigo', 'passwordOk', 'confirmado1', 'confirmado2']);
        $this->paso = 1;
    }

    private function bloqueado(string $campo): bool
    {
        if (RateLimiter::tooManyAttempts($this->llave(), self::INTENTOS)) {
            $segundos = RateLimiter::availableIn($this->llave());
            $this->addError($campo, "Demasiados intentos. Espera {$segundos} segundos.");

            return true;
        }

        return false;
    }

    private function llave(): string
    {
        return 'restablecer-instancia:'.auth()->id();
    }
}
