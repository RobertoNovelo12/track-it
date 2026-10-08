<?php

namespace App\Livewire;

use App\Models\Area;
use App\Models\CatalogoValor;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\TipoEquipo;
use App\Services\SystemCacheService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EquiposIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    private const PER_PAGE_OPTIONS = [
        10,
        25,
        50,
        100,
    ];

    private const CACHE_MODULE =
        'equipos';


    private const CATALOG_CACHE_MODULE =
        'catalogos';


    private const TABLE_CACHE_TTL_HOURS =
        12;


    private const CATALOG_CACHE_TTL_HOURS =
        24;


    private const EDIT_BASE_CACHE_TTL_MINUTES =
        10;


    private const SORTABLE_COLUMNS = [
        'codigo_inventario' => 'e.codigo_inventario',

        'nombre_equipo' => 'e.nombre_equipo',

        'tipo_equipo' => 'te.nombre',

        'marca' => 'ma.nombre',

        'modelo' => 'mo.nombre',

        'numero_serie' => 'e.numero_serie',

        /*
         * Se conserva porque Service Tag seguirá
         * existiendo como filtro y para compatibilidad
         * con URLs anteriores.
         */
        'service_tag' => 'e.service_tag',

        'direccion_ip' => 'e.direccion_ip',

        'estado' => 'cv.nombre',

        /*
         * Esta columna es calculada.
         * Se ordena de forma especial en applySorting().
         */
        'ubicacion_organizacional' => null,
    ];


    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA GENERAL
    |--------------------------------------------------------------------------
    */

    #[Url(as: 'buscar')]
    public ?string $search = null;


    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    |
    | Son nullable porque los searchable-select pueden representar
    | "Todos" / "Sin selección" como null.
    |
    */

    #[Url(as: 'tipo_activo')]
    public ?string $tipoActivo = null;

    #[Url]
    public ?string $marca = null;

    #[Url]
    public ?string $modelo = null;

    #[Url(as: 'nombre_equipo')]
    public ?string $nombreEquipo = null;

    #[Url(as: 'numero_serie')]
    public ?string $numeroSerie = null;

    #[Url(as: 'service_tag')]
    public ?string $serviceTag = null;

    #[Url(as: 'direccion_ip')]
    public ?string $direccionIp = null;

    #[Url]
    public ?string $estado = null;

    #[Url]
    public ?string $area = null;


    /*
    |--------------------------------------------------------------------------
    | PAGINACIÓN
    |--------------------------------------------------------------------------
    */

    #[Url(as: 'per_page')]
    public int $perPage = 10;


    /*
    |--------------------------------------------------------------------------
    | ORDENAMIENTO
    |--------------------------------------------------------------------------
    */

    #[Url(as: 'ordenar_por')]
    public string $sortField = 'codigo_inventario';

    #[Url]
    public string $sort = 'asc';


    /*
    |--------------------------------------------------------------------------
    | RESETEAR PAGINACIÓN AL CAMBIAR FILTROS
    |--------------------------------------------------------------------------
    */

    public function updating(
        string $property
    ): void {
        if (
            in_array(
                $property,
                [
                    'search',
                    'tipoActivo',
                    'marca',
                    'modelo',
                    'nombreEquipo',
                    'numeroSerie',
                    'serviceTag',
                    'direccionIp',
                    'estado',
                    'area',
                ],
                true
            )
        ) {
            $this->resetPage();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE CANTIDAD POR PÁGINA
    |--------------------------------------------------------------------------
    */

    public function updatedPerPage(): void
    {
        if (
            ! in_array(
                $this->perPage,
                self::PER_PAGE_OPTIONS,
                true
            )
        ) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE DIRECCIÓN DE ORDENAMIENTO
    |--------------------------------------------------------------------------
    */

    public function updatedSort(): void
    {
        if (
            ! in_array(
                $this->sort,
                [
                    'asc',
                    'desc',
                ],
                true
            )
        ) {
            $this->sort = 'asc';
        }

        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE COLUMNA DE ORDENAMIENTO
    |--------------------------------------------------------------------------
    */

    public function updatedSortField(): void
    {
        if (
            ! array_key_exists(
                $this->sortField,
                self::SORTABLE_COLUMNS
            )
        ) {
            $this->sortField =
                'codigo_inventario';
        }

        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | ORDENAR DESDE ENCABEZADO
    |--------------------------------------------------------------------------
    */

    public function sortBy(
        string $field
    ): void {
        if (
            ! array_key_exists(
                $field,
                self::SORTABLE_COLUMNS
            )
        ) {
            return;
        }

        if (
            $this->sortField === $field
        ) {
            $this->sort =
                $this->sort === 'asc'
                    ? 'desc'
                    : 'asc';
        } else {
            $this->sortField = $field;
            $this->sort = 'asc';
        }

        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR FILTROS
    |--------------------------------------------------------------------------
    |
    | No se limpia la búsqueda general porque continúa siendo independiente.
    |
    */

    public function resetFilters(): void
    {
        $this->tipoActivo = null;
        $this->marca = null;
        $this->modelo = null;

        $this->nombreEquipo = null;

        $this->numeroSerie = null;
        $this->serviceTag = null;
        $this->direccionIp = null;
        $this->estado = null;
        $this->area = null;

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    private function systemCache(): SystemCacheService
    {
        return app(
            SystemCacheService::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR TABLA
    |--------------------------------------------------------------------------
    */

    public function refreshTable(): void
    {
        $this->systemCache()
            ->refreshModule(
                self::CACHE_MODULE
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CLAVE DE CACHÉ DE LA TABLA
    |--------------------------------------------------------------------------
    */

        private function tableCacheState(
            int $perPage,
            int $page
        ): array {
        $state = [
            'page' =>
                $page,

            'per_page' =>
                $perPage,

            'search' =>
                trim(
                    (string) (
                        $this->search
                        ?? ''
                    )
                ),

            'tipo_activo' =>
                trim(
                    (string) (
                        $this->tipoActivo
                        ?? ''
                    )
                ),

            'marca' =>
                trim(
                    (string) (
                        $this->marca
                        ?? ''
                    )
                ),

            'modelo' =>
                trim(
                    (string) (
                        $this->modelo
                        ?? ''
                    )
                ),

            'nombre_equipo' =>
                trim(
                    (string) (
                        $this->nombreEquipo
                        ?? ''
                    )
                ),

            'numero_serie' =>
                trim(
                    (string) (
                        $this->numeroSerie
                        ?? ''
                    )
                ),

            'service_tag' =>
                trim(
                    (string) (
                        $this->serviceTag
                        ?? ''
                    )
                ),

            'direccion_ip' =>
                trim(
                    (string) (
                        $this->direccionIp
                        ?? ''
                    )
                ),

            'estado' =>
                trim(
                    (string) (
                        $this->estado
                        ?? ''
                    )
                ),

            'area' =>
                trim(
                    (string) (
                        $this->area
                        ?? ''
                    )
                ),

            'sort_field' =>
                array_key_exists(
                    $this->sortField,
                    self::SORTABLE_COLUMNS
                )
                    ? $this->sortField
                    : 'codigo_inventario',

            'sort' =>
                $this->sort === 'desc'
                    ? 'desc'
                    : 'asc',
        ];


        return $state;
    }


    /*
    |--------------------------------------------------------------------------
    | PREPARAR CACHE BASE PARA EDICIÓN
    |--------------------------------------------------------------------------
    |
    | Se ejecuta únicamente cuando el listado realmente consulta PostgreSQL.
    |
    | Los registros que ya fueron traídos para construir la tabla se reutilizan
    | para preparar EquipoEdit.
    |
    | No se realizan consultas adicionales.
    |
    */

    private function primeEditBaseCache(
        array $items
    ): void {
        /*
        |--------------------------------------------------------------------------
        | CAMPOS QUE EQUIPOEDIT NECESITA
        |--------------------------------------------------------------------------
        */

        $requiredKeys = [
            'id_equipo',

            'codigo_inventario',
            'nombre_equipo',

            'host',

            'id_estado_activo',
            'id_tipo_equipo',
            'id_modelo',

            'fecha_compra',

            'id_marca',

            'direccion_mac',
            'numero_factura',
            'numero_serie',

            'id_proveedor',

            'id_condicion_activo',

            'id_area',
            'id_departamento',

            'departamento_area_id',

            'fecha_fin_garantia',

            'comentarios',
        ];


        foreach (
            $items
            as $item
        ) {
            /*
            |--------------------------------------------------------------------------
            | NORMALIZAR
            |--------------------------------------------------------------------------
            */

            $row =
                (array) $item;


            /*
            |--------------------------------------------------------------------------
            | PROTECCIÓN
            |--------------------------------------------------------------------------
            |
            | Si por alguna razón estamos trabajando con una versión antigua
            | de la cache de tabla que todavía no contiene estos campos,
            | simplemente no generamos una cache incompleta.
            |
            */

            $complete = true;


            foreach (
                $requiredKeys
                as $key
            ) {
                if (
                    ! array_key_exists(
                        $key,
                        $row
                    )
                ) {
                    $complete = false;

                    break;
                }
            }


            if (! $complete) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CACHE INDIVIDUAL DE EDICIÓN
            |--------------------------------------------------------------------------
            */

            Cache::put(
                'equipos.edit.base.'
                    . (int) $row['id_equipo'],

                [
                    'id_equipo' =>
                        $row['id_equipo'],

                    'codigo_inventario' =>
                        $row['codigo_inventario'],

                    'nombre_equipo' =>
                        $row['nombre_equipo'],

                    'host' =>
                        $row['host'],

                    'id_estado_activo' =>
                        $row['id_estado_activo'],

                    'id_tipo_equipo' =>
                        $row['id_tipo_equipo'],

                    'id_modelo' =>
                        $row['id_modelo'],

                    'fecha_compra' =>
                        $row['fecha_compra'],

                    'id_marca' =>
                        $row['id_marca'],

                    'direccion_mac' =>
                        $row['direccion_mac'],

                    'numero_factura' =>
                        $row['numero_factura'],

                    'numero_serie' =>
                        $row['numero_serie'],

                    'id_proveedor' =>
                        $row['id_proveedor'],

                    'id_condicion_activo' =>
                        $row['id_condicion_activo'],

                    'id_area' =>
                        $row['id_area'],

                    'id_departamento' =>
                        $row['id_departamento'],

                    'departamento_area_id' =>
                        $row['departamento_area_id'],

                    'fecha_fin_garantia' =>
                        $row['fecha_fin_garantia'],

                    'comentarios' =>
                        $row['comentarios'],
                ],

                now()->addMinutes(
                    self::EDIT_BASE_CACHE_TTL_MINUTES
                )
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CONSULTA BASE
    |--------------------------------------------------------------------------
    */

    private function equiposQuery(): Builder
    {
        return DB::table('equipos as e')

            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )

            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )

            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )

            ->leftJoin(
                'catalogo_valores as cv',
                'cv.id_valor',
                '=',
                'e.id_estado_activo'
            )

            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'e.id_departamento'
            )

            ->leftJoin(
                'areas as a_dep',
                'a_dep.id_area',
                '=',
                'dep.id_area'
            )

            ->leftJoin(
                'areas as a_dir',
                'a_dir.id_area',
                '=',
                'e.id_area'
            )

            ->select([
                /*
                |--------------------------------------------------------------------------
                | CAMPOS UTILIZADOS POR EL LISTADO
                |--------------------------------------------------------------------------
                */

                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.service_tag',
                'e.direccion_ip',

                'te.nombre as tipo_equipo_nombre',
                'ma.nombre as marca_nombre',
                'mo.nombre as modelo_nombre',
                'cv.nombre as estado_nombre',


                /*
                |--------------------------------------------------------------------------
                | DATOS BASE PARA EDICIÓN
                |--------------------------------------------------------------------------
                |
                | Estos campos viajan en la MISMA consulta del listado.
                |
                | No se ejecuta ninguna consulta adicional por equipo.
                |
                */

                'e.host',

                'e.id_estado_activo',
                'e.id_tipo_equipo',
                'e.id_modelo',

                'e.fecha_compra',

                'e.id_marca',

                'e.direccion_mac',
                'e.numero_factura',

                'e.id_proveedor',

                'e.id_condicion_activo',

                'e.id_area',
                'e.id_departamento',

                'e.fecha_fin_garantia',

                'e.comentarios',


                /*
                |--------------------------------------------------------------------------
                | ÁREA DEL DEPARTAMENTO
                |--------------------------------------------------------------------------
                |
                | EquipoEdit ya espera departamento_area_id cuando el equipo
                | pertenece a un departamento.
                |
                */

                'dep.id_area as departamento_area_id',
            ])

            ->selectRaw("
                COALESCE(
                    CASE
                        WHEN dep.id_departamento IS NOT NULL
                            THEN CASE
                                WHEN a_dep.nombre IS NOT NULL
                                    THEN a_dep.nombre || ' / ' || dep.nombre
                                ELSE dep.nombre
                            END
                        ELSE a_dir.nombre
                    END,
                    '—'
                ) as ubicacion_organizacional
            ");
    }


    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA GENERAL
    |--------------------------------------------------------------------------
    */

    private function applySearch(
        Builder $query
    ): void {
        $search =
            trim(
                (string) (
                    $this->search
                    ?? ''
                )
            );


        if ($search === '') {
            return;
        }


        $terms = preg_split(
            '/\s+/',
            $search,
            -1,
            PREG_SPLIT_NO_EMPTY
        );


        if (
            ! is_array($terms)
            ||
            $terms === []
        ) {
            return;
        }


        foreach (
            $terms
            as $term
        ) {
            $like =
                '%'
                . $term
                . '%';


            $query->where(
                function (
                    Builder $sub
                ) use (
                    $like
                ): void {
                    $sub

                        /*
                        |--------------------------------------------------------------------------
                        | Nombre del equipo
                        |--------------------------------------------------------------------------
                        */

                        ->where(
                            'e.nombre_equipo',
                            'ilike',
                            $like
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | ID interno
                        |--------------------------------------------------------------------------
                        */

                        ->orWhereRaw(
                            'CAST(e.id_equipo AS TEXT) ILIKE ?',
                            [
                                $like,
                            ]
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Código de inventario
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'e.codigo_inventario',
                            'ilike',
                            $like
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Número de serie
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'e.numero_serie',
                            'ilike',
                            $like
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Tipo
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'te.nombre',
                            'ilike',
                            $like
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Marca
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'ma.nombre',
                            'ilike',
                            $like
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Modelo
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'mo.nombre',
                            'ilike',
                            $like
                        );
                }
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        Builder $query
    ): void {
        /*
         * Tanto null como '' significan:
         * "sin filtro".
         */

        $tipoActivo =
            trim(
                (string) (
                    $this->tipoActivo
                    ?? ''
                )
            );


        $marca =
            trim(
                (string) (
                    $this->marca
                    ?? ''
                )
            );


        $modelo =
            trim(
                (string) (
                    $this->modelo
                    ?? ''
                )
            );


        $nombreEquipo =
            trim(
                (string) (
                    $this->nombreEquipo
                    ?? ''
                )
            );


        $numeroSerie =
            trim(
                (string) (
                    $this->numeroSerie
                    ?? ''
                )
            );


        $serviceTag =
            trim(
                (string) (
                    $this->serviceTag
                    ?? ''
                )
            );


        $direccionIp =
            trim(
                (string) (
                    $this->direccionIp
                    ?? ''
                )
            );


        $estado =
            trim(
                (string) (
                    $this->estado
                    ?? ''
                )
            );


        $area =
            trim(
                (string) (
                    $this->area
                    ?? ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | TIPO DE ACTIVO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $tipoActivo !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_tipo_equipo',
                    (int) $tipoActivo
                )
        );


        /*
        |--------------------------------------------------------------------------
        | MARCA
        |--------------------------------------------------------------------------
        */

        $query->when(
            $marca !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_marca',
                    (int) $marca
                )
        );


        /*
        |--------------------------------------------------------------------------
        | MODELO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $modelo !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_modelo',
                    (int) $modelo
                )
        );


        /*
        |--------------------------------------------------------------------------
        | NOMBRE DE EQUIPO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $nombreEquipo !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.nombre_equipo',
                    'ilike',
                    '%'
                    . $nombreEquipo
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | NÚMERO DE SERIE
        |--------------------------------------------------------------------------
        */

        $query->when(
            $numeroSerie !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.numero_serie',
                    'ilike',
                    '%'
                    . $numeroSerie
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | SERVICE TAG
        |--------------------------------------------------------------------------
        */

        $query->when(
            $serviceTag !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.service_tag',
                    'ilike',
                    '%'
                    . $serviceTag
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | DIRECCIÓN IP
        |--------------------------------------------------------------------------
        */

        $query->when(
            $direccionIp !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.direccion_ip',
                    'ilike',
                    '%'
                    . $direccionIp
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | ESTADO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $estado !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_estado_activo',
                    (int) $estado
                )
        );


        /*
        |--------------------------------------------------------------------------
        | ÁREA / DEPARTAMENTO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $area !== '',
            function (
                Builder $q
            ) use (
                $area
            ): void {
                $areaId =
                    (int) $area;


                $q->where(
                    function (
                        Builder $sub
                    ) use (
                        $areaId
                    ): void {
                        $sub
                            ->where(
                                'a_dir.id_area',
                                $areaId
                            )

                            ->orWhere(
                                'dep.id_area',
                                $areaId
                            );
                    }
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ORDENAMIENTO
    |--------------------------------------------------------------------------
    */

    private function applySorting(
        Builder $query
    ): void {
        $direction =
            $this->sort === 'desc'
                ? 'desc'
                : 'asc';


        $field =
            array_key_exists(
                $this->sortField,
                self::SORTABLE_COLUMNS
            )
                ? $this->sortField
                : 'codigo_inventario';


        /*
        |--------------------------------------------------------------------------
        | ÁREA / DEPARTAMENTO
        |--------------------------------------------------------------------------
        */

        if (
            $field
            === 'ubicacion_organizacional'
        ) {
            $query->orderByRaw(
                "
                COALESCE(
                    CASE
                        WHEN dep.id_departamento IS NOT NULL
                            THEN CASE
                                WHEN a_dep.nombre IS NOT NULL
                                    THEN a_dep.nombre || ' / ' || dep.nombre
                                ELSE dep.nombre
                            END
                        ELSE a_dir.nombre
                    END,
                    '—'
                ) {$direction}
                "
            );
        } else {
            $column =
                self::SORTABLE_COLUMNS[
                    $field
                ];

            $query->orderBy(
                $column,
                $direction
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ORDEN SECUNDARIO ESTABLE
        |--------------------------------------------------------------------------
        */

        if (
            $field
            !== 'codigo_inventario'
        ) {
            $query->orderBy(
                'e.codigo_inventario',
                'asc'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS
    |--------------------------------------------------------------------------
    */

    private function getTiposActivo(): array
    {
        return $this->systemCache()
            ->rememberSimple(
                self::CATALOG_CACHE_MODULE,
                'filtros.tipos_equipo',

                fn () =>
                    TipoEquipo::where(
                        'activo',
                        true
                    )
                        ->orderBy(
                            'nombre'
                        )
                        ->get([
                            'id_tipo_equipo',
                            'nombre',
                        ])
                        ->toArray(),

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    private function getMarcas(): array
    {
        return $this->systemCache()
            ->rememberSimple(
                self::CATALOG_CACHE_MODULE,
                'filtros.marcas',

                fn () =>
                    Marca::where(
                        'activo',
                        true
                    )
                        ->orderBy(
                            'nombre'
                        )
                        ->get([
                            'id_marca',
                            'nombre',
                        ])
                        ->toArray(),

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    private function getModelos(): array
    {
        return $this->systemCache()
            ->rememberSimple(
                self::CATALOG_CACHE_MODULE,
                'filtros.modelos',

                fn () =>
                    Modelo::where(
                        'activo',
                        true
                    )
                        ->orderBy(
                            'nombre'
                        )
                        ->get([
                            'id_modelo',
                            'nombre',
                        ])
                        ->toArray(),

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    private function getEstados(): array
    {
        return $this->systemCache()
            ->rememberSimple(
                self::CATALOG_CACHE_MODULE,
                'filtros.estados_activo',

                fn () =>
                    CatalogoValor::deCatalogo(
                        'estado_activo'
                    )
                        ->get([
                            'id_valor',
                            'nombre',
                        ])
                        ->toArray(),

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    private function getAreas(): array
    {
        return $this->systemCache()
            ->rememberSimple(
                self::CATALOG_CACHE_MODULE,
                'filtros.areas',

                fn () =>
                    Area::where(
                        'activo',
                        true
                    )
                        ->orderBy(
                            'nombre'
                        )
                        ->get([
                            'id_area',
                            'nombre',
                        ])
                        ->toArray(),

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PLACEHOLDER
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view(
            'livewire.placeholders.equipos-index'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        /*
        |--------------------------------------------------------------------------
        | CANTIDAD POR PÁGINA
        |--------------------------------------------------------------------------
        */

        $perPage =
            in_array(
                $this->perPage,
                self::PER_PAGE_OPTIONS,
                true
            )
                ? $this->perPage
                : 10;


        /*
        |--------------------------------------------------------------------------
        | PÁGINA ACTUAL
        |--------------------------------------------------------------------------
        */

        $page = max(
            1,
            (int) $this->getPage()
        );


        /*
        |--------------------------------------------------------------------------
        | DATOS CACHEADOS
        |--------------------------------------------------------------------------
        */

        $cached =
            $this->systemCache()
                ->remember(
                    self::CACHE_MODULE,

                    'index.page',

                    $this->tableCacheState(
                        $perPage,
                        $page
                    ),

                    function () use (
                        $perPage,
                        $page
                    ): array {
                    /*
                    |--------------------------------------------------------------------------
                    | CONSULTA
                    |--------------------------------------------------------------------------
                    */

                    $query =
                        $this->equiposQuery();


                    /*
                    |--------------------------------------------------------------------------
                    | BÚSQUEDA
                    |--------------------------------------------------------------------------
                    */

                    $this->applySearch(
                        $query
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | FILTROS
                    |--------------------------------------------------------------------------
                    */

                    $this->applyFilters(
                        $query
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | ORDENAMIENTO
                    |--------------------------------------------------------------------------
                    */

                    $this->applySorting(
                        $query
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | PAGINACIÓN REAL
                    |--------------------------------------------------------------------------
                    */

                    $paginator =
                        $query->paginate(
                            $perPage,
                            ['*'],
                            'page',
                            $page
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | PREPARAR DATOS PARA EDITAR
                    |--------------------------------------------------------------------------
                    |
                    | Reutilizamos exactamente los registros que acaba de traer
                    | la consulta paginada.
                    |
                    | No hacemos ninguna consulta adicional.
                    |
                    */

                    $this->primeEditBaseCache(
                        $paginator->items()
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | PAYLOAD SEGURO PARA CACHÉ
                    |--------------------------------------------------------------------------
                    */

                    return [
                        'items' =>
                            array_map(
                                static fn ($item): array =>
                                    (array) $item,
                                $paginator->items()
                            ),

                        'total' =>
                            $paginator->total(),
                    ];
                },
                self::TABLE_CACHE_TTL_HOURS
            );


        /*
        |--------------------------------------------------------------------------
        | RECONSTRUIR PAGINADOR
        |--------------------------------------------------------------------------
        */

        $items =
            array_map(
                static fn (array $item): object =>
                    (object) $item,
                $cached['items']
            );


        $equipos =
            new LengthAwarePaginator(
                $items,
                (int) $cached['total'],
                $perPage,
                $page,
                [
                    'path' =>
                        request()->url(),

                    'pageName' =>
                        'page',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */

        return view(
            'livewire.equipos-index',
            [
                'equipos' =>
                    $equipos,

                'tiposActivo' =>
                    $this->getTiposActivo(),

                'marcas' =>
                    $this->getMarcas(),

                'modelos' =>
                    $this->getModelos(),

                'estados' =>
                    $this->getEstados(),

                'areas' =>
                    $this->getAreas(),
            ]
        );
    }
}