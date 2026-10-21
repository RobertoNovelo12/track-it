<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RestablecimientoContrasenaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| «¿Olvidaste tu contraseña?»
|--------------------------------------------------------------------------
|
| Ya no se envían correos: el usuario responde dos preguntas de seguridad
| y, si acierta, se avisa a los administradores para que le den una
| contraseña temporal (ver RestablecimientoContrasenaService).
|
| Las rutas conservan sus nombres (password.request / password.email).
|
*/

class PasswordResetLinkController extends Controller
{
    private const SESION = 'restablecer_contrasena';

    private const INTENTOS = 5;

    private const BLOQUEO_SEGUNDOS = 900;

    public function __construct(
        private RestablecimientoContrasenaService $restablecimiento
    ) {}

    /*
    | Paso 1: usuario o correo
    */
    public function create(Request $request): View
    {
        $request->session()->forget(self::SESION);

        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Escribe tu nombre de usuario o tu correo.',
        ]);

        $this->limitar('restablecer-buscar:' . $request->ip(), 'login', 10);

        $usuario = $this->restablecimiento->buscarUsuario($datos['login']);

        if ($usuario) {
            $preguntas = $this->restablecimiento->preguntasPara($usuario);

            if ($preguntas === []) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'login' => 'Tu cuenta no tiene datos suficientes para verificar tu identidad. Acude con un administrador del sistema.',
                    ]);
            }
        } else {
            /*
             * Cuenta inexistente o inactiva: se muestran preguntas igual y
             * la verificación fallará, para no revelar qué cuentas existen.
             */
            $preguntas = ['fecha_nacimiento', 'numero_colaborador'];
        }

        $request->session()->put(self::SESION, [
            'id_usuario' => $usuario ? (int) $usuario->id_usuario : null,
            'login' => mb_strtolower(trim($datos['login'])),
            'preguntas' => $preguntas,
        ]);

        return redirect()->route('password.preguntas');
    }

    /*
    | Paso 2: preguntas de seguridad
    */
    public function preguntas(Request $request): View|RedirectResponse
    {
        $estado = $request->session()->get(self::SESION);

        if (! is_array($estado) || empty($estado['preguntas'])) {
            return redirect()->route('password.request');
        }

        $preguntas = collect($estado['preguntas'])
            ->mapWithKeys(fn (string $clave) => [$clave => RestablecimientoContrasenaService::PREGUNTAS[$clave]])
            ->all();

        return view('auth.forgot-password-preguntas', [
            'preguntas' => $preguntas,
            'marcas' => in_array('marca_equipo', $estado['preguntas'], true)
                ? $this->restablecimiento->opcionesMarca()
                : [],
        ]);
    }

    public function verificar(Request $request): RedirectResponse
    {
        $estado = $request->session()->get(self::SESION);

        if (! is_array($estado) || empty($estado['preguntas'])) {
            return redirect()->route('password.request');
        }

        $reglas = [];

        foreach ($estado['preguntas'] as $clave) {
            $reglas['respuestas.' . $clave] = ['required', 'string', 'max:255'];
        }

        $datos = $request->validate($reglas, [
            'respuestas.*.required' => 'Responde esta pregunta.',
        ]);

        $llave = 'restablecer-verificar:' . $estado['login'] . '|' . $request->ip();

        $this->limitar($llave, 'respuestas');

        $correcto = $estado['id_usuario'] !== null
            && $this->restablecimiento->verificar(
                (int) $estado['id_usuario'],
                $estado['preguntas'],
                $datos['respuestas'] ?? []
            );

        if (! $correcto) {
            RateLimiter::hit($llave, self::BLOQUEO_SEGUNDOS);

            return back()
                ->withInput()
                ->withErrors([
                    'respuestas' => 'Las respuestas no coinciden con los datos de tu cuenta.',
                ]);
        }

        RateLimiter::clear($llave);

        $usuario = $this->restablecimiento->buscarUsuario($estado['login']);

        if ($usuario) {
            $this->restablecimiento->registrarSolicitud($usuario, $request->ip());
        }

        $request->session()->forget(self::SESION);

        return redirect()->route('password.solicitada');
    }

    public function solicitada(): View
    {
        return view('auth.forgot-password-solicitada');
    }

    /** Bloquea tras varios intentos fallidos (15 min). */
    private function limitar(string $llave, string $campo, int $maximo = self::INTENTOS): void
    {
        if (RateLimiter::tooManyAttempts($llave, $maximo)) {
            $minutos = (int) ceil(RateLimiter::availableIn($llave) / 60);

            throw ValidationException::withMessages([
                $campo => 'Demasiados intentos. Vuelve a intentarlo en ' . $minutos . ($minutos === 1 ? ' minuto.' : ' minutos.'),
            ]);
        }

        if (str_starts_with($llave, 'restablecer-buscar:')) {
            RateLimiter::hit($llave, self::BLOQUEO_SEGUNDOS);
        }
    }
}
