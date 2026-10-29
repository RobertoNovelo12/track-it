<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Cambio obligatorio de la contraseña temporal
|--------------------------------------------------------------------------
*/

class CambioContrasenaController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! (bool) ($request->user()->debe_cambiar_password ?? false)) {
            return redirect()->to('/');
        }

        return view('auth.cambiar-contrasena');
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        if (! (bool) ($usuario->debe_cambiar_password ?? false)) {
            return redirect()->to('/');
        }

        $datos = $request->validate([
            'password' => [
                'required',
                'string',
                // Política única (App\Support\PoliticaContrasena).
                ...\App\Support\PoliticaContrasena::reglas(),
                'confirmed',
            ],
        ], \App\Support\PoliticaContrasena::mensajes('password') + [
            'password.required' => 'Escribe tu nueva contraseña.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        if (Hash::check($datos['password'], $usuario->getAuthPassword())) {
            return back()->withErrors([
                'password' => 'Elige una contraseña diferente a la temporal.',
            ]);
        }

        DB::transaction(function () use ($usuario, $datos) {
            DB::table('usuarios')
                ->where('id_usuario', $usuario->id_usuario)
                ->update([
                    'password_hash' => Hash::make($datos['password']),
                    'debe_cambiar_password' => false,
                ]);

            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => $usuario->id_usuario,
                'accion' => 'CONTRASENA_TEMPORAL_CAMBIADA',
                'modulo' => 'SEGURIDAD',
                'descripcion' => 'El usuario reemplazó la contraseña temporal asignada por un administrador.',
                'fecha_hora' => now(),
            ]);
        });

        return redirect()->to('/')
            ->with('status', 'Tu contraseña se actualizó correctamente.');
    }
}
