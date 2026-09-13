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
        // Antes: 3 consultas separadas = 3 viajes de red a Supabase.
        // Ahora: 1 sola consulta con subconsultas = 1 solo viaje de red.
        $row = DB::selectOne('
            SELECT
                (SELECT COUNT(*) FROM equipos) AS total_activos,
                (SELECT COUNT(DISTINCT id_equipo) FROM asignaciones WHERE fecha_fin IS NULL) AS equipos_asignados,
                (SELECT COUNT(DISTINCT id_equipo) FROM mantenimientos WHERE hora_fin IS NULL) AS en_mantenimiento
        ');

        $this->totalActivos = (int) $row->total_activos;
        $this->equiposAsignados = (int) $row->equipos_asignados;
        $this->enMantenimiento = (int) $row->en_mantenimiento;

        $this->stockDisponible = max(
            $this->totalActivos
                - $this->equiposAsignados
                - $this->enMantenimiento,
            0
        );
    }
};
?>

@placeholder
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-14 animate-pulse">

        @for ($i = 0; $i < 4; $i++)
            <div class="bg-white border-2 border-[#50514F]/10 rounded-lg px-4 sm:px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5">

                <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-lg bg-[#50514F]/10"></div>

                <div class="flex-1 space-y-2">
                    <div class="h-7 w-16 rounded bg-[#50514F]/10"></div>
                    <div class="h-3 w-20 sm:w-24 rounded bg-[#50514F]/10"></div>
                </div>

            </div>
        @endfor

    </div>
@endplaceholder


<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-14">

    {{-- Total activos --}}
    <div class="bg-white border-2 border-[#247BA0]/20 rounded-lg px-4 sm:px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5">

        <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 bg-[#247BA0]/15 rounded-lg flex items-center justify-center text-[#247BA0]">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="7" width="18" height="10" rx="1"/>
                <path d="M7 14h1"/>
                <path d="M11 14h1"/>
                <path d="M15 14h1"/>
            </svg>
        </div>

        <div class="min-w-0">
            <p class="text-2xl sm:text-3xl font-semibold text-[#247BA0]">
                {{ number_format($totalActivos) }}
            </p>

            <p class="text-[11px] sm:text-xs text-[#247BA0] mt-1">
                Total de Activos
            </p>
        </div>

    </div>


    {{-- Asignados --}}
    <div class="bg-white border-2 border-[#CB8B2A]/15 rounded-lg px-4 sm:px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5">

        <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 bg-[#CB8B2A]/15 rounded-lg flex items-center justify-center text-[#CB8B2A]">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="8" r="3"/>
                <path d="M5 20c0-4 2-7 7-7s7 3 7 7"/>
            </svg>
        </div>

        <div class="min-w-0">
            <p class="text-2xl sm:text-3xl font-semibold text-[#CB8B2A]">
                {{ number_format($equiposAsignados) }}
            </p>

            <p class="text-[11px] sm:text-xs text-[#CB8B2A] mt-1">
                Equipos Asignados
            </p>
        </div>

    </div>


    {{-- Mantenimiento --}}
    <div class="bg-white border-2 border-[#50514F]/15 rounded-lg px-4 sm:px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5">

        <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 bg-[#50514F]/15 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 3l10 18H2z"/>
                <path d="M12 9v5"/>
                <circle cx="12" cy="17" r=".6" fill="currentColor"/>
            </svg>
        </div>

        <div class="min-w-0">
            <p class="text-2xl sm:text-3xl font-semibold text-[#50514F]">
                {{ number_format($enMantenimiento) }}
            </p>

            <p class="text-[11px] sm:text-xs text-[#50514F]/70 mt-1">
                En Mantenimiento
            </p>
        </div>

    </div>


    {{-- Stock --}}
    <div class="bg-white border-2 border-green-400/30 rounded-lg px-4 sm:px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5">

        <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 bg-green-400/20 rounded-lg flex items-center justify-center text-green-600">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 7l8-4 8 4-8 4z"/>
                <path d="M4 7v10l8 4 8-4V7"/>
                <path d="M12 11v10"/>
            </svg>
        </div>

        <div class="min-w-0">
            <p class="text-2xl sm:text-3xl font-semibold text-green-600">
                {{ number_format($stockDisponible) }}
            </p>

            <p class="text-[11px] sm:text-xs text-green-600 mt-1">
                Stock Disponible
            </p>
        </div>

    </div>

</div>