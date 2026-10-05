<?php

namespace App\Livewire\Catalogos;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Json;
use Livewire\Component;
use Livewire\WithPagination;

class MarcasModelos extends Component
{
    use WithPagination;

    private const CATALOG_FRONTEND_CACHE_KEY =
        'catalogos.marcas-modelos.frontend.v1';

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public string $search = '';

    public string $estado = 'todos';

    public string $tipo = 'todos';

    public int $perPageMarcas = 10;

    public int $perPageModelos = 10;

    public string $sortMarcas = 'asc';

    public string $sortModelos = 'asc';

    /*
    |--------------------------------------------------------------------------
    | Modal / formulario
    |--------------------------------------------------------------------------
    */

    public bool $modalOpen = false;

    public string $formType = 'marca';

    public string $formMode = 'create';

    public ?int $editingMarcaId = null;

    public ?int $editingModeloId = null;

    /*
    |--------------------------------------------------------------------------
    | Marca
    |--------------------------------------------------------------------------
    */

    public string $marcaNombre = '';

    public string $marcaDescripcion = '';

    public bool $marcaActivo = true;

    /*
    |--------------------------------------------------------------------------
    | Modelo
    |--------------------------------------------------------------------------
    */

    public string $modeloNombre = '';

    public string $modeloDescripcion = '';

    public ?int $modeloMarcaId = null;

    public ?int $modeloTipoEquipoId = null;

    public bool $modeloActivo = true;

    /*
    |--------------------------------------------------------------------------
    | Reiniciar paginación al filtrar
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        $this->resetCatalogPagination();
    }

    public function updatedEstado(): void
    {
        $this->resetCatalogPagination();
    }

    public function updatedTipo(): void
    {
        $this->resetPage(
            pageName: 'modelosPage'
        );
    }

    public function updatedPerPageMarcas(): void
    {
        $this->resetPage(
            pageName: 'marcasPage'
        );
    }

    public function updatedPerPageModelos(): void
    {
        $this->resetPage(
            pageName: 'modelosPage'
        );
    }

    public function updatedSortMarcas(): void
    {
        if (
            ! in_array(
                $this->sortMarcas,
                ['asc', 'desc'],
                true
            )
        ) {
            $this->sortMarcas = 'asc';
        }

        $this->resetPage(
            pageName: 'marcasPage'
        );
    }

    public function updatedSortModelos(): void
    {
        if (
            ! in_array(
                $this->sortModelos,
                ['asc', 'desc'],
                true
            )
        ) {
            $this->sortModelos = 'asc';
        }

        $this->resetPage(
            pageName: 'modelosPage'
        );
    }

    private function resetCatalogPagination(): void
    {
        $this->resetPage(
            pageName: 'marcasPage'
        );

        $this->resetPage(
            pageName: 'modelosPage'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Nueva marca
    |--------------------------------------------------------------------------
    */

    public function createMarca(): void
    {
        $this->resetForm();

        $this->formType = 'marca';

        $this->formMode = 'create';

        $this->marcaActivo = true;

        $this->modalOpen = true;

        $this->dispatch(
            'catalog-form-opened'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Nuevo modelo
    |--------------------------------------------------------------------------
    */

    public function createModelo(): void
    {
        $this->resetForm();

        $this->formType = 'modelo';

        $this->formMode = 'create';

        $this->modeloActivo = true;

        $this->modalOpen = true;

        $this->dispatch(
            'catalog-form-opened'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Editar marca
    |--------------------------------------------------------------------------
    */

    public function editMarca(
        int $marcaId
    ): void {
        $marca = DB::table('marcas')
            ->where(
                'id_marca',
                $marcaId
            )
            ->first();

        if (! $marca) {
            return;
        }

        $this->resetForm();

        $this->formType = 'marca';

        $this->formMode = 'edit';

        $this->editingMarcaId =
            (int) $marca->id_marca;

        $this->marcaNombre =
            (string) $marca->nombre;

        $this->marcaDescripcion =
            (string) ($marca->descripcion ?? '');

        $this->marcaActivo =
            (bool) $marca->activo;

        $this->modalOpen = true;

        $this->dispatch(
            'catalog-form-opened'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Editar modelo
    |--------------------------------------------------------------------------
    */

    public function editModelo(
        int $modeloId
    ): void {
        $modelo = DB::table('modelos')
            ->where(
                'id_modelo',
                $modeloId
            )
            ->first();

        if (! $modelo) {
            return;
        }

        $this->resetForm();

        $this->formType = 'modelo';

        $this->formMode = 'edit';

        $this->editingModeloId =
            (int) $modelo->id_modelo;

        $this->modeloNombre =
            (string) $modelo->nombre;

        $this->modeloDescripcion =
            (string) ($modelo->descripcion ?? '');

        $this->modeloMarcaId =
            (int) $modelo->id_marca;

        $this->modeloTipoEquipoId =
            (int) $modelo->id_tipo_equipo;

        $this->modeloActivo =
            (bool) $modelo->activo;

        $this->modalOpen = true;

        $this->dispatch(
            'catalog-form-opened'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar formulario
    |--------------------------------------------------------------------------
    */

    public function saveCatalogItem(): void
    {
        if ($this->formType === 'modelo') {
            $this->saveModelo();

            return;
        }

        $this->saveMarca();
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar marca
    |--------------------------------------------------------------------------
    */

    private function saveMarca(): void
    {
        $validated = $this->validate([
            'marcaNombre' => [
                'required',
                'string',
                'max:255',
            ],

            'marcaDescripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'marcaActivo' => [
                'boolean',
            ],
        ], [
            'marcaNombre.required' =>
                'Ingresa el nombre de la marca.',

            'marcaNombre.max' =>
                'El nombre no puede superar los 255 caracteres.',

            'marcaDescripcion.max' =>
                'La descripción no puede superar los 1000 caracteres.',
        ]);

        $nombre =
            trim(
                $validated['marcaNombre']
            );

        /*
         * Validación adicional amigable antes
         * de dejar que actúe el índice UNIQUE.
         */

        $duplicate = DB::table('marcas')
            ->whereRaw(
                'LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))',
                [$nombre]
            )
            ->when(
                $this->editingMarcaId !== null,
                fn ($query) =>
                    $query->where(
                        'id_marca',
                        '!=',
                        $this->editingMarcaId
                    )
            )
            ->exists();

        if ($duplicate) {
            $this->addError(
                'marcaNombre',
                'Ya existe una marca con este nombre.'
            );

            return;
        }

        DB::transaction(
            function () use (
                $validated,
                $nombre
            ): void {
                if (
                    $this->formMode === 'edit'
                    &&
                    $this->editingMarcaId !== null
                ) {
                    DB::table('marcas')
                        ->where(
                            'id_marca',
                            $this->editingMarcaId
                        )
                        ->update([
                            'nombre' =>
                                $nombre,

                            'descripcion' =>
                                filled(
                                    $validated['marcaDescripcion']
                                )
                                    ? trim(
                                        $validated['marcaDescripcion']
                                    )
                                    : null,

                            'activo' =>
                                (bool) $validated['marcaActivo'],

                            'fecha_actualizacion' =>
                                now(),
                        ]);

                    $accion =
                        'MARCA_ACTUALIZADA';

                    $descripcion =
                        "Se actualizó la marca {$nombre}.";
                } else {
                    DB::table('marcas')
                        ->insert([
                            'nombre' =>
                                $nombre,

                            'descripcion' =>
                                filled(
                                    $validated['marcaDescripcion']
                                )
                                    ? trim(
                                        $validated['marcaDescripcion']
                                    )
                                    : null,

                            'activo' =>
                                (bool) $validated['marcaActivo'],

                            'fecha_creacion' =>
                                now(),

                            'fecha_actualizacion' =>
                                now(),
                        ]);

                    $accion =
                        'MARCA_CREADA';

                    $descripcion =
                        "Se creó la marca {$nombre}.";
                }

                $this->writeAudit(
                    $accion,
                    $descripcion
                );
            }
        );

        $this->forgetCatalogFrontendCache();

        $this->finishSave(
            'La marca se guardó correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar modelo
    |--------------------------------------------------------------------------
    */

    private function saveModelo(): void
    {
        $validated = $this->validate([
            'modeloNombre' => [
                'required',
                'string',
                'max:255',
            ],

            'modeloDescripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'modeloMarcaId' => [
                'required',
                'integer',
            ],

            'modeloTipoEquipoId' => [
                'required',
                'integer',
            ],

            'modeloActivo' => [
                'boolean',
            ],
        ], [
            'modeloNombre.required' =>
                'Ingresa el nombre del modelo.',

            'modeloNombre.max' =>
                'El nombre no puede superar los 255 caracteres.',

            'modeloDescripcion.max' =>
                'La descripción no puede superar los 1000 caracteres.',

            'modeloMarcaId.required' =>
                'Selecciona una marca.',

            'modeloTipoEquipoId.required' =>
                'Selecciona un tipo de equipo.',
        ]);

        /*
         * Comprobar relaciones manualmente.
         */

        $marcaExists = DB::table('marcas')
            ->where(
                'id_marca',
                $validated['modeloMarcaId']
            )
            ->exists();

        if (! $marcaExists) {
            $this->addError(
                'modeloMarcaId',
                'La marca seleccionada no es válida.'
            );

            return;
        }

        $tipoExists = DB::table('tipos_equipo')
            ->where(
                'id_tipo_equipo',
                $validated['modeloTipoEquipoId']
            )
            ->exists();

        if (! $tipoExists) {
            $this->addError(
                'modeloTipoEquipoId',
                'El tipo de equipo seleccionado no es válido.'
            );

            return;
        }

        $nombre =
            trim(
                $validated['modeloNombre']
            );

        /*
         * Un modelo se considera repetido cuando
         * coinciden marca + tipo + nombre.
         */

        $duplicate = DB::table('modelos')
            ->where(
                'id_marca',
                $validated['modeloMarcaId']
            )
            ->where(
                'id_tipo_equipo',
                $validated['modeloTipoEquipoId']
            )
            ->whereRaw(
                'LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))',
                [$nombre]
            )
            ->when(
                $this->editingModeloId !== null,
                fn ($query) =>
                    $query->where(
                        'id_modelo',
                        '!=',
                        $this->editingModeloId
                    )
            )
            ->exists();

        if ($duplicate) {
            $this->addError(
                'modeloNombre',
                'Ya existe este modelo para la marca y tipo de equipo seleccionados.'
            );

            return;
        }

        DB::transaction(
            function () use (
                $validated,
                $nombre
            ): void {
                if (
                    $this->formMode === 'edit'
                    &&
                    $this->editingModeloId !== null
                ) {
                    DB::table('modelos')
                        ->where(
                            'id_modelo',
                            $this->editingModeloId
                        )
                        ->update([
                            'id_marca' =>
                                $validated['modeloMarcaId'],

                            'id_tipo_equipo' =>
                                $validated['modeloTipoEquipoId'],

                            'nombre' =>
                                $nombre,

                            'descripcion' =>
                                filled(
                                    $validated['modeloDescripcion']
                                )
                                    ? trim(
                                        $validated['modeloDescripcion']
                                    )
                                    : null,

                            'activo' =>
                                (bool) $validated['modeloActivo'],

                            'fecha_actualizacion' =>
                                now(),
                        ]);

                    $accion =
                        'MODELO_ACTUALIZADO';

                    $descripcion =
                        "Se actualizó el modelo {$nombre}.";
                } else {
                    DB::table('modelos')
                        ->insert([
                            'id_marca' =>
                                $validated['modeloMarcaId'],

                            'id_tipo_equipo' =>
                                $validated['modeloTipoEquipoId'],

                            'nombre' =>
                                $nombre,

                            'descripcion' =>
                                filled(
                                    $validated['modeloDescripcion']
                                )
                                    ? trim(
                                        $validated['modeloDescripcion']
                                    )
                                    : null,

                            'activo' =>
                                (bool) $validated['modeloActivo'],

                            'fecha_creacion' =>
                                now(),

                            'fecha_actualizacion' =>
                                now(),
                        ]);

                    $accion =
                        'MODELO_CREADO';

                    $descripcion =
                        "Se creó el modelo {$nombre}.";
                }

                $this->writeAudit(
                    $accion,
                    $descripcion
                );
            }
        );

        $this->forgetCatalogFrontendCache();

        $this->finishSave(
            'El modelo se guardó correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Activar / desactivar marca
    |--------------------------------------------------------------------------
    */

    #[Json]
    public function toggleMarcaStatus(
        int $marcaId
    ): array {
        $marca = DB::table('marcas')
            ->where(
                'id_marca',
                $marcaId
            )
            ->first([
                'id_marca',
                'nombre',
                'activo',
            ]);

        if (! $marca) {
            return [
                'ok' => false,
                'message' =>
                    'La marca ya no existe.',
            ];
        }

        $newStatus =
            ! (bool) $marca->activo;

        DB::transaction(
            function () use (
                $marca,
                $newStatus
            ): void {
                DB::table('marcas')
                    ->where(
                        'id_marca',
                        $marca->id_marca
                    )
                    ->update([
                        'activo' =>
                            $newStatus,

                        'fecha_actualizacion' =>
                            now(),
                    ]);

                $this->writeAudit(
                    $newStatus
                        ? 'MARCA_ACTIVADA'
                        : 'MARCA_DESACTIVADA',

                    $newStatus
                        ? "Se activó la marca {$marca->nombre}."
                        : "Se desactivó la marca {$marca->nombre}."
                );
            }
        );

        /*
         * Crear / Editar equipo utiliza este catálogo.
         */

        Cache::forget(
            'form.marcas.v2'
        );

        $this->forgetCatalogFrontendCache();

        return [
            'ok' =>
                true,

            'id' =>
                (int) $marca->id_marca,

            'active' =>
                $newStatus,

            'message' =>
                $newStatus
                    ? 'La marca fue activada.'
                    : 'La marca fue desactivada.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Activar / desactivar modelo
    |--------------------------------------------------------------------------
    */

    #[Json]
    public function toggleModeloStatus(
        int $modeloId
    ): array {
        $modelo = DB::table('modelos')
            ->where(
                'id_modelo',
                $modeloId
            )
            ->first([
                'id_modelo',
                'nombre',
                'activo',
            ]);

        if (! $modelo) {
            return [
                'ok' => false,
                'message' =>
                    'El modelo ya no existe.',
            ];
        }

        $newStatus =
            ! (bool) $modelo->activo;

        DB::transaction(
            function () use (
                $modelo,
                $newStatus
            ): void {
                DB::table('modelos')
                    ->where(
                        'id_modelo',
                        $modelo->id_modelo
                    )
                    ->update([
                        'activo' =>
                            $newStatus,

                        'fecha_actualizacion' =>
                            now(),
                    ]);

                $this->writeAudit(
                    $newStatus
                        ? 'MODELO_ACTIVADO'
                        : 'MODELO_DESACTIVADO',

                    $newStatus
                        ? "Se activó el modelo {$modelo->nombre}."
                        : "Se desactivó el modelo {$modelo->nombre}."
                );
            }
        );

        /*
         * Crear / Editar equipo utiliza esta colección.
         */

        Cache::forget(
            'form.modelos.all.v2'
        );

        $this->forgetCatalogFrontendCache();

        return [
            'ok' =>
                true,

            'id' =>
                (int) $modelo->id_modelo,

            'active' =>
                $newStatus,

            'message' =>
                $newStatus
                    ? 'El modelo fue activado.'
                    : 'El modelo fue desactivado.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Cerrar formulario
    |--------------------------------------------------------------------------
    */

    public function closeForm(): void
    {
        $this->modalOpen = false;

        $this->resetForm();
    }

    /*
    |--------------------------------------------------------------------------
    | Reiniciar formulario
    |--------------------------------------------------------------------------
    */

    private function resetForm(): void
    {
        $this->resetValidation();

        $this->editingMarcaId = null;

        $this->editingModeloId = null;

        $this->marcaNombre = '';

        $this->marcaDescripcion = '';

        $this->marcaActivo = true;

        $this->modeloNombre = '';

        $this->modeloDescripcion = '';

        $this->modeloMarcaId = null;

        $this->modeloTipoEquipoId = null;

        $this->modeloActivo = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Finalizar guardado
    |--------------------------------------------------------------------------
    */

    private function finishSave(
        string $message
    ): void {
        $this->modalOpen = false;

        $this->resetForm();

        $this->resetCatalogPagination();

        $this->dispatch(
            'catalog-item-saved',
            message: $message
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Bitácora
    |--------------------------------------------------------------------------
    */

    private function writeAudit(
        string $action,
        string $description
    ): void {
        DB::table('bitacora_auditoria')
            ->insert([
                'id_usuario' =>
                    auth()->id(),

                'accion' =>
                    $action,

                'modulo' =>
                    'CATALOGOS',

                'descripcion' =>
                    $description,

                'fecha_hora' =>
                    now(),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar caché del catálogo
    |--------------------------------------------------------------------------
    */

    private function forgetCatalogFrontendCache(): void
    {
        Cache::forget(
            self::CATALOG_FRONTEND_CACHE_KEY
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Datos iniciales para Alpine
    |--------------------------------------------------------------------------
    */

    private function getCatalogFrontendData(): array
    {
        return Cache::remember(
            self::CATALOG_FRONTEND_CACHE_KEY,
            now()->addMinutes(5),
            function (): array {

                /*
                |--------------------------------------------------------------------------
                | Conteos agrupados
                |--------------------------------------------------------------------------
                |
                | Evitamos subconsultas correlacionadas por cada fila.
                |
                */

                $equiposPorMarca =
                    DB::table('equipos')
                        ->select('id_marca')
                        ->selectRaw(
                            'COUNT(*) AS total_equipos'
                        )
                        ->whereNotNull(
                            'id_marca'
                        )
                        ->groupBy(
                            'id_marca'
                        );

                $modelosPorMarca =
                    DB::table('modelos')
                        ->select('id_marca')
                        ->selectRaw(
                            'COUNT(*) AS total_modelos'
                        )
                        ->groupBy(
                            'id_marca'
                        );

                $equiposPorModelo =
                    DB::table('equipos')
                        ->select('id_modelo')
                        ->selectRaw(
                            'COUNT(*) AS total_equipos'
                        )
                        ->whereNotNull(
                            'id_modelo'
                        )
                        ->groupBy(
                            'id_modelo'
                        );

                /*
                |--------------------------------------------------------------------------
                | Marcas
                |--------------------------------------------------------------------------
                */

                $marcas =
                    DB::table('marcas as m')
                        ->leftJoinSub(
                            $equiposPorMarca,
                            'em',
                            function ($join) {
                                $join->on(
                                    'em.id_marca',
                                    '=',
                                    'm.id_marca'
                                );
                            }
                        )
                        ->leftJoinSub(
                            $modelosPorMarca,
                            'mm',
                            function ($join) {
                                $join->on(
                                    'mm.id_marca',
                                    '=',
                                    'm.id_marca'
                                );
                            }
                        )
                        ->orderBy(
                            'm.nombre'
                        )
                        ->get([
                            'm.id_marca',
                            'm.nombre',
                            'm.descripcion',
                            'm.activo',
                            'm.fecha_creacion',
                            'm.fecha_actualizacion',

                            DB::raw(
                                'COALESCE(em.total_equipos, 0) AS total_equipos'
                            ),

                            DB::raw(
                                'COALESCE(mm.total_modelos, 0) AS total_modelos'
                            ),
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Modelos
                |--------------------------------------------------------------------------
                */

                $modelos =
                    DB::table('modelos as mo')
                        ->join(
                            'marcas as m',
                            'm.id_marca',
                            '=',
                            'mo.id_marca'
                        )
                        ->join(
                            'tipos_equipo as te',
                            'te.id_tipo_equipo',
                            '=',
                            'mo.id_tipo_equipo'
                        )
                        ->leftJoinSub(
                            $equiposPorModelo,
                            'epm',
                            function ($join) {
                                $join->on(
                                    'epm.id_modelo',
                                    '=',
                                    'mo.id_modelo'
                                );
                            }
                        )
                        ->orderBy(
                            'm.nombre'
                        )
                        ->orderBy(
                            'mo.nombre'
                        )
                        ->get([
                            'mo.id_modelo',
                            'mo.nombre',
                            'mo.descripcion',
                            'mo.activo',
                            'mo.fecha_creacion',
                            'mo.fecha_actualizacion',

                            'm.id_marca',
                            'm.nombre as marca_nombre',

                            'te.id_tipo_equipo',
                            'te.nombre as tipo_equipo_nombre',

                            DB::raw(
                                'COALESCE(epm.total_equipos, 0) AS total_equipos'
                            ),
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Tipos de equipo disponibles para filtro
                |--------------------------------------------------------------------------
                */

                $tiposEquipo =
                    DB::table('tipos_equipo')
                        ->where(
                            'activo',
                            true
                        )
                        ->orderBy(
                            'nombre'
                        )
                        ->get([
                            'id_tipo_equipo',
                            'nombre',
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Formato para Alpine
                |--------------------------------------------------------------------------
                */

                $marcasFrontend =
                    $marcas
                        ->map(
                            fn ($marca): array => [
                                'id' =>
                                    (int) $marca->id_marca,

                                'nombre' =>
                                    (string) $marca->nombre,

                                'descripcion' =>
                                    (string) (
                                        $marca->descripcion
                                        ?? ''
                                    ),

                                'activo' =>
                                    (bool) $marca->activo,

                                'fechaCreacion' =>
                                    (string) (
                                        $marca->fecha_creacion
                                        ?? ''
                                    ),

                                'fechaActualizacion' =>
                                    (string) (
                                        $marca->fecha_actualizacion
                                        ?? ''
                                    ),

                                'totalEquipos' =>
                                    (int) $marca->total_equipos,

                                'totalModelos' =>
                                    (int) $marca->total_modelos,

                                'editUrl' =>
                                    route(
                                        'catalogos.edit',
                                        [
                                            'tipo' =>
                                                'marca',

                                            'registro' =>
                                                (int) $marca->id_marca,
                                        ]
                                    ),
                            ]
                        )
                        ->values()
                        ->all();

                $modelosFrontend =
                    $modelos
                        ->map(
                            fn ($modelo): array => [
                                'id' =>
                                    (int) $modelo->id_modelo,

                                'nombre' =>
                                    (string) $modelo->nombre,

                                'descripcion' =>
                                    (string) (
                                        $modelo->descripcion
                                        ?? ''
                                    ),

                                'activo' =>
                                    (bool) $modelo->activo,

                                'fechaCreacion' =>
                                    (string) (
                                        $modelo->fecha_creacion
                                        ?? ''
                                    ),

                                'fechaActualizacion' =>
                                    (string) (
                                        $modelo->fecha_actualizacion
                                        ?? ''
                                    ),

                                'idMarca' =>
                                    (string) $modelo->id_marca,

                                'marcaNombre' =>
                                    (string) $modelo->marca_nombre,

                                'idTipoEquipo' =>
                                    (string) $modelo->id_tipo_equipo,

                                'tipoEquipoNombre' =>
                                    (string) $modelo->tipo_equipo_nombre,

                                'totalEquipos' =>
                                    (int) $modelo->total_equipos,

                                'editUrl' =>
                                    route(
                                        'catalogos.edit',
                                        [
                                            'tipo' =>
                                                'modelo',

                                            'registro' =>
                                                (int) $modelo->id_modelo,
                                        ]
                                    ),
                            ]
                        )
                        ->values()
                        ->all();

                $tiposFrontend =
                    $tiposEquipo
                        ->map(
                            fn ($tipo): array => [
                                'value' =>
                                    (string) $tipo->id_tipo_equipo,

                                'label' =>
                                    (string) $tipo->nombre,
                            ]
                        )
                        ->values()
                        ->all();

                /*
                |--------------------------------------------------------------------------
                | Métricas
                |--------------------------------------------------------------------------
                |
                | Ya tenemos todos los registros en memoria.
                | No hacemos otras cuatro consultas COUNT().
                |
                */

                $metrics = [
                    'totalMarcas' =>
                        count(
                            $marcasFrontend
                        ),

                    'totalModelos' =>
                        count(
                            $modelosFrontend
                        ),

                    'marcasInactivas' =>
                        collect(
                            $marcasFrontend
                        )
                            ->where(
                                'activo',
                                false
                            )
                            ->count(),

                    'modelosEnUso' =>
                        collect(
                            $modelosFrontend
                        )
                            ->filter(
                                fn (array $modelo): bool =>
                                    $modelo['totalEquipos'] > 0
                            )
                            ->count(),
                ];

                /*
                |--------------------------------------------------------------------------
                | Opciones estáticas
                |--------------------------------------------------------------------------
                */

                return [
                    'marcas' =>
                        $marcasFrontend,

                    'modelos' =>
                        $modelosFrontend,

                    'tiposEquipo' =>
                        $tiposFrontend,

                    'metrics' =>
                        $metrics,

                    'filters' => [
                        'estados' => [
                            [
                                'value' => 'todos',
                                'label' => 'Todos los estados',
                            ],
                            [
                                'value' => 'activos',
                                'label' => 'Activos',
                            ],
                            [
                                'value' => 'inactivos',
                                'label' => 'Inactivos',
                            ],
                        ],

                        'perPage' => [
                            [
                                'value' => '10',
                                'label' => '10',
                            ],
                            [
                                'value' => '25',
                                'label' => '25',
                            ],
                            [
                                'value' => '50',
                                'label' => '50',
                            ],
                            [
                                'value' => '100',
                                'label' => '100',
                            ],
                        ],

                        'sort' => [
                            [
                                'value' => 'asc',
                                'label' => 'ASC',
                            ],
                            [
                                'value' => 'desc',
                                'label' => 'DESC',
                            ],
                        ],
                    ],

                    'defaults' => [
                        'search' => '',
                        'estado' => 'todos',
                        'tipo' => 'todos',

                        'perPageMarcas' => '10',
                        'perPageModelos' => '10',

                        'sortMarcas' => 'asc',
                        'sortModelos' => 'asc',
                    ],
                ];
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view(
            'livewire.placeholders.catalogos-marcas-modelos'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        return view(
            'livewire.catalogos.marcas-modelos',
            [
                'catalogData' =>
                    $this->getCatalogFrontendData(),
            ]
        );
    }
}