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
    // Registros por página de Ajustes › Preferencias.
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;

    use WithPagination;

    protected $paginationTheme = 'tailwind';


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    /*
     * Situaciones operativas que se pueden filtrar
     * (enlaces de los indicadores de la Vista general).
     */
    public const SITUACIONES = [
        'vigentes' => 'Equipos vigentes',
        'asignados' => 'Asignados',
        'en_mantenimiento' => 'En mantenimiento',
        'mantenimiento_vencido' => 'Mantenimiento vencido',
        'garantia_vencida' => 'Garantía vencida',
        'garantia_por_vencer' => 'Garantía por vencer (30 días)',
    ];

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

        'estado' => 'ee.nombre',

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

    #[Url(as: 'numero_factura')]
    public ?string $numeroFactura = null;

    /*
     * Filtros específicos del tipo de equipo seleccionado.
     * [id_tipo_campo => valor]. Los campos salen de la configuración
     * del formulario del tipo (Administración › Tipos de equipo), así
     * que un campo nuevo en «Información de {Tipo}» aparece solo.
     */
    #[Url(as: 'campos')]
    public array $camposTipo = [];

    #[Url(as: 'service_tag')]
    public ?string $serviceTag = null;

    #[Url(as: 'direccion_ip')]
    public ?string $direccionIp = null;

    #[Url]
    public ?string $estado = null;

    #[Url]
    public ?string $area = null;

    /*
     * Situación operativa (accesos de la Vista general):
     * vigentes, asignados, en_mantenimiento, mantenimiento_vencido,
     * garantia_vencida, garantia_por_vencer.
     */
    #[Url]
    public ?string $situacion = null;


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
                    'numeroFactura',
                    'serviceTag',
                    'direccionIp',
                    'estado',
                    'area',
                    'situacion',
                    'camposTipo',
                ],
                true
            )
            || str_starts_with($property, 'camposTipo.')
        ) {
            $this->resetPage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE TIPO DE EQUIPO
    |--------------------------------------------------------------------------
    |
    | Los filtros específicos pertenecen a un tipo: al cambiarlo se limpian.
    |
    */

    public function updatedTipoActivo(): void
    {
        $this->camposTipo = [];
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
        $this->numeroFactura = null;
        $this->camposTipo = [];
        $this->serviceTag = null;
        $this->direccionIp = null;
        $this->estado = null;
        $this->area = null;
        $this->situacion = null;

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

            'situacion' =>
                array_key_exists((string) $this->situacion, self::SITUACIONES)
                    ? (string) $this->situacion
                    : '',

            /*
             * Vencimientos dependen del día: la caché de un filtro
             * de situación no se reutiliza al día siguiente.
             */
            'situacion_fecha' =>
                filled($this->situacion)
                    ? now()->toDateString()
                    : '',

            'numero_factura' =>
                trim(
                    (string) (
                        $this->numeroFactura
                        ?? ''
                    )
                ),

            'campos_tipo' =>
                $this->camposTipoActivos(),

            'ubicacion_schema' => 'unidades-v1',

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

            'id_unidad',
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

            if ((int) ($row['dado_de_baja'] ?? 0) === 1) {
                Cache::forget('equipos.edit.base.' . (int) $row['id_equipo']);
                continue;
            }


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

                    'id_unidad' => $row['id_unidad'],

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
                'estados_equipo as ee',
                'ee.id_estado_equipo',
                '=',
                'e.id_estado_activo'
            )

            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'e.id_departamento'
            )

            ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'e.id_unidad')

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
                'ee.nombre as estado_nombre',
                'ee.clave as estado_clave',


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

                'e.id_unidad',
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
                CASE WHEN ee.clave = 'BAJA' OR EXISTS (SELECT 1 FROM bajas b WHERE b.id_equipo = e.id_equipo) THEN 1 ELSE 0 END as dado_de_baja
            ")
            ->selectRaw("
                COALESCE(
                    CASE
                        WHEN dep.id_departamento IS NOT NULL
                            THEN CASE
                                WHEN u.nombre IS NOT NULL
                                    THEN u.nombre || ' / ' || dep.nombre
                                ELSE dep.nombre
                            END
                        ELSE u.nombre
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
        $this->applySituacion($query);

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
                                'e.id_unidad',
                                $areaId
                            );
                    }
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | NÚMERO DE FACTURA
        |--------------------------------------------------------------------------
        */

        $numeroFactura = trim((string) ($this->numeroFactura ?? ''));

        $query->when(
            $numeroFactura !== '',
            fn (Builder $q) => $q->where(
                'e.numero_factura',
                'ilike',
                '%' . $numeroFactura . '%'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | FILTROS ESPECÍFICOS DEL TIPO
        |--------------------------------------------------------------------------
        */

        if ($tipoActivo !== '') {
            $this->applyCamposTipo(
                $query,
                (int) $tipoActivo
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILTROS ESPECÍFICOS DEL TIPO (DINÁMICOS)
    |--------------------------------------------------------------------------
    |
    | Columnas base que ya tienen su propio filtro: no se repiten.
    |
    */

    private const COLUMNAS_FILTRO_BASE = [
        'codigo_inventario',
        'id_tipo_equipo',
        'nombre_equipo',
        'id_estado_activo',
        'id_marca',
        'id_modelo',
        'numero_serie',
        'numero_factura',
        'id_unidad',
    ];

    /*
     * Secciones del formulario que NO son específicas del tipo:
     * «Información general» y «Información adicional» (ubicación,
     * compra, comentarios). Todo lo demás —«Información de {Tipo}» y
     * cualquier sección personalizada— genera filtros.
     */
    private const SECCIONES_NO_ESPECIFICAS = [
        'general',
        'adicional',
    ];

    /*
     * Valores activos y válidos de los filtros específicos, ordenados
     * (también se usan como parte de la clave de caché del listado).
     */
    private function camposTipoActivos(): array
    {
        if (trim((string) ($this->tipoActivo ?? '')) === '') {
            return [];
        }

        $activos = [];

        foreach ($this->camposTipo as $id => $valor) {
            if (! is_numeric($id) || is_array($valor) || $valor === null) {
                continue;
            }

            $valor = trim((string) $valor);

            if ($valor === '') {
                continue;
            }

            $activos[(string) (int) $id] = $valor;
        }

        ksort($activos);

        return $activos;
    }

    /*
     * Campos filtrables del tipo. Se guarda junto con los catálogos:
     * al guardar el formulario del tipo se renueva la caché de
     * catálogos y el campo nuevo aparece sin más cambios.
     */
    private function getCamposFiltrables(int $tipoId): array
    {
        if ($tipoId <= 0) {
            return [];
        }

        return $this->systemCache()->rememberSimple(
            self::CATALOG_CACHE_MODULE,
            'filtros.campos_tipo.' . $tipoId . '.v1',
            fn (): array => $this->leerCamposFiltrables($tipoId),
            self::CATALOG_CACHE_TTL_HOURS
        );
    }

    private function leerCamposFiltrables(int $tipoId): array
    {
        $filas = DB::table('tipo_equipo_campos as tec')
            ->join('campos_equipo as ce', 'ce.id_campo', '=', 'tec.id_campo')
            ->join('tipo_equipo_secciones as tes', 'tes.id_seccion', '=', 'tec.id_seccion')
            ->where('tec.id_tipo_equipo', $tipoId)
            ->where('ce.activo', true)
            ->where('tec.visible', true)
            ->whereIn('tec.origen_dato', ['equipos', 'tabla_tipo', 'atributo'])
            ->whereNotIn('tes.clave', self::SECCIONES_NO_ESPECIFICAS)
            ->orderBy('tes.orden')
            ->orderBy('tec.orden')
            ->orderBy('tec.id_tipo_campo')
            ->get([
                'tec.id_tipo_campo',
                'tec.id_campo',
                'tec.origen_dato',
                'tec.tabla_destino',
                'tec.columna_destino',
                'tec.etiqueta',
                'tec.fuente_opciones',
                'tec.id_catalogo',
                'ce.nombre as nombre_base',
                'ce.tipo_control',
            ]);

        $identificador = '/^[a-z_][a-z0-9_]*$/';
        $campos = [];

        foreach ($filas as $fila) {
            $origen = (string) $fila->origen_dato;
            $tabla = (string) ($fila->tabla_destino ?? '');
            $columna = (string) ($fila->columna_destino ?? '');
            $control = (string) $fila->tipo_control;

            if (! in_array($control, ['text', 'textarea', 'number', 'date', 'boolean', 'select'], true)) {
                continue;
            }

            /*
             * Los nombres de tabla/columna vienen de la configuración y se
             * usan como identificadores SQL: solo se aceptan nombres simples.
             */
            if ($origen === 'equipos') {
                if (
                    ! preg_match($identificador, $columna)
                    || ($tabla !== '' && $tabla !== 'equipos')
                    || in_array($columna, self::COLUMNAS_FILTRO_BASE, true)
                ) {
                    continue;
                }
            } elseif ($origen === 'tabla_tipo') {
                if (! preg_match($identificador, $tabla) || ! preg_match($identificador, $columna)) {
                    continue;
                }
            }

            $etiqueta = trim((string) ($fila->etiqueta ?? ''));

            $campos[] = [
                'id' => (string) $fila->id_tipo_campo,
                'id_campo' => (int) $fila->id_campo,
                'etiqueta' => $etiqueta !== '' ? $etiqueta : (string) $fila->nombre_base,
                'control' => $control === 'textarea' ? 'text' : $control,
                'origen' => $origen,
                'tabla' => $tabla,
                'columna' => $columna,
                'opciones' => match ($control) {
                    'boolean' => [
                        ['value' => '1', 'label' => 'Sí'],
                        ['value' => '0', 'label' => 'No'],
                    ],
                    'select' => $this->opcionesCampo($fila),
                    default => [],
                },
            ];
        }

        return $campos;
    }

    /*
     * Opciones de una lista: personalizadas del campo, catálogo general
     * o catálogos del sistema (mismas fuentes que el formulario).
     */
    private function opcionesCampo(object $fila): array
    {
        $fuente = (string) ($fila->fuente_opciones ?? '');

        if ($fuente === 'personalizado') {
            $items = DB::table('tipo_equipo_campo_opciones')
                ->where('id_tipo_campo', $fila->id_tipo_campo)
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('id_opcion')
                ->get(['id_opcion as value', 'nombre as label']);
        } elseif ($fuente === 'catalogo') {
            $items = DB::table('catalogo_valores')
                ->where('id_catalogo', $fila->id_catalogo)
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get(['id_valor as value', 'nombre as label']);
        } else {
            $fuentes = [
                'estados_equipo' => 'id_estado_equipo',
                'marcas' => 'id_marca',
                'modelos' => 'id_modelo',
                'proveedores' => 'id_proveedor',
                'unidades_organizativas' => 'id_unidad',
                'areas' => 'id_area',
                'departamentos' => 'id_departamento',
            ];

            if (! array_key_exists($fuente, $fuentes)) {
                return [];
            }

            $items = DB::table($fuente)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get([$fuentes[$fuente] . ' as value', 'nombre as label']);
        }

        return $items
            ->map(fn ($item): array => [
                'value' => (string) $item->value,
                'label' => (string) $item->label,
            ])
            ->values()
            ->all();
    }

    private function applyCamposTipo(Builder $query, int $tipoId): void
    {
        $activos = $this->camposTipoActivos();

        if ($activos === []) {
            return;
        }

        $definiciones = collect($this->getCamposFiltrables($tipoId))->keyBy('id');

        foreach ($activos as $id => $valor) {
            $campo = $definiciones->get((string) $id);

            if (! $campo) {
                continue;
            }

            if ($campo['origen'] === 'equipos') {
                $this->condicionCampo($query, '"e"."' . $campo['columna'] . '"', $campo['control'], $valor);

                continue;
            }

            if ($campo['origen'] === 'tabla_tipo') {
                $query->whereExists(function (Builder $sub) use ($campo, $valor): void {
                    $sub->selectRaw('1')
                        ->from($campo['tabla'] . ' as ft')
                        ->whereColumn('ft.id_equipo', 'e.id_equipo');

                    $this->condicionCampo($sub, '"ft"."' . $campo['columna'] . '"', $campo['control'], $valor);
                });

                continue;
            }

            // atributo: atributos_equipo (campos agregados desde el constructor)
            $query->whereExists(function (Builder $sub) use ($campo, $valor): void {
                $sub->selectRaw('1')
                    ->from('atributos_equipo as fa')
                    ->whereColumn('fa.id_equipo', 'e.id_equipo')
                    ->where('fa.id_campo', $campo['id_campo']);

                $this->condicionCampo($sub, '"fa"."valor"', $campo['control'], $valor);
            });
        }
    }

    /*
     * Condición según el tipo de control. Se compara como texto para
     * servir igual a columnas físicas y a atributos (guardados como texto).
     */
    private function condicionCampo(Builder $query, string $columna, string $control, string $valor): void
    {
        $texto = 'CAST(' . $columna . ' AS TEXT)';

        switch ($control) {
            case 'select':
                $query->whereRaw($texto . ' = ?', [$valor]);
                break;

            case 'boolean':
                $query->whereRaw(
                    $texto . ' IN (?, ?)',
                    $valor === '1' ? ['1', 'true'] : ['0', 'false']
                );
                break;

            case 'number':
                if (! is_numeric($valor)) {
                    $query->whereRaw('1 = 0');
                    break;
                }

                // CASE evita convertir valores no numéricos (atributos libres).
                $query->whereRaw(
                    '(CASE WHEN ' . $texto . " ~ '^-{0,1}[0-9]+([.][0-9]+){0,1}$' THEN CAST(" . $texto . ' AS NUMERIC) END) = CAST(? AS NUMERIC)',
                    [$valor]
                );
                break;

            case 'date':
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                    $query->whereRaw('1 = 0');
                    break;
                }

                $query->whereRaw('LEFT(' . $texto . ', 10) = ?', [$valor]);
                break;

            default:
                $query->whereRaw($texto . ' ILIKE ?', ['%' . addcslashes($valor, '%_\\') . '%']);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SITUACIÓN OPERATIVA
    |--------------------------------------------------------------------------
    |
    | Mismos criterios que los indicadores de la Vista general
    | (⚡dashboard-overview::buildDashboardData), para que el número
    | de la tarjeta coincida con los resultados del listado.
    |
    */

    private function applySituacion(
        Builder $query
    ): void {
        $situacion = (string) $this->situacion;

        if (! array_key_exists($situacion, self::SITUACIONES)) {
            return;
        }

        $hoy = now()->toDateString();

        /*
         * Todas las situaciones se calculan sobre el inventario
         * vigente: sin baja histórica ni estado BAJA.
         */
        $query
            ->where(function (Builder $q): void {
                $q->whereNull('ee.clave')
                    ->orWhere('ee.clave', '<>', 'BAJA');
            })
            ->whereNotExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('bajas as b_situacion')
                    ->whereColumn('b_situacion.id_equipo', 'e.id_equipo');
            });

        match ($situacion) {
            'asignados' => $query->whereExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('asignaciones as a_situacion')
                    ->whereColumn('a_situacion.id_equipo', 'e.id_equipo')
                    ->whereNull('a_situacion.fecha_fin');
            }),

            'en_mantenimiento' => $query->whereExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('mantenimientos as m_situacion')
                    ->join('catalogo_valores as cv_situacion', 'cv_situacion.id_valor', '=', 'm_situacion.id_estado_mantenimiento')
                    ->whereColumn('m_situacion.id_equipo', 'e.id_equipo')
                    ->where('cv_situacion.clave', 'EN_PROCESO');
            }),

            'mantenimiento_vencido' => $query->whereExists(function (Builder $q) use ($hoy): void {
                $q->selectRaw('1')
                    ->from('mantenimientos as m_situacion')
                    ->join('catalogo_valores as cv_situacion', 'cv_situacion.id_valor', '=', 'm_situacion.id_estado_mantenimiento')
                    ->whereColumn('m_situacion.id_equipo', 'e.id_equipo')
                    ->whereNotIn('cv_situacion.clave', ['COMPLETADO', 'CANCELADO'])
                    ->whereNotNull('m_situacion.fecha_proximo_mantenimiento')
                    ->whereDate('m_situacion.fecha_proximo_mantenimiento', '<', $hoy);
            }),

            'garantia_vencida' => $query
                ->whereNotNull('e.fecha_fin_garantia')
                ->whereDate('e.fecha_fin_garantia', '<', $hoy),

            'garantia_por_vencer' => $query
                ->whereNotNull('e.fecha_fin_garantia')
                ->whereBetween('e.fecha_fin_garantia', [
                    $hoy,
                    now()->addDays(30)->toDateString(),
                ]),

            default => null,
        };
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
                                WHEN u.nombre IS NOT NULL
                                    THEN u.nombre || ' / ' || dep.nombre
                                ELSE dep.nombre
                            END
                        ELSE u.nombre
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
        return $this->systemCache()->rememberSimple(
            self::CATALOG_CACHE_MODULE,
            'filtros.tipos_equipo.arrays.v3',
            fn (): array => TipoEquipo::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_tipo_equipo', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_tipo_equipo' => (int) $row->id_tipo_equipo,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            self::CATALOG_CACHE_TTL_HOURS
        );
    }


    private function getMarcas(): array
    {
        return $this->systemCache()->rememberSimple(
            self::CATALOG_CACHE_MODULE,
            'filtros.marcas.arrays.v3',
            fn (): array => Marca::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_marca', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_marca' => (int) $row->id_marca,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            self::CATALOG_CACHE_TTL_HOURS
        );
    }


    private function getModelos(): array
    {
        return $this->systemCache()->rememberSimple(
            self::CATALOG_CACHE_MODULE,
            'filtros.modelos.arrays.v3',
            fn (): array => Modelo::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_modelo', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_modelo' => (int) $row->id_modelo,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            self::CATALOG_CACHE_TTL_HOURS
        );
    }


    private function getEstados(): array
    {
        return $this->systemCache()->rememberSimple(
            self::CATALOG_CACHE_MODULE,
            'filtros.estados_equipo.arrays.v3',
            fn (): array => DB::table('estados_equipo')
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->get(['id_estado_equipo', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_valor' => (int) $row->id_estado_equipo,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            self::CATALOG_CACHE_TTL_HOURS
        );
    }


    private function getAreas(): array
    {
        return $this->systemCache()->rememberSimple(
            self::CATALOG_CACHE_MODULE,
            'filtros.unidades.arrays.v1',
            fn (): array => DB::table('unidades_organizativas')->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_unidad', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_area' => (int) $row->id_unidad,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            self::CATALOG_CACHE_TTL_HOURS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HOTEL PREDETERMINADO
    |--------------------------------------------------------------------------
    |
    | equipos/index.blade.php lo envía solo cuando la página se abre sin
    | filtros en la URL (Ajustes › Preferencias).
    |
    */

    public function mount(?int $unidadPredeterminada = null): void
    {
        if ($unidadPredeterminada && blank($this->area)) {
            $this->area = (string) $unidadPredeterminada;
        }
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

                    'index.page.bajas.v2',

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

                'camposTipoFiltros' =>
                    trim((string) ($this->tipoActivo ?? '')) !== ''
                        ? $this->getCamposFiltrables((int) $this->tipoActivo)
                        : [],
                'situaciones' =>
                    collect(self::SITUACIONES)
                        ->map(fn (string $label, string $value): array => [
                            'value' => $value,
                            'label' => $label,
                        ])
                        ->values()
                        ->all(),
            ]
        );
    }
}