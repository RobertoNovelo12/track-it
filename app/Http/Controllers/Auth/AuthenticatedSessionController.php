<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Permite iniciar sesión con correo o con nombre de usuario en el mismo campo.
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'correo' : 'username';

        if (! Auth::attempt(
            [$field => $credentials['login'], 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'login' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $user = Auth::user();

        $estado = DB::table('estados_usuario')
            ->where('id_estado_usuario', $user->id_estado_usuario)
            ->value('clave');

        if ($estado !== 'ACTIVO') {
            Auth::logout();

            $mensaje = match ($estado) {
                'PENDIENTE' => 'Tu cuenta aún está pendiente de aprobación por un administrador.',
                'INACTIVO' => 'Tu cuenta está deshabilitada temporalmente. Contacta a un administrador.',
                'BAJA' => 'Tu cuenta fue dada de baja del sistema.',
                default => 'Tu cuenta no está habilitada para iniciar sesión.',
            };

            throw ValidationException::withMessages(['login' => $mensaje]);
        }

        $request->session()->regenerate();

        // Cambia esto cuando tengas la vista real del dashboard del admin/técnico.
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}