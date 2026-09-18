<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $sedes = DB::table('sedes')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_sede',
                'nombre',
            ]);

        $areas = DB::table('areas')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_area',
                'id_sede',
                'nombre',
            ]);

        $departamentos = DB::table('departamentos')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_departamento',
                'id_area',
                'nombre',
            ]);

        $roles = DB::table('roles')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_rol',
                'nombre',
            ]);

        return view('auth.register', compact(
            'sedes',
            'areas',
            'departamentos',
            'roles',
        ));
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | Información personal
            |--------------------------------------------------------------------------
            */

            'nombres' => [
                'required',
                'string',
                'max:255',
            ],

            'apellido_paterno' => [
                'required',
                'string',
                'max:255',
            ],

            'apellido_materno' => [
                'nullable',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:255',
                'unique:usuarios,username',
            ],

            'correo' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:usuarios,correo',
            ],

            /*
            |--------------------------------------------------------------------------
            | Información organizacional
            |--------------------------------------------------------------------------
            */

            'numero_colaborador' => [
                'required',
                'string',
                'max:255',
                'unique:usuarios,numero_colaborador',
            ],

            'puesto' => [
                'required',
                'string',
                'max:255',
            ],

            'id_sede' => [
                'required',
                'integer',
                Rule::exists('sedes', 'id_sede')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],

            'id_area' => [
                'required',
                'integer',
                Rule::exists('areas', 'id_area')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],

            'id_departamento' => [
                'required',
                'integer',
                Rule::exists('departamentos', 'id_departamento')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],

            'id_rol' => [
                'required',
                'integer',
                Rule::exists('roles', 'id_rol')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],

            /*
            |--------------------------------------------------------------------------
            | Seguridad
            |--------------------------------------------------------------------------
            */

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validar relación Sede -> Área
        |--------------------------------------------------------------------------
        */

        $areaValida = DB::table('areas')
            ->where('id_area', $validated['id_area'])
            ->where('id_sede', $validated['id_sede'])
            ->where('activo', true)
            ->exists();

        if (! $areaValida) {
            return back()
                ->withErrors([
                    'id_area' => 'El área seleccionada no pertenece a la sede indicada.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validar relación Área -> Departamento
        |--------------------------------------------------------------------------
        */

        $departamentoValido = DB::table('departamentos')
            ->where('id_departamento', $validated['id_departamento'])
            ->where('id_area', $validated['id_area'])
            ->where('activo', true)
            ->exists();

        if (! $departamentoValido) {
            return back()
                ->withErrors([
                    'id_departamento' => 'El departamento seleccionado no pertenece al área indicada.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Estado inicial
        |--------------------------------------------------------------------------
        */

        $idEstadoPendiente = DB::table('estados_usuario')
            ->where('clave', 'PENDIENTE')
            ->value('id_estado_usuario');

        if (! $idEstadoPendiente) {
            return back()
                ->withErrors([
                    'username' => 'No se encontró el estado "PENDIENTE" en estados_usuario.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Crear usuario
        |--------------------------------------------------------------------------
        */

        User::create([
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,

            'username' => $validated['username'],
            'correo' => $validated['correo'],

            'numero_colaborador' => $validated['numero_colaborador'],
            'puesto' => $validated['puesto'],
            'id_area' => $validated['id_area'],
            'id_departamento' => $validated['id_departamento'],
            'id_rol' => $validated['id_rol'],

            'password_hash' => Hash::make($validated['password']),

            'id_estado_usuario' => $idEstadoPendiente,
        ]);

        return redirect()->route('register.pending');
    }
}