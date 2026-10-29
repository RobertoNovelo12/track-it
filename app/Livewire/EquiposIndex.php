<?php

namespace App\Livewire;

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
    use \App\Livewire\Concerns\FiltraEquipos;
    // Registros por página de Ajustes › Preferencias.
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;

    use WithPagination;

    // Únicamente la exportación; no cambia filtros ni consultas existentes.
    use \App\Livewire\Concerns\ExportaInventario;

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
        'sin_asignar' => 'Sin asignar',
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
        // Predeterminado: orden de registro (lo más reciente primero con «desc»).
        'recientes' => 'e.id_equipo',

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

    #[Url(as: 'hotel')]
    public ?string $hotelFiltro = null;

    /*
     * Situación operativa (accesos de la Vista general):
     * vigentes, asignados, sin_asignar, en_mantenimiento, mantenimiento_vencido,
     * garantia_vencida, garantia_por_vencer.
     */
    #[Url]
    public ?string $situacion = null;

    /*
     * Programa instalado (Administración › Programas): solo los
     * equipos que lo tienen registrado.
     */
    #[Url]
    public ?string $programa = null;


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

    // Por defecto lo más reciente primero (último equipo registrado arriba).
    #[Url(as: 'ordenar_por')]
    public string $sortField = 'recientes';

    #[Url]
    public string $sort = 'desc';


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
                    'hotelFiltro',
                    'situacion',
                    'programa',
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

        // «Programa instalado» sólo aplica a tipos que incluyen programas.
        if (! $this->filtroProgramaAplica()) {
            $this->programa = null;
        }
    }

    /*
     * «Programa instalado» sólo se muestra con un tipo elegido que
     * «Incluye programas» (sin tipo elegido no aparece).
     */
    private function filtroProgramaAplica(): bool
    {
        $tipo = trim((string) ($this->tipoActivo ?? ''));

        if ($tipo === '') {
            return false;
        }

        $porTipo = app(\App\Services\ProgramasService::class)->porTipo();

        return (bool) ($porTipo[$tipo]['incluye'] ?? false);
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
            $this->sort = 'desc';
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
                'recientes';
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

    /*
     * El buscador del encabezado se vació estando en Inventario: quitar
     * también ?buscar= (si no, seguía filtrando sin verse en el panel).
     */
    #[\Livewire\Attributes\On('limpiar-busqueda-general')]
    public function limpiarBusquedaGeneral(): void
    {
        if (($this->search ?? '') === '') {
            return;
        }

        $this->search = null;
        $this->resetPage();
    }

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
        $this->hotelFiltro = null;
        $this->situacion = null;
        $this->programa = null;

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

            'hotel' =>
                trim(
                    (string) (
                        $this->hotelFiltro
                        ?? ''
                    )
                ),

            'situacion' =>
                array_key_exists((string) $this->situacion, self::SITUACIONES)
                    ? (string) $this->situacion
                    : '',

            'programa' => ctype_digit((string) $this->programa) ? (string) $this->programa : '',

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
                    : 'recientes',

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
            'id_departamento',


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


                    'id_departamento' =>
                        $row['id_departamento'],


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
                'e.id_departamento',

                'e.fecha_fin_garantia',

                'e.comentarios',


                /*
                |--------------------------------------------------------------------------
                | DEPARTAMENTO PRINCIPAL
                |--------------------------------------------------------------------------
                |
                | EquipoEdit espera el departamento principal cuando el
                | equipo pertenece a un subdepartamento.
                |
                */

            ])

            ->selectRaw("
                CASE WHEN ee.clave = 'BAJA' OR EXISTS (SELECT 1 FROM bajas b WHERE b.id_equipo = e.id_equipo) THEN 1 ELSE 0 END as dado_de_baja
            ")
            // Con responsable (asignación sin fecha de fin): menú «Devolver».
            ->selectRaw("
                CASE WHEN EXISTS (SELECT 1 FROM asignaciones a_vigente WHERE a_vigente.id_equipo = e.id_equipo AND a_vigente.fecha_fin IS NULL) THEN 1 ELSE 0 END as tiene_responsable
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
        /*
         * Búsqueda tolerante (App\Support\BusquedaTolerante): sin acentos
         * («movil» = «Móvil»), ignora signos tecleados por accidente
         * («laptop{») y compara códigos y series también sin guiones.
         */
        $columnas = [
            'e.nombre_equipo',
            'e.id_equipo',
            'e.codigo_inventario',
            'e.numero_serie',
            'te.nombre',
            'ma.nombre',
            'mo.nombre',
        ];

        // Software instalado (igual que el buscador del encabezado).
        if (app(\App\Services\ProgramasService::class)->disponible()) {
            $columnas[] = "(SELECT string_agg(p_busqueda.nombre || ' ' || COALESCE(p_busqueda.empresa, ''), ' ')
                FROM equipo_programa AS ep_busqueda
                JOIN programas AS p_busqueda ON p_busqueda.id_programa = ep_busqueda.id_programa
                WHERE ep_busqueda.id_equipo = e.id_equipo)";
        }

        \App\Support\BusquedaTolerante::aplicar(
            $query,
            (string) ($this->search ?? ''),
            $columnas,
            [
                'e.codigo_inventario',
                'e.numero_serie',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */



    /*
    |--------------------------------------------------------------------------
    | FILTROS ESPECÍFICOS DEL TIPO (DINÁMICOS)
    |--------------------------------------------------------------------------
    |
    | Columnas base que ya tienen su propio filtro: no se repiten.
    |
    */



    /*
     * Secciones del formulario que NO son específicas del tipo:
     * «Información general» y «Información adicional» (ubicación,
     * compra, comentarios). Todo lo demás —«Información de {Tipo}» y
     * cualquier sección personalizada— genera filtros.
     */


    /*
     * Valores activos y válidos de los filtros específicos, ordenados
     * (también se usan como parte de la clave de caché del listado).
     */


    /*
     * Campos filtrables del tipo. Se guarda junto con los catálogos:
     * al guardar el formulario del tipo se renueva la caché de
     * catálogos y el campo nuevo aparece sin más cambios.
     */




    /*
     * Opciones de una lista: personalizadas del campo, catálogo general
     * o catálogos del sistema (mismas fuentes que el formulario).
     */




    /*
     * Condición según el tipo de control. Se compara como texto para
     * servir igual a columnas físicas y a atributos (guardados como texto).
     */



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
                : 'recientes';


        /*
        |--------------------------------------------------------------------------
        | HOTEL / DEPARTAMENTO
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
        if ($unidadPredeterminada && blank($this->hotelFiltro)) {
            $this->hotelFiltro = (string) $unidadPredeterminada;
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

                'hotelesFiltro' =>
                    $this->getHotelesFiltro(),

                'camposTipoFiltros' =>
                    trim((string) ($this->tipoActivo ?? '')) !== ''
                        ? $this->getCamposFiltrables((int) $this->tipoActivo)
                        : [],
                // Filtro «Programa instalado» (vacío si no hay programas).
                // Sólo para tipos que «Incluyen programas».
                'programasFiltro' =>
                    $this->filtroProgramaAplica()
                        ? app(\App\Services\ProgramasService::class)->opciones(
                            ctype_digit((string) $this->programa) ? [(int) $this->programa] : []
                        )
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
