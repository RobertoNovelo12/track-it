<?php

namespace App\Livewire;

use App\Models\Departamento;
use App\Models\User;
use App\Services\NotificacionService;
use App\Services\OrganizacionService;
use App\Services\PlantillaInstalacionService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Seguridad y roles › Nuevo usuario
|--------------------------------------------------------------------------
|
| El administrador crea la cuenta ya ACTIVA y con su rol.
|
| Correo: siempre debe pertenecer a uno de los dominios configurados en
| Administración › Organización, aunque el registro público no tenga la
| restricción activada. Sin dominios configurados no se pueden crear
| usuarios desde aquí.
|
*/

class UsuarioCreate extends Component
{
    public string $nombres = '';
    public string $apellidoPaterno = '';
    public string $apellidoMaterno = '';
    public string $username = '';
    public string $correo = '';
    public string $telefono = '';

    public string $numeroColaborador = '';
    public string $puesto = '';

    public ?string $idUnidad = null;
    public ?string $idDepartamento = null;
    public ?string $idRol = null;

    public string $password = '';
    public string $passwordConfirmation = '';

    /*
     * Cuenta para un colaborador sin cuenta (Seguridad y roles ›
     * Colaboradores sin cuenta): al guardar se le vinculan sus equipos.
     */
    #[\Livewire\Attributes\Locked]
    public string $colaboradorNombre = '';

    #[\Livewire\Attributes\Locked]
    public string $colaboradorClave = '';

    public function mount(): void
    {
        $nombre = trim((string) preg_replace('/\s+/u', ' ', (string) request()->query('colaborador', '')));

        if ($nombre === '' || mb_strlen($nombre) > 255) {
            return;
        }

        $this->colaboradorNombre = $nombre;
        $this->colaboradorClave = app(\App\Services\ColaboradoresService::class)->clave($nombre);

        /*
         * Nombre escrito → nombres y apellidos: las dos últimas palabras
         * se toman como apellidos («Juan Carlos Pérez López»). El
         * administrador puede corregirlo antes de guardar.
         */
        $partes = explode(' ', $nombre);

        if (count($partes) >= 3) {
            $this->apellidoMaterno = array_pop($partes);
            $this->apellidoPaterno = array_pop($partes);
        } elseif (count($partes) === 2) {
            $this->apellidoPaterno = array_pop($partes);
        }

        $this->nombres = implode(' ', $partes);
        $this->numeroColaborador = mb_substr(trim((string) request()->query('numero', '')), 0, 50);
    }

    public function updatedIdUnidad(): void
    {
        $this->idDepartamento = null;
        $this->resetValidation(['idUnidad', 'idDepartamento']);
    }

    /*
    |--------------------------------------------------------------------------
    | Contraseña temporal
    |--------------------------------------------------------------------------
    */

    public function generarPassword(): void
    {
        $password = Str::password(12, symbols: false);

        $this->password = $password;
        $this->passwordConfirmation = $password;

        $this->resetValidation(['password', 'passwordConfirmation']);

        $this->dispatch('usuario-password-generada');
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    public function save(OrganizacionService $organizacion, PlantillaInstalacionService $plantilla)
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $etiquetas = $plantilla->etiquetas();
        $unidad = mb_strtolower($etiquetas['unidad_singular']);

        $dominios = $organizacion->dominios();

        $regla = fn (string $campo): string => $organizacion->campoObligatorio($campo)
            ? 'required'
            : 'nullable';

        foreach (['nombres', 'apellidoPaterno', 'apellidoMaterno', 'username', 'correo', 'telefono', 'numeroColaborador', 'puesto'] as $campo) {
            $this->{$campo} = trim($this->{$campo});
        }

        $this->correo = Str::lower($this->correo);

        $validated = $this->validate(
            [
                'nombres' => ['required', 'string', 'max:255'],
                'apellidoPaterno' => ['required', 'string', 'max:255'],
                'apellidoMaterno' => [$regla('apellido_materno'), 'string', 'max:255'],

                'username' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[A-Za-z0-9._-]+$/',
                    Rule::unique('usuarios', 'username'),
                ],

                'correo' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('usuarios', 'correo'),
                    function (string $attribute, mixed $value, Closure $fail) use ($dominios) {
                        if ($dominios === []) {
                            $fail('Configura al menos un dominio de correo en Administración › Organización antes de crear usuarios.');

                            return;
                        }

                        $dominio = Str::lower(Str::afterLast((string) $value, '@'));

                        if (! in_array($dominio, $dominios, true)) {
                            $fail('El correo debe ser del dominio de la organización ('
                                . collect($dominios)->map(fn ($d) => '@' . $d)->implode(', ')
                                . ').');
                        }
                    },
                ],

                'telefono' => [$regla('telefono'), 'string', 'max:30', 'regex:/^[0-9+()\-\s.]{7,30}$/'],

                'numeroColaborador' => [
                    $regla('numero_colaborador'),
                    'string',
                    'max:255',
                    Rule::unique('usuarios', 'numero_colaborador'),
                ],

                'puesto' => [$regla('puesto'), 'string', 'max:255'],

                'idUnidad' => [
                    'required',
                    'integer',
                    Rule::exists('unidades_organizativas', 'id_unidad')
                        ->where(fn ($query) => $query->where('activo', true)),
                ],

                'idDepartamento' => ['required', 'integer'],

                'idRol' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id_rol')
                        ->where(fn ($query) => $query->where('activo', true)),
                ],

                'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
                'passwordConfirmation' => ['required', 'string'],
            ],
            [
                'nombres.required' => 'Escribe el nombre.',
                'apellidoPaterno.required' => 'Escribe el apellido paterno.',
                'apellidoMaterno.required' => 'Escribe el apellido materno.',
                'username.required' => 'Escribe el nombre de usuario.',
                'username.regex' => 'Usa solo letras, números, punto, guion o guion bajo, sin espacios.',
                'username.unique' => 'Ese nombre de usuario ya está en uso.',
                'correo.required' => 'Escribe el correo electrónico.',
                'correo.email' => 'Escribe un correo válido.',
                'correo.unique' => 'Ya existe una cuenta con ese correo.',
                'telefono.required' => 'Escribe el teléfono.',
                'telefono.regex' => 'Escribe un teléfono válido.',
                'numeroColaborador.required' => 'Escribe el número de colaborador.',
                'numeroColaborador.unique' => 'Ese número de colaborador ya está registrado.',
                'puesto.required' => 'Escribe el puesto.',
                'idUnidad.required' => 'Selecciona el ' . $unidad . '.',
                'idUnidad.exists' => 'El ' . $unidad . ' seleccionado no está disponible.',
                'idDepartamento.required' => 'Selecciona el departamento.',
                'idRol.required' => 'Selecciona el rol.',
                'idRol.exists' => 'El rol seleccionado no está disponible.',
                'password.required' => 'Escribe una contraseña o genera una.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.same' => 'Las contraseñas no coinciden.',
                'passwordConfirmation.required' => 'Confirma la contraseña.',
            ]
        );

        $departamentoValido = Departamento::query()
            ->disponiblesParaUnidad((int) $validated['idUnidad'])
            ->where('departamentos.id_departamento', (int) $validated['idDepartamento'])
            ->exists();

        if (! $departamentoValido) {
            $this->addError('idDepartamento', 'El departamento no atiende el ' . $unidad . ' seleccionado.');

            return null;
        }

        $idEstadoActivo = DB::table('estados_usuario')
            ->where('clave', 'ACTIVO')
            ->value('id_estado_usuario');

        if (! $idEstadoActivo) {
            $this->addError('idRol', 'No se encontró el estado ACTIVO en el sistema.');

            return null;
        }

        /*
         * usuarios tiene la llave foránea compuesta
         * (id_departamento, id_area) -> departamentos, por eso id_area
         * debe ser el área registrada en el propio departamento
         * (NULL si no tiene). No se usa el área de origen del hotel.
         */
        $areaOrigen = DB::table('departamentos')
            ->where('id_departamento', (int) $validated['idDepartamento'])
            ->value('id_area');

        $opcional = fn (?string $valor): ?string => filled($valor) ? $valor : null;

        $usuario = DB::transaction(function () use ($validated, $idEstadoActivo, $areaOrigen, $opcional) {
            $usuario = User::create([
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellidoPaterno'],
                'apellido_materno' => $opcional($validated['apellidoMaterno'] ?? null),
                'telefono' => $opcional($validated['telefono'] ?? null),

                'username' => $validated['username'],
                'correo' => $validated['correo'],

                'numero_colaborador' => $opcional($validated['numeroColaborador'] ?? null),
                'puesto' => $opcional($validated['puesto'] ?? null),

                'id_unidad' => (int) $validated['idUnidad'],

                // Debe coincidir con departamentos.id_area (FK compuesta).
                'id_area' => $areaOrigen,

                'id_departamento' => (int) $validated['idDepartamento'],
                'id_rol' => (int) $validated['idRol'],

                'password_hash' => Hash::make($validated['password']),

                'id_estado_usuario' => $idEstadoActivo,
            ]);

            $rol = DB::table('roles')->where('id_rol', (int) $validated['idRol'])->value('nombre');

            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => auth()->id(),
                'accion' => 'USUARIO_CREADO',
                'modulo' => 'SEGURIDAD',
                'descripcion' => 'Se creó al usuario '
                    . $validated['nombres'] . ' ' . $validated['apellidoPaterno']
                    . ' (' . $validated['correo'] . ') con el rol ' . $rol . '.',
                'fecha_hora' => now(),
            ]);

            return $usuario;
        });

        $notificaciones = app(NotificacionService::class);

        $notificaciones->notificar(
            'usuarios',
            'Nuevo usuario creado',
            $notificaciones->nombreActor()
                . ' creó la cuenta de '
                . $validated['nombres'] . ' ' . $validated['apellidoPaterno']
                . ' (' . $validated['username'] . ').',
            route('usuarios.index', [], false),
            $notificaciones->administradoresDeUsuarios()
        );

        // Selector de colaboradores: incluir la cuenta nueva.
        $colaboradores = app(\App\Services\ColaboradoresService::class);
        $colaboradores->olvidarOpciones();

        $vinculadas = $this->colaboradorClave !== ''
            ? $colaboradores->vincular($this->colaboradorClave, (int) $usuario->id_usuario)
            : 0;

        session()->flash(
            'usuarioCreado',
            'La cuenta de ' . $usuario->nombres . ' se creó correctamente.'
                . ($vinculadas > 0
                    ? ' Se le vincularon ' . $vinculadas . ($vinculadas === 1 ? ' asignación.' : ' asignaciones.')
                    : '')
        );

        return $this->redirectRoute('usuarios.index', navigate: true);
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(OrganizacionService $organizacion, PlantillaInstalacionService $plantilla): View
    {
        $unidades = DB::table('unidades_organizativas')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_unidad', 'nombre'])
            ->map(fn ($u) => ['value' => (string) $u->id_unidad, 'label' => (string) $u->nombre])
            ->all();

        $departamentos = filled($this->idUnidad)
            ? Departamento::query()
                ->disponiblesParaUnidad((int) $this->idUnidad)
                ->orderBy('nombre')
                ->get(['departamentos.id_departamento', 'departamentos.nombre'])
                ->map(fn ($d) => ['value' => (string) $d->id_departamento, 'label' => (string) $d->nombre])
                ->all()
            : [];

        $columnasRol = ['id_rol', 'nombre', 'descripcion'];

        if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'nivel')) {
            $columnasRol[] = 'nivel';
        }

        $roles = DB::table('roles')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get($columnasRol);

        return view('livewire.usuario-create', [
            'unidades' => $unidades,
            'departamentos' => $departamentos,
            'roles' => $roles,
            'dominios' => $organizacion->dominios(),
            'etiquetas' => $plantilla->etiquetas(),
            'obligatorio' => fn (string $campo): bool => $organizacion->campoObligatorio($campo),
        ]);
    }
}
