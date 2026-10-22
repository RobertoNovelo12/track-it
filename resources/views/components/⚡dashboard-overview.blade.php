<?php

use App\Services\SystemCacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{


    private const CACHE_MODULE =
        'dashboard';


    private const CACHE_TTL_HOURS =
        12;
    /*
    |--------------------------------------------------------------------------
    | KPIs
    |--------------------------------------------------------------------------
    */

    public int $totalEquipos = 0;

    public int $equiposAsignados = 0;

    public int $equiposDisponibles = 0;

    public int $enMantenimiento = 0;

    public int $mantenimientosVencidos = 0;

    public int $garantiasVencidas = 0;

    public int $garantiasPorVencer = 0;


    /*
    |--------------------------------------------------------------------------
    | PORCENTAJES
    |--------------------------------------------------------------------------
    */

    public int $porcentajeAsignados = 0;

    public int $porcentajeMantenimiento = 0;


    /*
    |--------------------------------------------------------------------------
    | GRÁFICAS
    |--------------------------------------------------------------------------
    */

    public array $antiguedadEquipos = [];

    public array $estadoMantenimientos = [];

    public array $mantenimientosPorMes = [];

    public array $equiposPorTipo = [];


    /*
    |--------------------------------------------------------------------------
    | OPERACIÓN
    |--------------------------------------------------------------------------
    */

    public array $alertas = [];

    /*
     * Gráficas y actividad cargadas (carga por secciones).
     */
    public bool $seccionesListas = false;

    public bool $errorSecciones = false;

    public array $actividadReciente = [];

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
    | MOUNT
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->loadDashboard();
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    #[On('dashboard-refresh-requested')]
    public function refreshDashboard(): void
    {
        $this->systemCache()
            ->refreshModule(
                self::CACHE_MODULE
            );

        $this->seccionesListas = false;

        $this->loadDashboard();
        $this->cargarSecciones();

        // El botón "Actualizar" (dashboard.blade.php) espera este
        // evento para desbloquearse y detener el giro del icono.
        $this->dispatch('dashboard-refreshed');
    }


    /*
    |--------------------------------------------------------------------------
    | CARGAR DASHBOARD
    |--------------------------------------------------------------------------
    */

    /*
     * 1. Tarjetas y alertas (consulta corta).
     * 2. Gráficas y actividad: si ya están en caché se muestran de
     *    inmediato; si no, la vista pide cargarSecciones() con wire:init
     *    y mientras tanto muestra su skeleton.
     */
    protected function loadDashboard(): void
    {
        $this->aplicarKpis(
            $this->systemCache()->rememberSimple(
                self::CACHE_MODULE,
                'kpis',
                fn () => $this->buildKpiData(),
                self::CACHE_TTL_HOURS
            )
        );

        $claveSecciones = $this->systemCache()->key(self::CACHE_MODULE, 'secciones-v2');

        if (\Illuminate\Support\Facades\Cache::has($claveSecciones)) {
            $this->cargarSecciones();
        } else {
            $this->seccionesListas = false;
        }
    }

    public function cargarSecciones(): void
    {
        if ($this->seccionesListas) {
            return;
        }

        $this->errorSecciones = false;

        try {
            $data = $this->systemCache()->rememberSimple(
                self::CACHE_MODULE,
                'secciones-v2',
                fn () => $this->buildSeccionesData(),
                self::CACHE_TTL_HOURS
            );
        } catch (\Throwable $exception) {
            report($exception);

            // La vista muestra el estado de error con «Reintentar».
            $this->errorSecciones = true;

            return;
        }

        $this->antiguedadEquipos = $data['antiguedadEquipos'] ?? [];
        $this->estadoMantenimientos = $data['estadoMantenimientos'] ?? [];
        $this->mantenimientosPorMes = $data['mantenimientosPorMes'] ?? [];
        $this->equiposPorTipo = $data['equiposPorTipo'] ?? [];
        $this->actividadReciente = $data['actividadReciente'] ?? [];

        $this->seccionesListas = true;
    }

    public function reintentarSecciones(): void
    {
        $this->seccionesListas = false;

        $this->cargarSecciones();
    }

    protected function aplicarKpis(array $data): void
    {
        $this->totalEquipos = (int) ($data['totalEquipos'] ?? 0);
        $this->equiposAsignados = (int) ($data['equiposAsignados'] ?? 0);
        $this->equiposDisponibles = (int) ($data['equiposDisponibles'] ?? 0);
        $this->enMantenimiento = (int) ($data['enMantenimiento'] ?? 0);
        $this->mantenimientosVencidos = (int) ($data['mantenimientosVencidos'] ?? 0);
        $this->garantiasVencidas = (int) ($data['garantiasVencidas'] ?? 0);
        $this->garantiasPorVencer = (int) ($data['garantiasPorVencer'] ?? 0);
        $this->porcentajeAsignados = (int) ($data['porcentajeAsignados'] ?? 0);
        $this->porcentajeMantenimiento = (int) ($data['porcentajeMantenimiento'] ?? 0);
        $this->alertas = $data['alertas'] ?? [];
    }


    /*
    |--------------------------------------------------------------------------
    | CONSTRUIR DATOS
    |--------------------------------------------------------------------------
    */

    /*
     * Compatibilidad: todos los datos juntos.
     */
    protected function buildDashboardData(): array
    {
        return $this->buildKpiData() + $this->buildSeccionesData();
    }


    /*
    |--------------------------------------------------------------------------
    | KPIs Y ALERTAS (primer bloque que se muestra)
    |--------------------------------------------------------------------------
    */

    protected function buildKpiData(): array
    {
        $today =
            Carbon::today();

        // Inventario operativo: excluye equipos con baja histórica
        // o estado BAJA, sin borrar sus registros.
        $equiposVigentes = static function () {
            return DB::table('equipos as e')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('bajas as b_inventario')
                        ->whereColumn('b_inventario.id_equipo', 'e.id_equipo');
                })
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('estados_equipo as ee_inventario')
                        ->whereColumn('ee_inventario.id_estado_equipo', 'e.id_estado_activo')
                        ->where('ee_inventario.clave', 'BAJA');
                });
        };



        /*
        |--------------------------------------------------------------------------
        | TOTAL DE EQUIPOS
        |--------------------------------------------------------------------------
        */

        $totalEquipos =
            $equiposVigentes()
                ->count();


        /*
        |--------------------------------------------------------------------------
        | EQUIPOS ASIGNADOS
        |--------------------------------------------------------------------------
        */

        $equiposAsignados =
            DB::table('asignaciones')
                ->whereIn('id_equipo', $equiposVigentes()->select('e.id_equipo'))
                ->whereNull('fecha_fin')
                ->distinct()
                ->count('id_equipo');


        /*
        |--------------------------------------------------------------------------
        | EN MANTENIMIENTO
        |--------------------------------------------------------------------------
        */

        $enMantenimiento =
            DB::table('mantenimientos as m')
                ->whereIn('m.id_equipo', $equiposVigentes()->select('e.id_equipo'))

                ->leftJoin(
                    'catalogo_valores as cv',
                    'cv.id_valor',
                    '=',
                    'm.id_estado_mantenimiento'
                )

                ->where(
                    'cv.clave',
                    'EN_PROCESO'
                )

                ->count(
                    'm.id_mantenimiento'
                );


        /*
        |--------------------------------------------------------------------------
        | MANTENIMIENTOS VENCIDOS
        |--------------------------------------------------------------------------
        */

        $mantenimientosVencidos =
            DB::table('mantenimientos as m')
                ->whereIn('m.id_equipo', $equiposVigentes()->select('e.id_equipo'))

                ->leftJoin(
                    'catalogo_valores as cv',
                    'cv.id_valor',
                    '=',
                    'm.id_estado_mantenimiento'
                )

                ->whereNotIn(
                    'cv.clave',
                    [
                        'COMPLETADO',
                        'CANCELADO',
                    ]
                )

                ->whereNotNull(
                    'm.fecha_proximo_mantenimiento'
                )

                ->whereDate(
                    'm.fecha_proximo_mantenimiento',
                    '<',
                    $today
                )

                ->count(
                    'm.id_mantenimiento'
                );


        /*
        |--------------------------------------------------------------------------
        | GARANTÍAS VENCIDAS
        |--------------------------------------------------------------------------
        */

        $garantiasVencidas =
            $equiposVigentes()

                ->whereNotNull(
                    'fecha_fin_garantia'
                )

                ->whereDate(
                    'fecha_fin_garantia',
                    '<',
                    $today
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | GARANTÍAS POR VENCER
        |--------------------------------------------------------------------------
        */

        $garantiasPorVencer =
            $equiposVigentes()

                ->whereNotNull(
                    'fecha_fin_garantia'
                )

                ->whereBetween(
                    'fecha_fin_garantia',
                    [
                        $today->toDateString(),
                        $today
                            ->copy()
                            ->addDays(30)
                            ->toDateString(),
                    ]
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $equiposDisponibles =
            max(
                $totalEquipos
                    - $equiposAsignados
                    - $enMantenimiento,
                0
            );


        /*
        |--------------------------------------------------------------------------
        | PORCENTAJES
        |--------------------------------------------------------------------------
        */

        $porcentajeAsignados =
            $totalEquipos > 0

                ? (int) round(
                    (
                        $equiposAsignados
                        / $totalEquipos
                    ) * 100
                )

                : 0;


        $porcentajeMantenimiento =
            $totalEquipos > 0

                ? (int) round(
                    (
                        $enMantenimiento
                        / $totalEquipos
                    ) * 100
                )

                : 0;


        /*
        |--------------------------------------------------------------------------
        | ALERTAS
        |--------------------------------------------------------------------------
        */

        $alertas = [

            [
                'title' =>
                    'Mantenimientos vencidos',

                'description' =>
                    $mantenimientosVencidos
                    . ' registro(s) con seguimiento atrasado.',

                'count' =>
                    $mantenimientosVencidos,

                'color' =>
                    '#D9775B',

                'soft' =>
                    'rgba(217, 119, 91, 0.12)',
            ],


            [
                'title' =>
                    'Garantías por vencer',

                'description' =>
                    $garantiasPorVencer
                    . ' equipo(s) vencen en los próximos 30 días.',

                'count' =>
                    $garantiasPorVencer,

                'color' =>
                    '#D9A441',

                'soft' =>
                    'rgba(217, 164, 65, 0.12)',
            ],


            [
                'title' =>
                    'Equipos sin asignar',

                'description' =>
                    $equiposDisponibles
                    . ' equipo(s) disponibles actualmente.',

                'count' =>
                    $equiposDisponibles,

                'color' =>
                    '#5B5BD6',

                'soft' =>
                    'rgba(91, 91, 214, 0.12)',
            ],


            [
                'title' =>
                    'Garantías vencidas',

                'description' =>
                    $garantiasVencidas
                    . ' equipo(s) con garantía vencida.',

                'count' =>
                    $garantiasVencidas,

                'color' =>
                    '#6BB38E',

                'soft' =>
                    'rgba(107, 179, 142, 0.12)',
            ],

        ];


        return [
            'totalEquipos' => $totalEquipos,
            'equiposAsignados' => $equiposAsignados,
            'equiposDisponibles' => $equiposDisponibles,
            'enMantenimiento' => $enMantenimiento,
            'mantenimientosVencidos' => $mantenimientosVencidos,
            'garantiasVencidas' => $garantiasVencidas,
            'garantiasPorVencer' => $garantiasPorVencer,
            'porcentajeAsignados' => $porcentajeAsignados,
            'porcentajeMantenimiento' => $porcentajeMantenimiento,
            'alertas' => $alertas,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | GRÁFICAS Y ACTIVIDAD (se cargan después de las tarjetas)
    |--------------------------------------------------------------------------
    */

    protected function buildSeccionesData(): array
    {
        $today =
            Carbon::today();

        // Inventario operativo: excluye equipos con baja histórica
        // o estado BAJA, sin borrar sus registros.
        $equiposVigentes = static function () {
            return DB::table('equipos as e')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('bajas as b_inventario')
                        ->whereColumn('b_inventario.id_equipo', 'e.id_equipo');
                })
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('estados_equipo as ee_inventario')
                        ->whereColumn('ee_inventario.id_estado_equipo', 'e.id_estado_activo')
                        ->where('ee_inventario.clave', 'BAJA');
                });
        };



        /*
        |--------------------------------------------------------------------------
        | ANTIGÜEDAD
        |--------------------------------------------------------------------------
        */

        $antiguedadBase = [

            [
                'label' => '0 - 2 años',

                'count' =>
                    $equiposVigentes()

                        ->whereNotNull(
                            'fecha_compra'
                        )

                        ->whereDate(
                            'fecha_compra',
                            '>=',
                            $today
                                ->copy()
                                ->subYears(2)
                        )

                        ->count(),

                'color' =>
                    '#3BA5D9',
            ],


            [
                'label' => '3 - 5 años',

                'count' =>
                    $equiposVigentes()

                        ->whereNotNull(
                            'fecha_compra'
                        )

                        ->whereDate(
                            'fecha_compra',
                            '<',
                            $today
                                ->copy()
                                ->subYears(2)
                        )

                        ->whereDate(
                            'fecha_compra',
                            '>=',
                            $today
                                ->copy()
                                ->subYears(5)
                        )

                        ->count(),

                'color' =>
                    '#6BB38E',
            ],


            [
                'label' => '6+ años',

                'count' =>
                    $equiposVigentes()

                        ->whereNotNull(
                            'fecha_compra'
                        )

                        ->whereDate(
                            'fecha_compra',
                            '<',
                            $today
                                ->copy()
                                ->subYears(5)
                        )

                        ->count(),

                'color' =>
                    '#D9A441',
            ],


            [
                'label' => 'Sin fecha',

                'count' =>
                    $equiposVigentes()

                        ->whereNull(
                            'fecha_compra'
                        )

                        ->count(),

                'color' =>
                    '#94A3B8',
            ],

        ];


        $maxAntiguedad =
            max(
                collect(
                    $antiguedadBase
                )
                    ->pluck('count')
                    ->max() ?? 0,
                1
            );


        $antiguedadEquipos =
            collect(
                $antiguedadBase
            )

                ->map(
                    function (
                        $item
                    ) use (
                        $maxAntiguedad
                    ) {

                        $item['percentage'] =
                            (int) round(
                                (
                                    $item['count']
                                    / $maxAntiguedad
                                ) * 100
                            );


                        return $item;
                    }
                )

                ->values()

                ->all();


        /*
        |--------------------------------------------------------------------------
        | ESTADOS DE MANTENIMIENTO
        |--------------------------------------------------------------------------
        */

        $estadoMantenimientosRaw =
            DB::table(
                'catalogos as c'
            )

                ->join(
                    'catalogo_valores as cv',
                    'cv.id_catalogo',
                    '=',
                    'c.id_catalogo'
                )

                ->leftJoin(
                    'mantenimientos as m',
                    'm.id_estado_mantenimiento',
                    '=',
                    'cv.id_valor'
                )

                ->where(
                    'c.clave',
                    'estado_mantenimiento'
                )

                ->whereIn(
                    'cv.clave',
                    [
                        'PROGRAMADO',
                        'EN_PROCESO',
                        'COMPLETADO',
                        'CANCELADO',
                    ]
                )

                ->select(
                    'cv.id_valor',
                    'cv.clave',
                    'cv.nombre',

                    DB::raw(
                        'COUNT(m.id_mantenimiento) as total'
                    )
                )

                ->groupBy(
                    'cv.id_valor',
                    'cv.clave',
                    'cv.nombre'
                )

                ->orderBy(
                    'cv.id_valor'
                )

                ->get();


        $estadoConfig = [

            'PROGRAMADO' => [
                'label' => 'Programado',
                'color' => '#D9A441',
            ],

            'EN_PROCESO' => [
                'label' => 'En proceso',
                'color' => '#3BA5D9',
            ],

            'COMPLETADO' => [
                'label' => 'Completado',
                'color' => '#6BB38E',
            ],

            'CANCELADO' => [
                'label' => 'Cancelado',
                'color' => '#D9775B',
            ],

        ];


        $estadoMantenimientos = [];


        foreach (
            $estadoConfig
            as $clave => $config
        ) {

            $found =
                $estadoMantenimientosRaw
                    ->firstWhere(
                        'clave',
                        $clave
                    );


            $estadoMantenimientos[] = [

                'key' =>
                    $clave,

                'label' =>
                    $config['label'],

                'count' =>
                    (int) (
                        $found->total
                        ?? 0
                    ),

                'color' =>
                    $config['color'],

            ];
        }


        $maxEstado =
            max(
                collect(
                    $estadoMantenimientos
                )
                    ->pluck('count')
                    ->max() ?? 0,
                1
            );


        $estadoMantenimientos =
            collect(
                $estadoMantenimientos
            )

                ->map(
                    function (
                        $item
                    ) use (
                        $maxEstado
                    ) {

                        $item['percentage'] =
                            (int) round(
                                (
                                    $item['count']
                                    / $maxEstado
                                ) * 100
                            );


                        return $item;
                    }
                )

                ->values()

                ->all();


        /*
        |--------------------------------------------------------------------------
        | MANTENIMIENTOS POR MES
        |--------------------------------------------------------------------------
        */

        $startMonth =
            Carbon::now()
                ->startOfMonth()
                ->subMonths(11);


        $endMonth =
            Carbon::now()
                ->endOfMonth();


        $mantenimientosRaw =
            DB::table(
                'mantenimientos as m'
            )

                ->leftJoin(
                    'catalogo_valores as cv',
                    'cv.id_valor',
                    '=',
                    'm.id_tipo_mantenimiento'
                )

                ->whereNotNull(
                    'm.fecha_intervencion'
                )

                ->whereBetween(
                    'm.fecha_intervencion',
                    [
                        $startMonth
                            ->toDateString(),

                        $endMonth
                            ->toDateString(),
                    ]
                )

                ->selectRaw("
                    DATE_TRUNC(
                        'month',
                        m.fecha_intervencion
                    )::date as mes,

                    cv.clave as tipo,

                    COUNT(*) as total
                ")

                ->groupBy(
                    'mes',
                    'tipo'
                )

                ->orderBy(
                    'mes'
                )

                ->get();


        $monthMap = [];


        foreach (
            range(
                0,
                11
            )
            as $offset
        ) {

            $month =
                $startMonth
                    ->copy()
                    ->addMonths(
                        $offset
                    );


            $key =
                $month
                    ->format(
                        'Y-m-01'
                    );


            $monthMap[$key] = [

                'label' =>
                    $this->monthLabel(
                        $month
                    ),

                'preventivo' =>
                    0,

                'correctivo' =>
                    0,

            ];
        }


        foreach (
            $mantenimientosRaw
            as $row
        ) {

            $mes =
                Carbon::parse(
                    $row->mes
                )
                    ->format(
                        'Y-m-01'
                    );


            if (
                !isset(
                    $monthMap[$mes]
                )
            ) {
                continue;
            }


            if (
                $row->tipo
                === 'PREVENTIVO'
            ) {

                $monthMap[$mes]['preventivo'] =
                    (int) $row->total;
            }


            if (
                $row->tipo
                === 'CORRECTIVO'
            ) {

                $monthMap[$mes]['correctivo'] =
                    (int) $row->total;
            }
        }


        $maxMantenimientosMes =
            max(
                collect(
                    $monthMap
                )
                    ->map(
                        fn ($item) =>
                            max(
                                $item['preventivo'],
                                $item['correctivo']
                            )
                    )
                    ->max() ?? 0,
                1
            );


        $mantenimientosPorMes =
            collect(
                $monthMap
            )

                ->map(
                    function (
                        $item
                    ) use (
                        $maxMantenimientosMes
                    ) {

                        $item['preventivo_percentage'] =
                            (int) round(
                                (
                                    $item['preventivo']
                                    / $maxMantenimientosMes
                                ) * 100
                            );


                        $item['correctivo_percentage'] =
                            (int) round(
                                (
                                    $item['correctivo']
                                    / $maxMantenimientosMes
                                ) * 100
                            );


                        return $item;
                    }
                )

                ->values()

                ->all();


        /*
        |--------------------------------------------------------------------------
        | ACTIVIDAD RECIENTE
        |--------------------------------------------------------------------------
        */

        $actividadReciente =
            DB::table(
                'bitacora_auditoria as b'
            )

                ->leftJoin(
                    'usuarios as u',
                    'u.id_usuario',
                    '=',
                    'b.id_usuario'
                )

                ->selectRaw("
                    b.accion,
                    b.modulo,
                    b.descripcion,
                    b.fecha_hora,

                    TRIM(
                        CONCAT(
                            COALESCE(
                                u.nombres,
                                ''
                            ),
                            ' ',
                            COALESCE(
                                u.apellido_paterno,
                                ''
                            ),
                            ' ',
                            COALESCE(
                                u.apellido_materno,
                                ''
                            )
                        )
                    ) as usuario
                ")

                ->orderByDesc(
                    'b.fecha_hora'
                )

                ->limit(6)

                ->get()

                ->map(
                    function (
                        $row
                    ) {

                        return [

                            'usuario' =>
                                filled(
                                    trim(
                                        (string) $row->usuario
                                    )
                                )

                                    ? trim(
                                        (string) $row->usuario
                                    )

                                    : 'Sistema',


                            'accion' =>
                                (string) $row->accion,


                            'modulo' =>
                                (string) $row->modulo,


                            'descripcion' =>
                                (string) $row->descripcion,


                            /*
                             * Se guarda la fecha original: la vista la
                             * muestra en la zona horaria de cada usuario.
                             */
                            'fecha' =>
                                $row->fecha_hora
                                    ? (string) $row->fecha_hora
                                    : null,

                        ];
                    }
                )

                ->values()

                ->all();


        /*
        |--------------------------------------------------------------------------
        | EQUIPOS POR TIPO
        |--------------------------------------------------------------------------
        |
        | Los 6 tipos con más equipos; el resto se agrupa en «Otros».
        | percentage = parte del inventario; bar = largo relativo al mayor.
        |
        */

        $tiposRaw = $equiposVigentes()
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->selectRaw('e.id_tipo_equipo as id, te.nombre as nombre, COUNT(*) as total')
            ->groupBy('e.id_tipo_equipo', 'te.nombre')
            ->orderByDesc('total')
            ->get();

        $totalTipos = max((int) $tiposRaw->sum('total'), 1);

        $equiposPorTipo = $tiposRaw
            ->take(6)
            ->map(fn ($row) => [
                'id' => $row->id !== null && filled($row->nombre) ? (int) $row->id : null,
                'nombre' => filled($row->nombre) ? (string) $row->nombre : 'Sin tipo',
                'count' => (int) $row->total,
            ])
            ->values()
            ->all();

        $restoTipos = (int) $tiposRaw->slice(6)->sum('total');

        if ($restoTipos > 0) {
            $equiposPorTipo[] = ['id' => null, 'nombre' => 'Otros tipos', 'count' => $restoTipos];
        }

        $maxTipo = max(collect($equiposPorTipo)->max('count') ?? 0, 1);

        $equiposPorTipo = array_map(fn (array $tipo) => $tipo + [
            'percentage' => (int) round($tipo['count'] / $totalTipos * 100),
            'bar' => (int) round($tipo['count'] / $maxTipo * 100),
        ], $equiposPorTipo);

        return [
            'equiposPorTipo' => $equiposPorTipo,
            'antiguedadEquipos' => $antiguedadEquipos,
            'estadoMantenimientos' => $estadoMantenimientos,
            'mantenimientosPorMes' => $mantenimientosPorMes,
            'actividadReciente' => $actividadReciente,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | NOMBRE DE MES
    |--------------------------------------------------------------------------
    */

    protected function monthLabel(
        Carbon $month
    ): string {

        return match (
            $month->month
        ) {

            1 => 'Ene',

            2 => 'Feb',

            3 => 'Mar',

            4 => 'Abr',

            5 => 'May',

            6 => 'Jun',

            7 => 'Jul',

            8 => 'Ago',

            9 => 'Sep',

            10 => 'Oct',

            11 => 'Nov',

            12 => 'Dic',

        };
    }
};

?>


{{-- ============================================================
    PLACEHOLDER
============================================================ --}}

@placeholder

    {{-- Misma estructura que la Vista general: tarjetas y secciones --}}
    <div class="space-y-4" aria-busy="true" aria-live="polite">
        <span class="sr-only">Cargando vista general…</span>

        {{-- Tarjetas de métricas --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @for ($i = 0; $i < 5; $i++)
                <div class="flex min-h-[104px] items-center gap-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3.5">
                    <x-ui.skeleton class="h-14 w-14 shrink-0 rounded-xl" />

                    <div class="flex-1 space-y-2.5">
                        <x-ui.skeleton class="h-7 w-14" />
                        <x-ui.skeleton class="h-3 w-28" />
                    </div>
                </div>
            @endfor
        </div>

        {{-- Secciones: misma rejilla de 3 columnas que la vista real --}}
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach (['md:col-span-2 xl:col-span-1', 'md:col-span-2', '', '', 'md:col-span-2 xl:col-span-1'] as $claseSkeleton)
                <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 {{ $claseSkeleton }}">
                    <x-ui.skeleton class="h-4 w-44" />
                    <x-ui.skeleton class="mt-2 h-3 w-64 max-w-full" />
                    <div class="mt-5 space-y-3">
                        @for ($barra = 0; $barra < 4; $barra++)
                            <x-ui.skeleton class="h-8 w-full rounded-lg" />
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endplaceholder



<div
    class="space-y-4"
    @unless ($seccionesListas || $errorSecciones) wire:init="cargarSecciones" @endunless
>

    {{-- ============================================================
        KPIs PRINCIPALES
    ============================================================ --}}

    <div
        class="
            grid
            grid-cols-1

            sm:grid-cols-2

            xl:grid-cols-5

            gap-3
        "
    >


        {{-- ========================================================
            TOTAL DE EQUIPOS
        ======================================================== --}}

        <a
            href="{{ route('equipos.index', ['situacion' => 'vigentes']) }}"
            wire:navigate
            title="Ver todos los equipos vigentes"
            aria-label="Ver todos los equipos vigentes"
            class="
                min-h-[104px]

                rounded-xl

                border
                border-[var(--theme-primary-border)]

                bg-[var(--theme-surface)]

                px-4
                py-3.5

                flex
                items-center
                gap-4

                shadow-sm

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    w-14
                    h-14

                    shrink-0

                    rounded-xl

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >

                <svg
                    class="
                        w-6
                        h-6
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.5"
                >

                    <rect
                        x="3"
                        y="6"
                        width="18"
                        height="11"
                        rx="1.5"
                    />


                    <path
                        d="
                            M8 20
                            h8
                        "
                    />


                    <path
                        d="
                            M12 17
                            v3
                        "
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <p
                    x-data="
                        dashboardCounter(
                            {{ $totalEquipos }}
                        )
                    "

                    x-init="init()"

                    x-text="display"

                    class="
                        text-[28px]
                        leading-none

                        font-semibold
                        tracking-tight

                        text-[var(--theme-primary)]
                    "
                ></p>


                <p
                    class="
                        mt-2

                        text-xs
                        font-medium

                        text-[var(--theme-primary)]
                    "
                >
                    Total de equipos
                </p>

            </div>

        </a>



        {{-- ========================================================
            EQUIPOS ASIGNADOS
        ======================================================== --}}

        <a
            href="{{ route('equipos.index', ['situacion' => 'asignados']) }}"
            wire:navigate
            title="Ver equipos asignados"
            aria-label="Ver equipos asignados"
            class="
                min-h-[104px]

                rounded-xl

                border
                border-[var(--theme-warning-border)]

                bg-[var(--theme-surface)]

                px-4
                py-3.5

                flex
                items-center
                gap-4

                shadow-sm

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    w-14
                    h-14

                    shrink-0

                    rounded-xl

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-warning-soft)]
                    text-[var(--theme-warning)]
                "
            >

                <svg
                    class="
                        w-6
                        h-6
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.5"
                >

                    <circle
                        cx="12"
                        cy="8"
                        r="3"
                    />


                    <path
                        d="
                            M5 20

                            c0-4
                            2-7
                            7-7

                            s7
                            3
                            7
                            7
                        "
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <p
                    x-data="
                        dashboardCounter(
                            {{ $equiposAsignados }}
                        )
                    "

                    x-init="init()"

                    x-text="display"

                    class="
                        text-[28px]
                        leading-none

                        font-semibold
                        tracking-tight

                        text-[var(--theme-warning)]
                    "
                ></p>


                <p
                    class="
                        mt-2

                        text-xs
                        font-medium

                        text-[var(--theme-warning)]
                    "
                >
                    Equipos asignados
                </p>

            </div>

        </a>



        {{-- ========================================================
            EN MANTENIMIENTO
        ======================================================== --}}

        <a
            href="{{ route('equipos.index', ['situacion' => 'en_mantenimiento']) }}"
            wire:navigate
            title="Ver equipos en mantenimiento"
            aria-label="Ver equipos en mantenimiento"
            class="
                min-h-[104px]

                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]

                px-4
                py-3.5

                flex
                items-center
                gap-4

                shadow-sm

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    w-14
                    h-14

                    shrink-0

                    rounded-xl

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text)]
                "
            >

                <svg
                    class="
                        w-6
                        h-6
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.5"
                >

                    <path
                        d="
                            M14.7 6.3

                            a4 4 0 0 0-5 5

                            L4 17

                            l3 3

                            5.7-5.7

                            a4 4 0 0 0 5-5

                            l-2.4 2.4

                            -3-3z
                        "
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <p
                    x-data="
                        dashboardCounter(
                            {{ $enMantenimiento }}
                        )
                    "

                    x-init="init()"

                    x-text="display"

                    class="
                        text-[28px]
                        leading-none

                        font-semibold
                        tracking-tight

                        text-[var(--theme-text-strong)]
                    "
                ></p>


                <p
                    class="
                        mt-2

                        text-xs
                        font-medium

                        text-[var(--theme-text-muted)]
                    "
                >
                    En mantenimiento
                </p>

            </div>

        </a>



        {{-- ========================================================
            MANTENIMIENTOS VENCIDOS
        ======================================================== --}}

        <a
            href="{{ route('equipos.index', ['situacion' => 'mantenimiento_vencido']) }}"
            wire:navigate
            title="Ver equipos con mantenimiento vencido"
            aria-label="Ver equipos con mantenimiento vencido"
            class="
                min-h-[104px]

                rounded-xl

                border
                border-[var(--theme-warning-border)]

                bg-[var(--theme-surface)]

                px-4
                py-3.5

                flex
                items-center
                gap-4

                shadow-sm

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    w-14
                    h-14

                    shrink-0

                    rounded-xl

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-warning-soft)]
                    text-[var(--theme-warning)]
                "
            >

                <svg
                    class="
                        w-6
                        h-6
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.5"
                >

                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    />


                    <path
                        d="
                            M12 7
                            v5
                        "
                    />


                    <path
                        d="
                            M12 16
                            h.01
                        "
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <p
                    x-data="
                        dashboardCounter(
                            {{ $mantenimientosVencidos }}
                        )
                    "

                    x-init="init()"

                    x-text="display"

                    class="
                        text-[28px]
                        leading-none

                        font-semibold
                        tracking-tight

                        text-[var(--theme-warning)]
                    "
                ></p>


                <p
                    class="
                        mt-2

                        text-xs
                        font-medium

                        text-[var(--theme-warning)]
                    "
                >
                    Mantenimientos vencidos
                </p>

            </div>

        </a>



        {{-- ========================================================
            GARANTÍAS VENCIDAS
        ======================================================== --}}

        <a
            href="{{ route('equipos.index', ['situacion' => 'garantia_vencida']) }}"
            wire:navigate
            title="Ver equipos con garantía vencida"
            aria-label="Ver equipos con garantía vencida"
            class="
                min-h-[104px]

                rounded-xl

                border
                border-[var(--theme-success-border)]

                bg-[var(--theme-surface)]

                px-4
                py-3.5

                flex
                items-center
                gap-4

                shadow-sm

                transition-colors
                hover:bg-[var(--theme-surface-soft)]
                focus-visible:outline-none
                focus-visible:ring-2
                focus-visible:ring-[var(--theme-primary)]
            "
        >

            <div
                class="
                    w-14
                    h-14

                    shrink-0

                    rounded-xl

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-success-soft)]
                    text-[var(--theme-success)]
                "
            >

                <svg
                    class="
                        w-6
                        h-6
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.5"
                >

                    <path
                        d="
                            M12 3

                            l7 3

                            v5

                            c0 5
                            -3 8
                            -7 10

                            c-4-2
                            -7-5
                            -7-10

                            V6z
                        "
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <p
                    x-data="
                        dashboardCounter(
                            {{ $garantiasVencidas }}
                        )
                    "

                    x-init="init()"

                    x-text="display"

                    class="
                        text-[28px]
                        leading-none

                        font-semibold
                        tracking-tight

                        text-[var(--theme-success)]
                    "
                ></p>


                <p
                    class="
                        mt-2

                        text-xs
                        font-medium

                        text-[var(--theme-success)]
                    "
                >
                    Garantías vencidas
                </p>


                <p
                    class="
                        mt-1

                        text-[10px]

                        text-[var(--theme-text-muted)]
                    "
                >
                    {{ number_format($garantiasPorVencer) }}
                    vencen en 30 días
                </p>

            </div>

        </a>

    </div>



    {{-- ============================================================
        GRÁFICAS
        ------------------------------------------------------------
        Rejilla de 3 columnas (escritorio):
        · Asignación (1) + Mantenimientos por mes (2)
        · Equipos por tipo + Estado de mantenimientos + Antigüedad
        Las alertas y la actividad (abajo) usan la misma rejilla.
    ============================================================ --}}
    @php
        /*
         * Dona en SVG: cada segmento es un círculo con dasharray.
         * Se deja un pequeño hueco entre segmentos cuando hay varios.
         */
        $dona = static function (array $segmentos, float $radio = 46): array {
            $circunferencia = 2 * M_PI * $radio;
            $total = array_sum(array_map(fn ($s) => (int) $s['count'], $segmentos));
            $conValor = count(array_filter($segmentos, fn ($s) => (int) $s['count'] > 0));
            $hueco = $conValor > 1 ? 2.5 : 0;
            $acumulado = 0.0;
            $salida = [];

            foreach ($segmentos as $segmento) {
                $largo = $total > 0 ? ((int) $segmento['count'] / $total) * $circunferencia : 0;

                $salida[] = $segmento + [
                    'largo' => max($largo - $hueco, 0),
                    'desfase' => -$acumulado,
                    'porcentaje' => $total > 0 ? (int) round(((int) $segmento['count'] / $total) * 100) : 0,
                ];

                $acumulado += $largo;
            }

            return ['circunferencia' => $circunferencia, 'radio' => $radio, 'total' => $total, 'segmentos' => $salida];
        };

        $claseTarjeta = 'flex min-w-0 flex-col rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4';

        $donaAsignacion = $dona([
            ['label' => 'Asignados', 'count' => $equiposAsignados, 'color' => '#3BA5D9', 'situacion' => 'asignados'],
            ['label' => 'En mantenimiento', 'count' => $enMantenimiento, 'color' => '#D9A441', 'situacion' => 'en_mantenimiento'],
            ['label' => 'Disponibles', 'count' => $equiposDisponibles, 'color' => '#6BB38E', 'situacion' => 'sin_asignar'],
        ]);

        // Secciones que dependen de la segunda carga (para error / skeleton).
        $seccionesDiferidas = [
            'mensual' => ['titulo' => 'Mantenimientos por mes', 'descripcion' => 'Preventivos y correctivos de los últimos 12 meses.', 'clase' => 'md:col-span-2', 'forma' => 'columnas'],
            'tipos' => ['titulo' => 'Equipos por tipo', 'descripcion' => 'Inventario vigente agrupado por tipo de equipo.', 'clase' => '', 'forma' => 'barras'],
            'estados' => ['titulo' => 'Estado de mantenimientos', 'descripcion' => 'Intervenciones registradas según su estado.', 'clase' => '', 'forma' => 'dona'],
            'antiguedad' => ['titulo' => 'Antigüedad de equipos', 'descripcion' => 'Según la fecha de compra registrada.', 'clase' => 'md:col-span-2 xl:col-span-1', 'forma' => 'columnas'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">

        {{-- ========================================================
            ASIGNACIÓN (datos de las tarjetas: siempre disponible)
        ======================================================== --}}
        <section class="{{ $claseTarjeta }} md:col-span-2 xl:col-span-1">
            <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Asignación de equipos</h3>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Cómo se reparte el inventario vigente.</p>

            <div class="mt-4 flex flex-1 flex-col items-center gap-5 sm:flex-row xl:flex-col 2xl:flex-row">
                <div
                    x-data="dashboardBar()"
                    x-init="init()"
                    class="relative h-40 w-40 shrink-0 transition duration-700 ease-out"
                    x-bind:class="ready ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                >
                    <svg class="h-40 w-40 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                        <circle cx="60" cy="60" r="{{ $donaAsignacion['radio'] }}" fill="none" stroke="var(--theme-surface-soft)" stroke-width="14"/>
                        @foreach ($donaAsignacion['segmentos'] as $segmento)
                            @if ($segmento['largo'] > 0)
                                <circle
                                    cx="60" cy="60" r="{{ $donaAsignacion['radio'] }}" fill="none"
                                    stroke="{{ $segmento['color'] }}" stroke-width="14"
                                    stroke-dasharray="{{ round($segmento['largo'], 2) }} {{ round($donaAsignacion['circunferencia'], 2) }}"
                                    stroke-dashoffset="{{ round($segmento['desfase'], 2) }}"
                                />
                            @endif
                        @endforeach
                    </svg>

                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-semibold leading-none text-[var(--theme-text-strong)]">{{ $porcentajeAsignados }}%</span>
                        <span class="mt-1.5 text-[11px] text-[var(--theme-text-muted)]">asignado</span>
                    </div>
                </div>

                <ul class="w-full min-w-0 flex-1 space-y-1.5">
                    @foreach ($donaAsignacion['segmentos'] as $segmento)
                        <li>
                            <a
                                href="{{ route('equipos.index', ['situacion' => $segmento['situacion']]) }}"
                                wire:navigate.hover
                                class="group flex items-center gap-3 rounded-lg bg-[var(--theme-surface-soft)] px-3 py-2.5 transition-colors hover:bg-[color-mix(in_srgb,var(--theme-surface-soft)_70%,var(--theme-text-strong)_6%)]"
                            >
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $segmento['color'] }};"></span>
                                <span class="min-w-0 flex-1 truncate text-xs text-[var(--theme-text)]">{{ $segmento['label'] }}</span>
                                <span class="text-sm font-semibold tabular-nums text-[var(--theme-text-strong)]">{{ number_format($segmento['count']) }}</span>
                                <span class="w-9 text-right text-[11px] tabular-nums text-[var(--theme-text-muted)]">{{ $segmento['porcentaje'] }}%</span>
                                <svg class="h-3.5 w-3.5 shrink-0 text-[var(--theme-text-muted)] opacity-0 transition-opacity group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        @if ($seccionesListas)

            {{-- ====================================================
                MANTENIMIENTOS POR MES
            ==================================================== --}}
            @php
                $preventivosAnio = array_sum(array_column($mantenimientosPorMes, 'preventivo'));
                $correctivosAnio = array_sum(array_column($mantenimientosPorMes, 'correctivo'));
                $mesActual = $mantenimientosPorMes === [] ? ['preventivo' => 0, 'correctivo' => 0] : $mantenimientosPorMes[array_key_last($mantenimientosPorMes)];
                $maximoMes = 0;

                foreach ($mantenimientosPorMes as $mes) {
                    $maximoMes = max($maximoMes, (int) $mes['preventivo'], (int) $mes['correctivo']);
                }

                // Escala con 4 divisiones enteras.
                $escalaMes = $maximoMes <= 4 ? 4 : (int) (ceil($maximoMes / 4) * 4);
                $altoBarra = fn (int $valor) => $valor > 0 ? max(round($valor / $escalaMes * 100, 2), 3) : 0;
            @endphp

            <section class="{{ $claseTarjeta }} md:col-span-2">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Mantenimientos por mes</h3>
                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Preventivos y correctivos de los últimos 12 meses.</p>
                    </div>

                    <dl class="flex shrink-0 gap-2">
                        <div class="rounded-lg bg-[var(--theme-surface-soft)] px-3 py-1.5">
                            <dt class="flex items-center gap-1.5 text-[10px] text-[var(--theme-text-muted)]"><span class="h-2 w-2 rounded-full bg-[#3BA5D9]"></span>Preventivos</dt>
                            <dd class="text-base font-semibold tabular-nums text-[var(--theme-text-strong)]">{{ number_format($preventivosAnio) }}</dd>
                        </div>
                        <div class="rounded-lg bg-[var(--theme-surface-soft)] px-3 py-1.5">
                            <dt class="flex items-center gap-1.5 text-[10px] text-[var(--theme-text-muted)]"><span class="h-2 w-2 rounded-full bg-[#6BB38E]"></span>Correctivos</dt>
                            <dd class="text-base font-semibold tabular-nums text-[var(--theme-text-strong)]">{{ number_format($correctivosAnio) }}</dd>
                        </div>
                        <div class="rounded-lg bg-[var(--theme-surface-soft)] px-3 py-1.5">
                            <dt class="text-[10px] text-[var(--theme-text-muted)]">Este mes</dt>
                            <dd class="text-base font-semibold tabular-nums text-[var(--theme-text-strong)]">{{ number_format((int) $mesActual['preventivo'] + (int) $mesActual['correctivo']) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative mt-5 flex min-h-[200px] flex-1 gap-2">
                    {{-- Eje Y --}}
                    <div class="flex w-6 shrink-0 flex-col justify-between pb-6 text-right text-[10px] tabular-nums text-[var(--theme-text-muted)]" aria-hidden="true">
                        @for ($marca = 4; $marca >= 0; $marca--)
                            <span class="-translate-y-1/2 leading-none first:translate-y-0 last:translate-y-0">{{ (int) ($escalaMes / 4 * $marca) }}</span>
                        @endfor
                    </div>

                    <div class="relative flex min-w-0 flex-1 flex-col">
                        {{-- Líneas guía --}}
                        <div class="pointer-events-none absolute inset-x-0 top-0 bottom-6 flex flex-col justify-between" aria-hidden="true">
                            @for ($marca = 0; $marca < 5; $marca++)
                                <span class="block border-t {{ $marca === 4 ? 'border-[var(--theme-border-strong)]' : 'border-dashed border-[var(--theme-border)]' }}"></span>
                            @endfor
                        </div>

                        <div class="relative grid flex-1 grid-cols-12 gap-1 sm:gap-2">
                            @foreach ($mantenimientosPorMes as $mes)
                                <div class="group relative flex min-w-0 flex-col">
                                    <div class="flex flex-1 items-end justify-center gap-[3px] rounded-t-md transition-colors group-hover:bg-[var(--theme-surface-soft)]">
                                        <div
                                            x-data="dashboardColumn({{ $altoBarra((int) $mes['preventivo']) }})"
                                            x-init="init()"
                                            x-bind:style="{ height: height + '%' }"
                                            class="w-full max-w-[14px] rounded-t-[4px] bg-[#3BA5D9] transition-[height] duration-1000 ease-out"
                                        ></div>
                                        <div
                                            x-data="dashboardColumn({{ $altoBarra((int) $mes['correctivo']) }})"
                                            x-init="init()"
                                            x-bind:style="{ height: height + '%' }"
                                            class="w-full max-w-[14px] rounded-t-[4px] bg-[#6BB38E] transition-[height] duration-1000 ease-out"
                                        ></div>
                                    </div>

                                    <span class="mt-2 h-4 truncate text-center text-[10px] text-[var(--theme-text-muted)]">{{ $mes['label'] }}</span>

                                    {{-- Detalle al pasar el cursor --}}
                                    <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-2.5 py-1.5 text-[11px] theme-shadow-xl group-hover:block">
                                        <p class="font-semibold text-[var(--theme-text-strong)]">{{ $mes['label'] }}</p>
                                        <p class="text-[var(--theme-text)]"><span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-[#3BA5D9]"></span>{{ $mes['preventivo'] }} preventivo(s)</p>
                                        <p class="text-[var(--theme-text)]"><span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-[#6BB38E]"></span>{{ $mes['correctivo'] }} correctivo(s)</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($preventivosAnio + $correctivosAnio === 0)
                        <p class="pointer-events-none absolute inset-0 flex items-center justify-center pb-6 text-xs text-[var(--theme-text-muted)]">
                            Sin mantenimientos en los últimos 12 meses.
                        </p>
                    @endif
                </div>
            </section>

            {{-- ====================================================
                EQUIPOS POR TIPO
            ==================================================== --}}
            <section class="{{ $claseTarjeta }}">
                <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Equipos por tipo</h3>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Inventario vigente agrupado por tipo de equipo.</p>

                @if ($equiposPorTipo === [])
                    <div class="flex flex-1 items-center justify-center py-8 text-xs text-[var(--theme-text-muted)]">Aún no hay equipos registrados.</div>
                @else
                    <ul class="mt-4 space-y-2">
                        @foreach ($equiposPorTipo as $tipo)
                            @php
                                $contenidoTipo = $tipo['id'] !== null;
                            @endphp
                            <li>
                                <{{ $contenidoTipo ? 'a' : 'div' }}
                                    @if ($contenidoTipo)
                                        href="{{ route('equipos.index', ['tipo_activo' => $tipo['id']]) }}"
                                        wire:navigate.hover
                                    @endif
                                    class="group relative flex h-9 items-center overflow-hidden rounded-lg bg-[var(--theme-surface-soft)] {{ $contenidoTipo ? 'transition-shadow hover:ring-1 hover:ring-[var(--theme-primary-border)]' : '' }}"
                                    title="{{ $tipo['nombre'] }}: {{ $tipo['count'] }} ({{ $tipo['percentage'] }}%)"
                                >
                                    <span
                                        x-data="dashboardBar()"
                                        x-init="init()"
                                        class="absolute inset-y-0 left-0 rounded-lg bg-[color-mix(in_srgb,var(--theme-primary)_22%,transparent)] transition-[width] duration-1000 ease-out"
                                        x-bind:style="{ width: ready ? '{{ max($tipo['bar'], 2) }}%' : '0%' }"
                                    ></span>
                                    <span class="relative min-w-0 flex-1 truncate px-3 text-xs font-medium text-[var(--theme-text-strong)]">{{ $tipo['nombre'] }}</span>
                                    <span class="relative pr-3 text-xs tabular-nums text-[var(--theme-text-muted)]">
                                        <span class="font-semibold text-[var(--theme-text-strong)]">{{ number_format($tipo['count']) }}</span>
                                        · {{ $tipo['percentage'] }}%
                                    </span>
                                </{{ $contenidoTipo ? 'a' : 'div' }}>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- ====================================================
                ESTADO DE MANTENIMIENTOS
            ==================================================== --}}
            @php
                $donaEstados = $dona($estadoMantenimientos);
            @endphp

            <section class="{{ $claseTarjeta }}">
                <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Estado de mantenimientos</h3>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Intervenciones registradas según su estado.</p>

                <div class="mt-4 flex flex-1 items-center gap-5">
                    <div
                        x-data="dashboardBar()"
                        x-init="init()"
                        class="relative h-32 w-32 shrink-0 transition duration-700 ease-out"
                        x-bind:class="ready ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                    >
                        <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                            <circle cx="60" cy="60" r="{{ $donaEstados['radio'] }}" fill="none" stroke="var(--theme-surface-soft)" stroke-width="14"/>
                            @foreach ($donaEstados['segmentos'] as $segmento)
                                @if ($segmento['largo'] > 0)
                                    <circle
                                        cx="60" cy="60" r="{{ $donaEstados['radio'] }}" fill="none"
                                        stroke="{{ $segmento['color'] }}" stroke-width="14"
                                        stroke-dasharray="{{ round($segmento['largo'], 2) }} {{ round($donaEstados['circunferencia'], 2) }}"
                                        stroke-dashoffset="{{ round($segmento['desfase'], 2) }}"
                                    />
                                @endif
                            @endforeach
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-semibold leading-none text-[var(--theme-text-strong)]">{{ number_format($donaEstados['total']) }}</span>
                            <span class="mt-1 text-[10px] text-[var(--theme-text-muted)]">registros</span>
                        </div>
                    </div>

                    <ul class="min-w-0 flex-1 space-y-2.5">
                        @foreach ($donaEstados['segmentos'] as $segmento)
                            <li class="flex items-center gap-2 text-xs">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $segmento['color'] }};"></span>
                                <span class="min-w-0 flex-1 truncate text-[var(--theme-text)]">{{ $segmento['label'] }}</span>
                                <span class="font-semibold tabular-nums text-[var(--theme-text-strong)]">{{ number_format($segmento['count']) }}</span>
                                <span class="w-8 text-right text-[11px] tabular-nums text-[var(--theme-text-muted)]">{{ $segmento['porcentaje'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- ====================================================
                ANTIGÜEDAD
            ==================================================== --}}
            @php
                $totalAntiguedad = array_sum(array_column($antiguedadEquipos, 'count'));
            @endphp

            <section class="{{ $claseTarjeta }} md:col-span-2 xl:col-span-1">
                <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Antigüedad de equipos</h3>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Según la fecha de compra registrada.</p>

                <div class="mt-4 grid min-h-[170px] flex-1 grid-cols-4 gap-3">
                    @foreach ($antiguedadEquipos as $item)
                        <div class="flex min-w-0 flex-col items-center" title="{{ $item['label'] }}: {{ $item['count'] }}">
                            <span class="text-sm font-semibold tabular-nums text-[var(--theme-text-strong)]">{{ number_format($item['count']) }}</span>
                            <span class="text-[10px] tabular-nums text-[var(--theme-text-muted)]">
                                {{ $totalAntiguedad > 0 ? (int) round($item['count'] / $totalAntiguedad * 100) : 0 }}%
                            </span>

                            <div class="mt-1.5 flex w-full flex-1 items-end justify-center rounded-md bg-[var(--theme-surface-soft)]">
                                <div
                                    x-data="dashboardColumn({{ $item['count'] > 0 ? max((int) $item['percentage'], 4) : 0 }})"
                                    x-init="init()"
                                    x-bind:style="{ height: height + '%' }"
                                    class="w-full rounded-md transition-[height] duration-1000 ease-out"
                                    style="background: {{ $item['color'] }};"
                                ></div>
                            </div>

                            <span class="mt-2 w-full truncate text-center text-[11px] text-[var(--theme-text)]">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

        @else

            {{-- Error o carga de las secciones diferidas --}}
            @foreach ($seccionesDiferidas as $seccion)
                <section class="{{ $claseTarjeta }} {{ $seccion['clase'] }}" @unless ($errorSecciones) aria-busy="true" @endunless>
                    <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">{{ $seccion['titulo'] }}</h3>
                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">{{ $seccion['descripcion'] }}</p>

                    @if ($errorSecciones)
                        <x-ui.empty-state variant="error" compact retry="reintentarSecciones" />
                    @else
                        <span class="sr-only">Cargando…</span>

                        @if ($seccion['forma'] === 'columnas')
                            <div class="mt-5 flex h-[170px] items-end gap-2">
                                @foreach ([35, 60, 45, 80, 30, 55, 70, 40, 65, 50, 75, 45] as $alto)
                                    <x-ui.skeleton class="flex-1 w-auto" style="height: {{ $alto }}%;" />
                                @endforeach
                            </div>
                        @elseif ($seccion['forma'] === 'dona')
                            <div class="mt-5 flex items-center gap-5">
                                <x-ui.skeleton class="h-32 w-32 shrink-0 rounded-full" />
                                <div class="flex-1 space-y-3">
                                    @for ($i = 0; $i < 4; $i++)
                                        <x-ui.skeleton class="h-3 w-full" />
                                    @endfor
                                </div>
                            </div>
                        @else
                            <div class="mt-5 space-y-2">
                                @for ($i = 0; $i < 5; $i++)
                                    <x-ui.skeleton class="h-9 w-full rounded-lg" />
                                @endfor
                            </div>
                        @endif
                    @endif
                </section>
            @endforeach

        @endif

    </div>


    {{-- ============================================================
        ALERTAS + ACTIVIDAD
    ============================================================ --}}

    <div
        class="
            grid
            grid-cols-1

            md:grid-cols-2
            xl:grid-cols-3

            gap-3
        "
    >


        {{-- ========================================================
            ALERTAS
        ======================================================== --}}

        <section
            class="
                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]

                p-4
            "
        >

            <h3
                class="
                    text-sm
                    font-semibold

                    text-[var(--theme-text-strong)]
                "
            >
                Alertas y avisos
            </h3>


            <p
                class="
                    mt-1

                    text-xs

                    text-[var(--theme-text-muted)]
                "
            >
                Indicadores que requieren atención.
            </p>


            <div
                class="
                    mt-4
                    space-y-2
                "
            >

                @foreach ($alertas as $item)

                    <div
                        class="
                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            px-3
                            py-2.5
                        "

                        style="
                            background:
                            {{ $item['soft'] }};
                        "
                    >

                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-3
                            "
                        >

                            <div
                                class="
                                    min-w-0

                                    flex
                                    items-start
                                    gap-2.5
                                "
                            >

                                <span
                                    class="
                                        mt-1

                                        w-2
                                        h-2

                                        shrink-0

                                        rounded-full
                                    "

                                    style="
                                        background:
                                        {{ $item['color'] }};
                                    "
                                ></span>


                                <div class="min-w-0">

                                    <p
                                        class="
                                            text-xs
                                            font-semibold

                                            text-[var(--theme-text-strong)]
                                        "
                                    >
                                        {{ $item['title'] }}
                                    </p>


                                    <p
                                        class="
                                            mt-0.5

                                            text-[10px]
                                            leading-4

                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        {{ $item['description'] }}
                                    </p>

                                </div>

                            </div>


                            <span
                                x-data="
                                    dashboardCounter(
                                        {{ $item['count'] }},
                                        700
                                    )
                                "

                                x-init="init()"

                                x-text="display"

                                class="
                                    text-xs
                                    font-semibold

                                    text-[var(--theme-text-strong)]
                                "
                            ></span>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>



        {{-- ========================================================
            ACTIVIDAD RECIENTE
        ======================================================== --}}

        <div class="min-w-0 md:col-span-1 xl:col-span-2">

        @if ($seccionesListas)

        <section
            class="
                rounded-xl

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]

                overflow-hidden
            "
        >

            <div
                class="
                    px-4
                    py-3

                    border-b
                    border-[var(--theme-border)]
                "
            >

                <h3
                    class="
                        text-sm
                        font-semibold

                        text-[var(--theme-text-strong)]
                    "
                >
                    Actividad reciente
                </h3>


                <p
                    class="
                        mt-1

                        text-xs

                        text-[var(--theme-text-muted)]
                    "
                >
                    Últimos movimientos registrados en bitácora.
                </p>

            </div>


            <div class="overflow-x-auto">

                <table
                    class="
                        min-w-full
                        table-fixed
                    "
                >

                    <thead>

                        <tr
                            class="
                                border-b
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface-soft)]
                            "
                        >

                            <th
                                class="
                                    px-3
                                    py-2

                                    text-left

                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-wide

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Usuario
                            </th>


                            <th
                                class="
                                    px-3
                                    py-2

                                    text-left

                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-wide

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Acción
                            </th>


                            <th
                                class="
                                    px-3
                                    py-2

                                    text-left

                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-wide

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Detalle
                            </th>


                            <th
                                class="
                                    px-3
                                    py-2

                                    text-left

                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-wide

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Fecha
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($actividadReciente as $item)

                            <tr
                                class="
                                    border-b
                                    border-[var(--theme-border)]

                                    last:border-b-0
                                "
                            >

                                <td
                                    class="
                                        px-3
                                        py-2.5

                                        align-top
                                    "
                                >

                                    <p
                                        class="
                                            text-xs
                                            font-medium

                                            text-[var(--theme-text-strong)]
                                        "
                                    >
                                        {{ $item['usuario'] }}
                                    </p>


                                    <p
                                        class="
                                            mt-0.5

                                            text-[10px]

                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        {{ $item['modulo'] }}
                                    </p>

                                </td>


                                <td
                                    class="
                                        px-3
                                        py-2.5

                                        align-top
                                    "
                                >

                                    <span
                                        class="
                                            inline-flex

                                            rounded-full

                                            bg-[var(--theme-surface-soft)]

                                            px-2
                                            py-1

                                            text-[10px]
                                            font-medium

                                            text-[var(--theme-text)]
                                        "
                                    >
                                        {{ $item['accion'] }}
                                    </span>

                                </td>


                                <td
                                    class="
                                        px-3
                                        py-2.5

                                        align-top
                                    "
                                >

                                    <p
                                        class="
                                            text-xs
                                            leading-4

                                            text-[var(--theme-text)]
                                        "
                                    >
                                        {{ $item['descripcion'] }}
                                    </p>

                                </td>


                                <td
                                    class="
                                        px-3
                                        py-2.5

                                        align-top
                                    "
                                >

                                    <p
                                        class="
                                            whitespace-nowrap

                                            text-[10px]

                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        {{ \App\Support\Fecha::conHora($item['fecha']) }}
                                    </p>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4">
                                    <x-ui.empty-state
                                        compact
                                        title="Sin actividad reciente"
                                        description="Aquí aparecerán los últimos cambios registrados en bitácora."
                                    />
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        @elseif ($errorSecciones)

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4">
                <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Actividad reciente</h3>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Últimos movimientos registrados en bitácora.</p>

                <x-ui.empty-state variant="error" compact retry="reintentarSecciones" />
            </section>

        @else

            {{-- Skeleton: título real y forma del contenido --}}
            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4" aria-busy="true">
                <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Actividad reciente</h3>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Últimos movimientos registrados en bitácora.</p>
                <span class="sr-only">Cargando…</span>

                <div class="mt-4 space-y-3">
                    @for ($i = 0; $i < 5; $i++)
                        <div class="flex items-center gap-4">
                            <x-ui.skeleton class="h-3 w-28" />
                            <x-ui.skeleton variant="pill" class="h-5 w-24" />
                            <x-ui.skeleton class="h-3 flex-1 w-auto" />
                            <x-ui.skeleton class="h-3 w-20" />
                        </div>
                    @endfor
                </div>
            </section>

        @endif

        </div>

    </div>

</div>



{{-- ============================================================
    ANIMACIONES DEL DASHBOARD
    Las funciones (dashboardCounter, dashboardBar, dashboardColumn,
    dashboardRing) están en layouts/partials/dashboard-script.blade.php
============================================================ --}}
