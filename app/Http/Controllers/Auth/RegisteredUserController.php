<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\User;
use App\Services\OrganizacionService;
use App\Services\PlantillaInstalacionService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(
        OrganizacionService $organizacion,
        PlantillaInstalacionService $plantilla
    ): View {
        $sedes = DB::table('sedes')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_sede',
                'nombre',
            ]);

        /*
         * Hoteles (unidades organizativas) activos de sedes activas.
         */
        $unidades = DB::table('unidades_organizativas')
            ->where('activo', true)
            ->whereIn('id_sede', $sedes->pluck('id_sede'))
            ->orderBy('nombre')
            ->get([
                'id_unidad',
                'id_sede',
                'nombre',
            ]);

        /*
         * Departamentos activos con su cobertura:
         * - atiende_todas_unidades, o
         * - los hoteles asignados en departamento_unidad.
         */
        $departamentos = Departamento::query()
            ->principales()
            ->activos()
            ->with('unidades:unidades_organizativas.id_unidad')
            ->orderBy('nombre')
            ->get([
                'id_departamento',
                'nombre',
                'atiende_todas_unidades',
            ])
            ->map(fn (Departamento $departamento) => (object) [
                'id_departamento' => $departamento->id_departamento,
                'nombre' => $departamento->nombre,
                'global' => (bool) $departamento->atiende_todas_unidades,
                'unidades' => $departamento->unidades
                    ->pluck('id_unidad')
                    ->map(fn ($id) => (int) $id)
                    ->all(),
            ]);

        $roles = DB::table('roles')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_rol',
                'nombre',
            ]);

        return view('auth.register', [
            'sedes' => $sedes,
            'unidades' => $unidades,
            'departamentos' => $departamentos,
            'roles' => $roles,
            'campos' => $organizacion->camposRegistro(),
            'dominios' => $organizacion->restringeDominio()
                ? $organizacion->dominios()
                : [],
            'etiquetas' => $plantilla->etiquetas(),
        ]);
    }


    public function store(
        Request $request,
        OrganizacionService $organizacion,
        PlantillaInstalacionService $plantilla
    ): RedirectResponse {
        $etiquetas = $plantilla->etiquetas();
        $unidad = mb_strtolower($etiquetas['unidad_singular']);

        /*
        |--------------------------------------------------------------------------
        | Campos configurables
        |--------------------------------------------------------------------------
        |
        | obligatorio -> required
        | opcional    -> nullable
        | oculto      -> se ignora aunque se envíe
        |
        */

        $regla = fn (string $campo): string =>
            $organizacion->campoObligatorio($campo)
                ? 'required'
                : 'nullable';

        $rules = [
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
                function (string $attribute, mixed $value, Closure $fail) use ($organizacion) {
                    if (! $organizacion->correoPermitido((string) $value)) {
                        $dominios = collect($organizacion->dominios())
                            ->map(fn ($dominio) => '@'.$dominio)
                            ->implode(', ');

                        $fail('Utiliza tu correo corporativo ('.$dominios.').');
                    }
                },
            ],

            /*
            |--------------------------------------------------------------------------
            | Información organizacional
            |--------------------------------------------------------------------------
            */

            'id_sede' => [
                'required',
                'integer',
                Rule::exists('sedes', 'id_sede')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],

            'id_unidad' => [
                'required',
                'integer',
                Rule::exists('unidades_organizativas', 'id_unidad')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],

            'id_departamento' => [
                'required',
                'integer',
                Rule::exists('departamentos', 'id_departamento')
                    ->where(fn ($query) => $query
                        ->where('activo', true)
                        ->whereNull('id_departamento_principal')),
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
        ];

        if ($organizacion->campoVisible('apellido_materno')) {
            $rules['apellido_materno'] = [$regla('apellido_materno'), 'string', 'max:255'];
        }

        if ($organizacion->campoVisible('telefono')) {
            $rules['telefono'] = [$regla('telefono'), 'string', 'max:30', 'regex:/^[0-9+()\-\s.]{7,30}$/'];
        }

        if ($organizacion->campoVisible('numero_colaborador')) {
            $rules['numero_colaborador'] = [
                $regla('numero_colaborador'),
                'string',
                'max:255',
                'unique:usuarios,numero_colaborador',
            ];
        }

        if ($organizacion->campoVisible('puesto')) {
            $rules['puesto'] = [$regla('puesto'), 'string', 'max:255'];
        }

        if ($organizacion->campoVisible('id_rol')) {
            $rules['id_rol'] = [
                $regla('id_rol'),
                'integer',
                Rule::exists('roles', 'id_rol')
                    ->where(fn ($query) => $query->where('activo', true)),
            ];
        }

        $validated = $request->validate($rules, [
            'id_unidad.required' => 'Selecciona tu '.$unidad.'.',
            'id_unidad.exists' => 'El '.$unidad.' seleccionado no está disponible.',
            'id_sede.required' => 'Selecciona tu sede.',
            'id_departamento.required' => 'Selecciona tu departamento.',
            'telefono.regex' => 'Escribe un teléfono válido.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validar relación Sede -> Hotel
        |--------------------------------------------------------------------------
        */

        $unidadSeleccionada = DB::table('unidades_organizativas')
            ->where('id_unidad', $validated['id_unidad'])
            ->where('id_sede', $validated['id_sede'])
            ->where('activo', true)
            ->first([
                'id_unidad',
                'id_area_origen',
            ]);

        if (! $unidadSeleccionada) {
            return back()
                ->withErrors([
                    'id_unidad' => 'El '.$unidad.' seleccionado no pertenece a la sede indicada.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validar cobertura Hotel -> Departamento
        |--------------------------------------------------------------------------
        */

        $departamentoValido = Departamento::query()
            ->disponiblesParaUnidad((int) $validated['id_unidad'])
            ->where('departamentos.id_departamento', $validated['id_departamento'])
            ->exists();

        if (! $departamentoValido) {
            return back()
                ->withErrors([
                    'id_departamento' => 'El departamento seleccionado no atiende el '.$unidad.' indicado.',
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

        $opcional = fn (string $campo): ?string =>
            filled($validated[$campo] ?? null)
                ? trim((string) $validated[$campo])
                : null;

        User::create([
            'nombres' => trim($validated['nombres']),
            'apellido_paterno' => trim($validated['apellido_paterno']),
            'apellido_materno' => $opcional('apellido_materno'),
            'telefono' => $opcional('telefono'),

            'username' => $validated['username'],
            'correo' => $validated['correo'],

            'numero_colaborador' => $opcional('numero_colaborador'),
            'puesto' => $opcional('puesto'),

            'id_unidad' => (int) $unidadSeleccionada->id_unidad,

            /*
             * Compatibilidad: los módulos que todavía leen usuarios.id_area
             * reciben el área de origen del hotel cuando existe.
             */
            'id_area' => $unidadSeleccionada->id_area_origen,

            'id_departamento' => (int) $validated['id_departamento'],
            'id_rol' => filled($validated['id_rol'] ?? null)
                ? (int) $validated['id_rol']
                : null,

            'password_hash' => Hash::make($validated['password']),

            'id_estado_usuario' => $idEstadoPendiente,
        ]);

        $notificaciones = app(\App\Services\NotificacionService::class);

        $notificaciones->notificar(
            'usuarios',
            'Nuevo usuario pendiente',
            trim($validated['nombres'] . ' ' . $validated['apellido_paterno'])
                . ' (' . $validated['username'] . ') se registró y espera aprobación.',
            route('usuarios.index', [], false),
            $notificaciones->administradoresDeUsuarios()
        );

        return redirect()->route('register.pending');
    }
}
