<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:usuarios,username'],
            'correo' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,correo'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $idEstadoPendiente = DB::table('estados_usuario')
            ->where('clave', 'PENDIENTE')
            ->value('id_estado_usuario');

        if (! $idEstadoPendiente) {
            // No se encontró el valor "PENDIENTE" en estados_usuario.
            return back()
                ->withErrors(['username' => 'No se encontró el estado "PENDIENTE" en estados_usuario.'])
                ->withInput();
        }

        User::create([
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'username' => $validated['username'],
            'correo' => $validated['correo'],
            'password_hash' => Hash::make($validated['password']),
            'id_estado_usuario' => $idEstadoPendiente,
        ]);

        return redirect()->route('register.pending');
    }
}