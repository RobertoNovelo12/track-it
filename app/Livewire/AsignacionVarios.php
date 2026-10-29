<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Json;
use Livewire\Attributes\Locked;

/*
|--------------------------------------------------------------------------
| Asignar varios equipos (una sola persona o un departamento)
|--------------------------------------------------------------------------
|
| Página propia (asignaciones.varios). Llega con ?equipos=1,2,3 desde la
| barra de selección de Inventario, o vacía desde Movimientos › Nuevo
| movimiento › Asignar varios.
|
| Asignar a:
|   persona      -> un colaborador (con cuenta o escrito sin cuenta).
|   departamento -> sin responsable: la asignación queda en el hotel /
|                   departamento / ubicación elegidos.
|
| Se guarda todo o nada: si un equipo ya está asignado o de baja, no se
| asigna ninguno y se dice cuáles son.
|
| Reutiliza las consultas de AsignacionCreate (equipos disponibles, tipos
| de asignación).
|
*/

class AsignacionVarios extends AsignacionCreate
{
    public const MAXIMO = 50;

    /** @var array<int, int> */
    #[Locked]
    public array $preseleccion = [];

    #[Locked]
    public string $volverA = '';

    public function mount(string $equipos = '', string $volver = ''): void
    {
        parent::mount();

        $this->preseleccion = array_slice(array_values(array_unique(array_filter(
            array_map('intval', explode(',', $equipos)),
            fn (int $id): bool => $id > 0
        ))), 0, self::MAXIMO);

        $this->volverA = $volver === 'inventario' ? route('equipos.index') : route('asignaciones.index');
    }

    #[Json]
    public function guardarVarios(array $payload): array
    {
        Gate::authorize('gestionar');

        $ids = array_slice(array_values(array_unique(array_filter(
            array_map('intval', (array) ($payload['equipos'] ?? [])),
            fn (int $id): bool => $id > 0
        ))), 0, self::MAXIMO);

        $destino = ($payload['destino'] ?? 'persona') === 'departamento' ? 'departamento' : 'persona';

        $datos = [
            'equipos' => $ids,
            'colaborador' => trim((string) ($payload['colaborador'] ?? '')),
            'numeroColaborador' => trim((string) ($payload['numeroColaborador'] ?? '')),
            'idUnidadAsignar' => $this->nullableInt($payload['idUnidadAsignar'] ?? null),
            'idDepartamentoAsignar' => $this->nullableInt($payload['idDepartamentoAsignar'] ?? null),
            'idUbicacionAsignar' => $this->nullableInt($payload['idUbicacionAsignar'] ?? null),
            'fechaAsignacion' => (string) ($payload['fechaAsignacion'] ?? ''),
            'idTipoAsignacion' => $this->nullableInt($payload['idTipoAsignacion'] ?? null),
            'observacionesAsignacion' => (string) ($payload['observacionesAsignacion'] ?? ''),
        ];

        $validated = Validator::make($datos, [
            'equipos' => ['required', 'array', 'min:1', 'max:'.self::MAXIMO],
            'colaborador' => [$destino === 'persona' ? 'required' : 'nullable', 'string', 'max:255'],
            'numeroColaborador' => ['nullable', 'string', 'max:255'],
            'idUnidadAsignar' => ['required', 'integer'],
            'idDepartamentoAsignar' => ['required', 'integer', 'exists:departamentos,id_departamento'],
            'idUbicacionAsignar' => ['nullable', 'integer', 'exists:ubicaciones,id_ubicacion'],
            'fechaAsignacion' => ['required', 'date'],
            'idTipoAsignacion' => ['required', 'integer', 'exists:catalogo_valores,id_valor'],
            'observacionesAsignacion' => ['nullable', 'string', 'max:2000'],
        ], [
            'equipos.required' => 'Agrega al menos un equipo.',
            'equipos.min' => 'Agrega al menos un equipo.',
            'equipos.max' => 'Puedes asignar hasta '.self::MAXIMO.' equipos a la vez.',
            'colaborador.required' => 'Busca un usuario o escribe el nombre del colaborador.',
            'idUnidadAsignar.required' => 'Selecciona un hotel.',
            'idDepartamentoAsignar.required' => 'Selecciona un departamento.',
            'fechaAsignacion.required' => 'Selecciona la fecha de asignación.',
            'idTipoAsignacion.required' => 'Selecciona el tipo de asignación.',
        ])->validate();

        $organizacion = app(\App\Services\UbicacionOrganizacionalService::class)->validar(
            $validated['idUnidadAsignar'],
            $validated['idDepartamentoAsignar'],
            $validated['idUbicacionAsignar'],
            ['unidad' => 'idUnidadAsignar', 'departamento' => 'idDepartamentoAsignar', 'ubicacion' => 'idUbicacionAsignar']
        );

        $tipos = collect($this->getTiposAsignacion())->pluck('value')->map(fn ($v) => (string) $v)->all();

        if (! in_array((string) $validated['idTipoAsignacion'], $tipos, true)) {
            throw ValidationException::withMessages(['idTipoAsignacion' => 'El tipo de asignación seleccionado no es válido.']);
        }

        // Responsable: persona (con o sin cuenta) o ninguno (departamento).
        $colaboradores = app(\App\Services\ColaboradoresService::class);
        $colaborador = null;

        if ($destino === 'persona') {
            $colaborador = $colaboradores->resolver($validated['colaborador'], $validated['colaborador'], $validated['numeroColaborador']);

            if (trim((string) $colaborador['nombre']) === '') {
                throw ValidationException::withMessages(['colaborador' => 'Busca un usuario o escribe el nombre del colaborador.']);
            }
        }

        $ahora = now();
        $fecha = Carbon::parse($validated['fechaAsignacion'])->setTime($ahora->hour, $ahora->minute, $ahora->second);

        $codigos = DB::transaction(function () use ($ids, $destino, $colaborador, $colaboradores, $organizacion, $validated, $fecha): array {
            $equipos = DB::table('equipos')
                ->whereIn('id_equipo', $ids)
                ->lockForUpdate()
                ->get(['id_equipo', 'codigo_inventario'])
                ->keyBy('id_equipo');

            $faltan = array_diff($ids, $equipos->keys()->map(fn ($id) => (int) $id)->all());

            if ($faltan !== []) {
                throw ValidationException::withMessages(['equipos' => 'Algunos equipos ya no existen. Quítalos de la lista y vuelve a intentarlo.']);
            }

            $etiqueta = fn ($lista) => collect($lista)->map(fn ($id) => (string) $equipos[$id]->codigo_inventario)->sort()->implode(', ');

            $asignados = DB::table('asignaciones')->whereIn('id_equipo', $ids)->whereNull('fecha_fin')->distinct()->pluck('id_equipo')->map(fn ($id) => (int) $id)->all();

            if ($asignados !== []) {
                throw ValidationException::withMessages(['equipos' => 'Ya tienen una asignación activa: '.$etiqueta($asignados).'. Quítalos de la lista o devuélvelos primero.']);
            }

            $deBaja = DB::table('bajas')->whereIn('id_equipo', $ids)->distinct()->pluck('id_equipo')->map(fn ($id) => (int) $id)->all();

            if ($deBaja !== []) {
                throw ValidationException::withMessages(['equipos' => 'Están dados de baja: '.$etiqueta($deBaja).'. Quítalos de la lista.']);
            }

            $responsable = $colaborador !== null
                ? $colaboradores->columnasAsignacion($colaborador)
                : ['nombre_colaborador' => null, 'numero_colaborador' => null] + ($colaboradores->disponible() ? ['id_usuario' => null] : []);

            $filas = array_map(fn (int $id): array => [
                'id_equipo' => $id,
                ...$responsable,
                'id_unidad' => $organizacion['id_unidad'],
                'id_departamento' => $organizacion['id_departamento'],
                'id_ubicacion' => $organizacion['id_ubicacion'],
                'id_tipo_asignacion' => (int) $validated['idTipoAsignacion'],
                'fecha_asignacion' => $fecha,
                'fecha_fin' => null,
                'observaciones' => $this->nullableString($validated['observacionesAsignacion']),
                'asignado_por' => auth()->id(),
            ], $ids);

            try {
                // Savepoint: si la columna no acepta vacío, se reintenta abajo.
                DB::transaction(fn () => DB::table('asignaciones')->insert($filas));
            } catch (QueryException $e) {
                // Sin responsable y la columna no admite NULL: se guarda vacío.
                if ($destino !== 'departamento' || ($e->errorInfo[0] ?? null) !== '23502') {
                    throw $e;
                }

                DB::table('asignaciones')->insert(array_map(
                    fn (array $fila): array => ['nombre_colaborador' => ''] + $fila,
                    $filas
                ));
            }

            $estadoAsignado = DB::table('estados_equipo')->whereRaw('LOWER(clave) = ?', ['asignado'])->value('id_estado_equipo');

            if ($estadoAsignado !== null) {
                DB::table('equipos')->whereIn('id_equipo', $ids)->update(['id_estado_activo' => $estadoAsignado]);
            }

            return $equipos->pluck('codigo_inventario')->map(fn ($c) => (string) $c)->sort()->values()->all();
        });

        // Cachés (asignaciones, inventario, vista general).
        $cache = app(\App\Services\SystemCacheService::class);
        $cache->refreshModule('asignaciones');
        $cache->refreshModule('equipos');
        $cache->refreshModule('dashboard');

        $total = count($codigos);
        $nombreDestino = $colaborador !== null
            ? (string) $colaborador['nombre']
            : 'el departamento '.(string) DB::table('departamentos')->where('id_departamento', $organizacion['id_departamento'])->value('nombre');

        try {
            $notificaciones = app(\App\Services\NotificacionService::class);

            $notificaciones->notificar(
                'asignaciones',
                $total === 1 ? 'Equipo asignado' : 'Equipos asignados',
                $notificaciones->nombreActor().' asignó '.($total === 1 ? 'el equipo ' : $total.' equipos (')
                    .implode(', ', array_slice($codigos, 0, 5)).($total > 5 ? ' y '.($total - 5).' más' : '').($total === 1 ? '' : ')')
                    .' a '.$nombreDestino.'.',
                route('asignaciones.index', [], false)
            );

            $this->dispatch('notificaciones-actualizadas');
        } catch (\Throwable $e) {
            report($e);
        }

        session()->flash('toast', [
            'type' => 'success',
            'title' => $total === 1 ? 'Equipo asignado' : $total.' equipos asignados',
            'message' => 'Asignados a '.$nombreDestino.'.'
                .($colaborador !== null && ($colaborador['sin_cuenta'] ?? false)
                    ? ' «'.$colaborador['nombre'].'» no tiene cuenta: un administrador deberá crearla en Seguridad y roles.'
                    : ''),
        ]);

        return ['ok' => true, 'total' => $total, 'volver' => $this->volverA];
    }

    /*
     * Equipos de la preselección que no se pueden asignar (asignados o de
     * baja): se avisa al abrir la página.
     */
    private function noDisponibles(array $disponibles): array
    {
        $idsDisponibles = array_map(fn ($e) => (int) $e['value'], $disponibles);
        $fuera = array_values(array_diff($this->preseleccion, $idsDisponibles));

        if ($fuera === []) {
            return [];
        }

        return DB::table('equipos')->whereIn('id_equipo', $fuera)->orderBy('codigo_inventario')->pluck('codigo_inventario')->map(fn ($c) => (string) $c)->all();
    }

    public function render(): View
    {
        $equipos = $this->getEquiposDisponibles();
        $organizacion = app(\App\Services\UbicacionOrganizacionalService::class)->datosFormulario();
        $tipos = $this->getTiposAsignacion();

        $idsDisponibles = array_map(fn ($e) => (string) $e['value'], $equipos);

        return view('livewire.asignacion-varios', [
            'datos' => [
                'equipos' => $equipos,
                'unidades' => $organizacion['unidades'],
                'departamentos' => $organizacion['departamentos'],
                'ubicaciones' => $organizacion['ubicaciones'],
                'tiposAsignacion' => $tipos,
                'usuarios' => app(\App\Services\ColaboradoresService::class)->opcionesUsuarios(),
                'preseleccion' => array_values(array_intersect(array_map('strval', $this->preseleccion), $idsDisponibles)),
                'maximo' => self::MAXIMO,
                'defaults' => [
                    'fechaAsignacion' => now()->toDateString(),
                    'idTipoAsignacion' => count($tipos) === 1 ? (string) $tipos[0]['value'] : '',
                ],
            ],
            'noDisponibles' => $this->noDisponibles($equipos),
        ]);
    }
}
