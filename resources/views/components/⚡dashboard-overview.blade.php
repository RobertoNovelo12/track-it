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


    /*
    |--------------------------------------------------------------------------
    | OPERACIÓN
    |--------------------------------------------------------------------------
    */

    public array $alertas = [];

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

        $this->loadDashboard();
    }


    /*
    |--------------------------------------------------------------------------
    | CARGAR DASHBOARD
    |--------------------------------------------------------------------------
    */

    protected function loadDashboard(): void
    {
    $data =
        $this->systemCache()
            ->rememberSimple(
                self::CACHE_MODULE,

                'overview',

                fn () =>
                    $this->buildDashboardData(),

                self::CACHE_TTL_HOURS
            );


        $this->totalEquipos =
            (int) ($data['totalEquipos'] ?? 0);


        $this->equiposAsignados =
            (int) ($data['equiposAsignados'] ?? 0);


        $this->equiposDisponibles =
            (int) ($data['equiposDisponibles'] ?? 0);


        $this->enMantenimiento =
            (int) ($data['enMantenimiento'] ?? 0);


        $this->mantenimientosVencidos =
            (int) ($data['mantenimientosVencidos'] ?? 0);


        $this->garantiasVencidas =
            (int) ($data['garantiasVencidas'] ?? 0);


        $this->garantiasPorVencer =
            (int) ($data['garantiasPorVencer'] ?? 0);


        $this->porcentajeAsignados =
            (int) ($data['porcentajeAsignados'] ?? 0);


        $this->porcentajeMantenimiento =
            (int) ($data['porcentajeMantenimiento'] ?? 0);


        $this->antiguedadEquipos =
            $data['antiguedadEquipos'] ?? [];


        $this->estadoMantenimientos =
            $data['estadoMantenimientos'] ?? [];


        $this->mantenimientosPorMes =
            $data['mantenimientosPorMes'] ?? [];


        $this->alertas =
            $data['alertas'] ?? [];


        $this->actividadReciente =
            $data['actividadReciente'] ?? [];
    }


    /*
    |--------------------------------------------------------------------------
    | CONSTRUIR DATOS
    |--------------------------------------------------------------------------
    */

    protected function buildDashboardData(): array
    {
        $today =
            Carbon::today();


        /*
        |--------------------------------------------------------------------------
        | TOTAL DE EQUIPOS
        |--------------------------------------------------------------------------
        */

        $totalEquipos =
            DB::table('equipos')
                ->count();


        /*
        |--------------------------------------------------------------------------
        | EQUIPOS ASIGNADOS
        |--------------------------------------------------------------------------
        */

        $equiposAsignados =
            DB::table('asignaciones')
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
            DB::table('equipos')

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
            DB::table('equipos')

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
        | ANTIGÜEDAD
        |--------------------------------------------------------------------------
        */

        $antiguedadBase = [

            [
                'label' => '0 - 2 años',

                'count' =>
                    DB::table('equipos')

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
                    DB::table('equipos')

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
                    DB::table('equipos')

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
                    DB::table('equipos')

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


                            'fecha' =>
                                $row->fecha_hora

                                    ? Carbon::parse(
                                        $row->fecha_hora
                                    )
                                        ->format(
                                            'd/m/Y H:i'
                                        )

                                    : '—',

                        ];
                    }
                )

                ->values()

                ->all();


        return [

            'totalEquipos' =>
                $totalEquipos,

            'equiposAsignados' =>
                $equiposAsignados,

            'equiposDisponibles' =>
                $equiposDisponibles,

            'enMantenimiento' =>
                $enMantenimiento,

            'mantenimientosVencidos' =>
                $mantenimientosVencidos,

            'garantiasVencidas' =>
                $garantiasVencidas,

            'garantiasPorVencer' =>
                $garantiasPorVencer,

            'porcentajeAsignados' =>
                $porcentajeAsignados,

            'porcentajeMantenimiento' =>
                $porcentajeMantenimiento,

            'antiguedadEquipos' =>
                $antiguedadEquipos,

            'estadoMantenimientos' =>
                $estadoMantenimientos,

            'mantenimientosPorMes' =>
                $mantenimientosPorMes,

            'alertas' =>
                $alertas,

            'actividadReciente' =>
                $actividadReciente,

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

    <div class="space-y-4 animate-pulse">

        <div
            class="
                flex
                items-center
                justify-between
            "
        >

            <div class="space-y-2">

                <div
                    class="
                        h-5
                        w-40
                        rounded
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

                <div
                    class="
                        h-3
                        w-72
                        rounded
                        bg-[var(--theme-surface-soft)]
                    "
                ></div>

            </div>


            <div
                class="
                    h-9
                    w-28
                    rounded-lg
                    bg-[var(--theme-surface-soft)]
                "
            ></div>

        </div>


        <div
            class="
                grid
                grid-cols-1
                sm:grid-cols-2
                xl:grid-cols-5
                gap-3
            "
        >

            @for ($i = 0; $i < 5; $i++)

                <div
                    class="
                        h-[104px]

                        rounded-xl

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]
                    "
                ></div>

            @endfor

        </div>

    </div>

@endplaceholder



<div class="space-y-4">

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

        <div
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

        </div>



        {{-- ========================================================
            EQUIPOS ASIGNADOS
        ======================================================== --}}

        <div
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

        </div>



        {{-- ========================================================
            EN MANTENIMIENTO
        ======================================================== --}}

        <div
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

        </div>



        {{-- ========================================================
            MANTENIMIENTOS VENCIDOS
        ======================================================== --}}

        <div
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

        </div>



        {{-- ========================================================
            GARANTÍAS VENCIDAS
        ======================================================== --}}

        <div
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

        </div>

    </div>



    {{-- ============================================================
        ASIGNACIÓN + ANTIGÜEDAD
    ============================================================ --}}

    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-2
            gap-3
        "
    >


        {{-- ========================================================
            ASIGNACIÓN
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

            <div>

                <h3
                    class="
                        text-sm
                        font-semibold

                        text-[var(--theme-text-strong)]
                    "
                >
                    Asignación de equipos
                </h3>


                <p
                    class="
                        mt-1

                        text-xs

                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos actualmente entregados a un responsable.
                </p>

            </div>


            <div
                class="
                    mt-4

                    flex
                    flex-col
                    gap-5

                    sm:flex-row
                    sm:items-center
                "
            >

                <div
                    x-data="
                        dashboardRing(
                            {{ $porcentajeAsignados }},
                            1100
                        )
                    "

                    x-init="init()"

                    class="
                        relative

                        w-28
                        h-28

                        shrink-0
                    "
                >

                    <svg
                        class="
                            w-28
                            h-28

                            -rotate-90
                        "

                        viewBox="0 0 120 120"
                    >

                        <circle
                            cx="60"
                            cy="60"
                            r="42"

                            fill="none"

                            stroke="rgba(91,91,214,.12)"

                            stroke-width="9"
                        />


                        <circle
                            cx="60"
                            cy="60"
                            r="42"

                            fill="none"

                            stroke="#5B5BD6"

                            stroke-width="9"

                            stroke-linecap="round"

                            stroke-dasharray="264"

                            :stroke-dashoffset="
                                264
                                -
                                (
                                    264
                                    *
                                    progress
                                    /
                                    100
                                )
                            "
                        />

                    </svg>


                    <div
                        class="
                            absolute
                            inset-0

                            flex
                            flex-col
                            items-center
                            justify-center
                        "
                    >

                        <span
                            x-text="
                                progress + '%'
                            "

                            class="
                                text-xl
                                font-semibold

                                text-[var(--theme-text-strong)]
                            "
                        ></span>


                        <span
                            class="
                                mt-1

                                text-[10px]

                                text-[var(--theme-text-muted)]
                            "
                        >
                            asignado
                        </span>

                    </div>

                </div>


                <div class="flex-1">

                    <div
                        class="
                            grid
                            grid-cols-2
                            gap-2
                        "
                    >

                        <div
                            class="
                                rounded-lg

                                bg-[var(--theme-surface-soft)]

                                px-3
                                py-2.5
                            "
                        >

                            <p
                                class="
                                    text-[10px]

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Asignados
                            </p>


                            <p
                                x-data="
                                    dashboardCounter(
                                        {{ $equiposAsignados }}
                                    )
                                "

                                x-init="init()"

                                x-text="display"

                                class="
                                    mt-1

                                    text-lg
                                    font-semibold

                                    text-[var(--theme-text-strong)]
                                "
                            ></p>

                        </div>


                        <div
                            class="
                                rounded-lg

                                bg-[var(--theme-surface-soft)]

                                px-3
                                py-2.5
                            "
                        >

                            <p
                                class="
                                    text-[10px]

                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Disponibles
                            </p>


                            <p
                                x-data="
                                    dashboardCounter(
                                        {{ $equiposDisponibles }}
                                    )
                                "

                                x-init="init()"

                                x-text="display"

                                class="
                                    mt-1

                                    text-lg
                                    font-semibold

                                    text-[var(--theme-text-strong)]
                                "
                            ></p>

                        </div>

                    </div>


                    <div class="mt-3">

                        <div
                            class="
                                mb-1.5

                                flex
                                items-center
                                justify-between

                                text-xs
                            "
                        >

                            <span
                                class="
                                    text-[var(--theme-text)]
                                "
                            >
                                Progreso
                            </span>


                            <span
                                class="
                                    font-medium

                                    text-[var(--theme-text-strong)]
                                "
                            >
                                {{ $porcentajeAsignados }}%
                            </span>

                        </div>


                        <div
                            x-data="dashboardBar()"

                            x-init="init()"

                            class="
                                h-2

                                overflow-hidden

                                rounded-full

                                bg-[var(--theme-surface-soft)]
                            "
                        >

                            <div
                                class="
                                    h-full

                                    rounded-full

                                    bg-[var(--theme-primary)]

                                    transition-[width]
                                    duration-1000
                                    ease-out
                                "

                                :style="{
                                    width:
                                        ready
                                            ? '{{ $porcentajeAsignados }}%'
                                            : '0%'
                                }"
                            ></div>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- ========================================================
            ANTIGÜEDAD
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
                Antigüedad de equipos
            </h3>


            <p
                class="
                    mt-1

                    text-xs

                    text-[var(--theme-text-muted)]
                "
            >
                Distribución según la fecha de compra registrada.
            </p>


            <div
                class="
                    mt-4
                    space-y-3
                "
            >

                @foreach ($antiguedadEquipos as $item)

                    <div>

                        <div
                            class="
                                mb-1.5

                                flex
                                items-center
                                justify-between
                            "
                        >

                            <span
                                class="
                                    text-xs

                                    text-[var(--theme-text)]
                                "
                            >
                                {{ $item['label'] }}
                            </span>


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


                        <div
                            x-data="dashboardBar()"

                            x-init="init()"

                            class="
                                h-2

                                overflow-hidden

                                rounded-full

                                bg-[var(--theme-surface-soft)]
                            "
                        >

                            <div
                                class="
                                    h-full

                                    rounded-full

                                    transition-[width]
                                    duration-1000
                                    ease-out
                                "

                                style="
                                    background:
                                    {{ $item['color'] }};
                                "

                                :style="{
                                    width:
                                        ready
                                            ? '{{ $item['percentage'] }}%'
                                            : '0%'
                                }"
                            ></div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>

    </div>



    {{-- ============================================================
        MANTENIMIENTOS
    ============================================================ --}}

    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-2
            gap-3
        "
    >


        {{-- ========================================================
            ESTADOS
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
                Estado de mantenimientos
            </h3>


            <p
                class="
                    mt-1

                    text-xs

                    text-[var(--theme-text-muted)]
                "
            >
                Seguimiento operativo de las intervenciones registradas.
            </p>


            <div
                class="
                    mt-4
                    space-y-3
                "
            >

                @foreach ($estadoMantenimientos as $item)

                    <div>

                        <div
                            class="
                                mb-1.5

                                flex
                                items-center
                                justify-between
                            "
                        >

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2
                                "
                            >

                                <span
                                    class="
                                        w-2
                                        h-2

                                        rounded-full
                                    "

                                    style="
                                        background:
                                        {{ $item['color'] }};
                                    "
                                ></span>


                                <span
                                    class="
                                        text-xs

                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $item['label'] }}
                                </span>

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


                        <div
                            x-data="dashboardBar()"

                            x-init="init()"

                            class="
                                h-2

                                overflow-hidden

                                rounded-full

                                bg-[var(--theme-surface-soft)]
                            "
                        >

                            <div
                                class="
                                    h-full

                                    rounded-full

                                    transition-[width]
                                    duration-1000
                                    ease-out
                                "

                                style="
                                    background:
                                    {{ $item['color'] }};
                                "

                                :style="{
                                    width:
                                        ready
                                            ? '{{ $item['percentage'] }}%'
                                            : '0%'
                                }"
                            ></div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>



        {{-- ========================================================
            MANTENIMIENTOS POR MES
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

            <div
                class="
                    flex
                    items-start
                    justify-between
                    gap-3
                "
            >

                <div>

                    <h3
                        class="
                            text-sm
                            font-semibold

                            text-[var(--theme-text-strong)]
                        "
                    >
                        Mantenimientos por mes
                    </h3>


                    <p
                        class="
                            mt-1

                            text-xs

                            text-[var(--theme-text-muted)]
                        "
                    >
                        Preventivos y correctivos de los últimos 12 meses.
                    </p>

                </div>


                <div
                    class="
                        hidden

                        sm:flex
                        items-center
                        gap-3

                        text-[10px]

                        text-[var(--theme-text-muted)]
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            gap-1.5
                        "
                    >

                        <span
                            class="
                                w-2
                                h-2

                                rounded-full

                                bg-[#3BA5D9]
                            "
                        ></span>

                        Preventivo

                    </div>


                    <div
                        class="
                            flex
                            items-center
                            gap-1.5
                        "
                    >

                        <span
                            class="
                                w-2
                                h-2

                                rounded-full

                                bg-[#6BB38E]
                            "
                        ></span>

                        Correctivo

                    </div>

                </div>

            </div>


            <div
                class="
                    mt-4

                    grid
                    grid-cols-12

                    h-40

                    items-end

                    gap-1
                    sm:gap-2
                "
            >

                @foreach ($mantenimientosPorMes as $item)

                    <div
                        class="
                            h-full

                            flex
                            flex-col
                            items-center
                            justify-end
                        "
                    >

                        <div
                            class="
                                flex
                                items-end
                                justify-center

                                h-full

                                gap-[2px]
                            "
                        >

                            <div
                                x-data="
                                    dashboardColumn(
                                        {{ $item['preventivo_percentage'] }}
                                    )
                                "

                                x-init="init()"

                                :style="{
                                    height:
                                        height + '%'
                                }"

                                class="
                                    w-2

                                    rounded-t-sm

                                    bg-[#3BA5D9]

                                    transition-[height]
                                    duration-1000
                                    ease-out
                                "

                                title="
                                    Preventivo:
                                    {{ $item['preventivo'] }}
                                "
                            ></div>


                            <div
                                x-data="
                                    dashboardColumn(
                                        {{ $item['correctivo_percentage'] }}
                                    )
                                "

                                x-init="init()"

                                :style="{
                                    height:
                                        height + '%'
                                }"

                                class="
                                    w-2

                                    rounded-t-sm

                                    bg-[#6BB38E]

                                    transition-[height]
                                    duration-1000
                                    ease-out
                                "

                                title="
                                    Correctivo:
                                    {{ $item['correctivo'] }}
                                "
                            ></div>

                        </div>


                        <span
                            class="
                                mt-2

                                text-[9px]

                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $item['label'] }}
                        </span>

                    </div>

                @endforeach

            </div>

        </section>

    </div>



    {{-- ============================================================
        ALERTAS + ACTIVIDAD
    ============================================================ --}}

    <div
        class="
            grid
            grid-cols-1

            xl:grid-cols-[340px_minmax(0,1fr)]

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
                                        {{ $item['fecha'] }}
                                    </p>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="4"

                                    class="
                                        px-3
                                        py-8

                                        text-center

                                        text-xs

                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    No hay actividad reciente.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</div>



{{-- ============================================================
    ANIMACIONES DEL DASHBOARD
============================================================ --}}

@once

    <script>

        /*
        |--------------------------------------------------------------------------
        | CONTADOR
        |--------------------------------------------------------------------------
        */

        function dashboardCounter(
            target,
            duration = 800
        ) {

            return {

                display: 0,

                target:
                    Number(
                        target
                    ) || 0,

                duration,


                init() {

                    /*
                    |--------------------------------------------------------------
                    | RESPETAR REDUCED MOTION
                    |--------------------------------------------------------------
                    */

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.display =
                            this.target;

                        return;
                    }


                    const start =
                        performance.now();


                    const animate = (
                        now
                    ) => {

                        const progress =
                            Math.min(
                                (
                                    now
                                    - start
                                )
                                / this.duration,
                                1
                            );


                        /*
                        |----------------------------------------------------------
                        | EASING
                        |----------------------------------------------------------
                        */

                        const eased =
                            1
                            -
                            Math.pow(
                                1 - progress,
                                3
                            );


                        this.display =
                            Math.round(
                                this.target
                                * eased
                            );


                        if (
                            progress
                            < 1
                        ) {

                            requestAnimationFrame(
                                animate
                            );

                        } else {

                            this.display =
                                this.target;
                        }
                    };


                    requestAnimationFrame(
                        animate
                    );
                }

            };
        }


        /*
        |--------------------------------------------------------------------------
        | BARRAS
        |--------------------------------------------------------------------------
        */

        function dashboardBar() {

            return {

                ready: false,


                init() {

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.ready =
                            true;

                        return;
                    }


                    requestAnimationFrame(
                        () => {

                            requestAnimationFrame(
                                () => {

                                    this.ready =
                                        true;
                                }
                            );
                        }
                    );
                }

            };
        }


        /*
        |--------------------------------------------------------------------------
        | COLUMNAS
        |--------------------------------------------------------------------------
        */

        function dashboardColumn(
            target
        ) {

            return {

                height: 0,

                target:
                    Number(
                        target
                    ) || 0,


                init() {

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.height =
                            this.target;

                        return;
                    }


                    requestAnimationFrame(
                        () => {

                            requestAnimationFrame(
                                () => {

                                    this.height =
                                        this.target;
                                }
                            );
                        }
                    );
                }

            };
        }


        /*
        |--------------------------------------------------------------------------
        | ANILLO
        |--------------------------------------------------------------------------
        */

        function dashboardRing(
            targetProgress,
            duration = 900
        ) {

            return {

                progress: 0,

                targetProgress:
                    Math.min(
                        Math.max(
                            Number(
                                targetProgress
                            ) || 0,
                            0
                        ),
                        100
                    ),

                duration,


                init() {

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.progress =
                            this.targetProgress;

                        return;
                    }


                    const start =
                        performance.now();


                    const animate = (
                        now
                    ) => {

                        const ratio =
                            Math.min(
                                (
                                    now
                                    - start
                                )
                                / this.duration,
                                1
                            );


                        const eased =
                            1
                            -
                            Math.pow(
                                1 - ratio,
                                3
                            );


                        this.progress =
                            Math.round(
                                this.targetProgress
                                * eased
                            );


                        if (
                            ratio
                            < 1
                        ) {

                            requestAnimationFrame(
                                animate
                            );

                        } else {

                            this.progress =
                                this.targetProgress;
                        }
                    };


                    requestAnimationFrame(
                        animate
                    );
                }

            };
        }

    </script>

@endonce