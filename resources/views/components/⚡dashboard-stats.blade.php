<?php

use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component {

    public int $totalActivos = 0;
    public int $equiposAsignados = 0;
    public int $enMantenimiento = 0;
    public int $stockDisponible = 0;


    public function mount(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Estadísticas principales
        |--------------------------------------------------------------------------
        |
        | Una sola consulta para reducir viajes hacia Supabase.
        |
        */

        $row = DB::selectOne('
            SELECT
                (
                    SELECT COUNT(*)
                    FROM equipos
                ) AS total_activos,

                (
                    SELECT COUNT(DISTINCT id_equipo)
                    FROM asignaciones
                    WHERE fecha_fin IS NULL
                ) AS equipos_asignados,

                (
                    SELECT COUNT(DISTINCT id_equipo)
                    FROM mantenimientos
                    WHERE hora_fin IS NULL
                ) AS en_mantenimiento
        ');


        $this->totalActivos =
            (int) $row->total_activos;

        $this->equiposAsignados =
            (int) $row->equipos_asignados;

        $this->enMantenimiento =
            (int) $row->en_mantenimiento;


        $this->stockDisponible = max(
            $this->totalActivos
                - $this->equiposAsignados
                - $this->enMantenimiento,
            0
        );
    }
};

?>


{{-- ============================================================
    PLACEHOLDER
============================================================ --}}
@placeholder

    <div
        class="
            grid
            grid-cols-2
            md:grid-cols-4

            gap-4

            mb-14

            animate-pulse
        "
    >

        @for ($i = 0; $i < 4; $i++)

            <div
                class="
                    bg-[var(--theme-surface)]

                    border-2
                    border-[var(--theme-border)]

                    rounded-lg

                    px-4
                    sm:px-5

                    py-5

                    flex
                    flex-col

                    sm:flex-row
                    sm:items-center

                    gap-3
                    sm:gap-5
                "
            >

                <div
                    class="
                        w-12
                        h-12

                        sm:w-14
                        sm:h-14

                        shrink-0

                        rounded-lg

                        bg-[var(--theme-surface-soft)]
                    "
                ></div>


                <div class="flex-1 space-y-2">

                    <div
                        class="
                            h-7
                            w-16

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>


                    <div
                        class="
                            h-3
                            w-20
                            sm:w-24

                            rounded

                            bg-[var(--theme-surface-soft)]
                        "
                    ></div>

                </div>

            </div>

        @endfor

    </div>

@endplaceholder



{{-- ============================================================
    ESTADÍSTICAS
============================================================ --}}
<div
    class="
        grid
        grid-cols-2
        md:grid-cols-4

        gap-4

        mb-14
    "
>

    {{-- ========================================================
        TOTAL DE ACTIVOS
    ======================================================== --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border-2
            border-[var(--theme-primary-border)]

            rounded-lg

            px-4
            sm:px-5

            py-5

            flex
            flex-col

            sm:flex-row
            sm:items-center

            gap-3
            sm:gap-5

            transition-[border-color,box-shadow]
            duration-200

            hover:border-[var(--theme-primary)]
            hover:shadow-[0_4px_12px_var(--theme-shadow)]
        "
    >

        <div
            class="
                w-12
                h-12

                sm:w-14
                sm:h-14

                shrink-0

                rounded-lg

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

                    sm:w-7
                    sm:h-7
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <rect
                    x="3"
                    y="7"
                    width="18"
                    height="10"
                    rx="1"
                />

                <path d="M7 14h1"/>
                <path d="M11 14h1"/>
                <path d="M15 14h1"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-2xl
                    sm:text-3xl

                    font-semibold

                    text-[var(--theme-primary)]
                "
            >
                {{ number_format($totalActivos) }}
            </p>


            <p
                class="
                    mt-1

                    text-[11px]
                    sm:text-xs

                    text-[var(--theme-primary)]
                "
            >
                Total de Activos
            </p>

        </div>

    </div>



    {{-- ========================================================
        EQUIPOS ASIGNADOS
    ======================================================== --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border-2
            border-[var(--theme-warning-border)]

            rounded-lg

            px-4
            sm:px-5

            py-5

            flex
            flex-col

            sm:flex-row
            sm:items-center

            gap-3
            sm:gap-5

            transition-[border-color,box-shadow]
            duration-200

            hover:border-[var(--theme-warning)]
            hover:shadow-[0_4px_12px_var(--theme-shadow)]
        "
    >

        <div
            class="
                w-12
                h-12

                sm:w-14
                sm:h-14

                shrink-0

                rounded-lg

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

                    sm:w-7
                    sm:h-7
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
                        c0-4 2-7 7-7
                        s7 3 7 7
                    "
                />
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-2xl
                    sm:text-3xl

                    font-semibold

                    text-[var(--theme-warning)]
                "
            >
                {{ number_format($equiposAsignados) }}
            </p>


            <p
                class="
                    mt-1

                    text-[11px]
                    sm:text-xs

                    text-[var(--theme-warning)]
                "
            >
                Equipos Asignados
            </p>

        </div>

    </div>



    {{-- ========================================================
        EN MANTENIMIENTO
    ======================================================== --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border-2
            border-[var(--theme-border-strong)]

            rounded-lg

            px-4
            sm:px-5

            py-5

            flex
            flex-col

            sm:flex-row
            sm:items-center

            gap-3
            sm:gap-5

            transition-[border-color,box-shadow]
            duration-200

            hover:border-[var(--theme-text-muted)]
            hover:shadow-[0_4px_12px_var(--theme-shadow)]
        "
    >

        <div
            class="
                w-12
                h-12

                sm:w-14
                sm:h-14

                shrink-0

                rounded-lg

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

                    sm:w-7
                    sm:h-7
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M12 3l10 18H2z"/>

                <path d="M12 9v5"/>

                <circle
                    cx="12"
                    cy="17"
                    r=".6"
                    fill="currentColor"
                />
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-2xl
                    sm:text-3xl

                    font-semibold

                    text-[var(--theme-text-strong)]
                "
            >
                {{ number_format($enMantenimiento) }}
            </p>


            <p
                class="
                    mt-1

                    text-[11px]
                    sm:text-xs

                    text-[var(--theme-text-muted)]
                "
            >
                En Mantenimiento
            </p>

        </div>

    </div>



    {{-- ========================================================
        STOCK DISPONIBLE
    ======================================================== --}}
    <div
        class="
            bg-[var(--theme-surface)]

            border-2
            border-[var(--theme-success-border)]

            rounded-lg

            px-4
            sm:px-5

            py-5

            flex
            flex-col

            sm:flex-row
            sm:items-center

            gap-3
            sm:gap-5

            transition-[border-color,box-shadow]
            duration-200

            hover:border-[var(--theme-success)]
            hover:shadow-[0_4px_12px_var(--theme-shadow)]
        "
    >

        <div
            class="
                w-12
                h-12

                sm:w-14
                sm:h-14

                shrink-0

                rounded-lg

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

                    sm:w-7
                    sm:h-7
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M4 7l8-4 8 4-8 4z"/>
                <path d="M4 7v10l8 4 8-4V7"/>
                <path d="M12 11v10"/>
            </svg>
        </div>


        <div class="min-w-0">

            <p
                class="
                    text-2xl
                    sm:text-3xl

                    font-semibold

                    text-[var(--theme-success)]
                "
            >
                {{ number_format($stockDisponible) }}
            </p>


            <p
                class="
                    mt-1

                    text-[11px]
                    sm:text-xs

                    text-[var(--theme-success)]
                "
            >
                Stock Disponible
            </p>

        </div>

    </div>

</div>