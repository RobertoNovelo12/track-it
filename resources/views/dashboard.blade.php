@extends('layouts.app')

@section('title', 'Vista General')

@section('content')

<div>

    {{-- Encabezado --}}
    <div class="mb-8">

        <h1 class="text-xl font-semibold text-[#50514F]">
            Módulos del Sistema
        </h1>

        <p class="text-xs text-[#50514F]/60 mt-1">
            Pantalla Principal <span class="mx-1">&gt;</span>
        </p>

    </div>


    {{-- Indicadores --}}
    <div class="grid grid-cols-4 gap-4 mb-14">

        {{-- Total activos --}}
        <div
            class="
                bg-white
                border-2 border-[#247BA0]/20
                rounded-lg
                px-5 py-5
                flex items-center gap-5
            "
        >

            <div
                class="
                    w-14 h-14
                    bg-[#247BA0]/15
                    rounded-lg
                    flex items-center justify-center
                    text-[#247BA0]
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <rect x="3" y="7" width="18" height="10" rx="1"/>
                    <path d="M7 14h1"/>
                    <path d="M11 14h1"/>
                    <path d="M15 14h1"/>
                </svg>
            </div>

            <div>
                <p class="text-3xl font-semibold text-[#247BA0]">
                    {{ number_format($totalActivos) }}
                </p>

                <p class="text-xs text-[#247BA0] mt-1">
                    Total de Activos
                </p>
            </div>

        </div>


        {{-- Asignados --}}
        <div
            class="
                bg-white
                border-2 border-[#CB8B2A]/15
                rounded-lg
                px-5 py-5
                flex items-center gap-5
            "
        >

            <div
                class="
                    w-14 h-14
                    bg-[#CB8B2A]/15
                    rounded-lg
                    flex items-center justify-center
                    text-[#CB8B2A]
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <circle cx="12" cy="8" r="3"/>
                    <path d="M5 20c0-4 2-7 7-7s7 3 7 7"/>
                </svg>
            </div>

            <div>

                <p class="text-3xl font-semibold text-[#CB8B2A]">
                    {{ number_format($equiposAsignados) }}
                </p>

                <p class="text-xs text-[#CB8B2A] mt-1">
                    Equipos Asignados
                </p>

            </div>

        </div>


        {{-- Mantenimiento --}}
        <div
            class="
                bg-white
                border-2 border-[#50514F]/15
                rounded-lg
                px-5 py-5
                flex items-center gap-5
            "
        >

            <div
                class="
                    w-14 h-14
                    bg-[#50514F]/15
                    rounded-lg
                    flex items-center justify-center
                "
            >
                <svg
                    class="w-7 h-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M12 3l10 18H2z"/>
                    <path d="M12 9v5"/>
                    <circle cx="12" cy="17" r=".6" fill="currentColor"/>
                </svg>
            </div>

            <div>

                <p class="text-3xl font-semibold text-[#50514F]">
                    {{ number_format($enMantenimiento) }}
                </p>

                <p class="text-xs text-[#50514F]/70 mt-1">
                    En Mantenimiento
                </p>

            </div>

        </div>


        {{-- Stock --}}
        <div
            class="
                bg-white
                border-2 border-green-400/30
                rounded-lg
                px-5 py-5
                flex items-center gap-5
            "
        >

            <div
                class="
                    w-14 h-14
                    bg-green-400/20
                    rounded-lg
                    flex items-center justify-center
                    text-green-600
                "
            >
                <svg
                    class="w-7 h-7"
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

            <div>

                <p class="text-3xl font-semibold text-green-600">
                    {{ number_format($stockDisponible) }}
                </p>

                <p class="text-xs text-green-600 mt-1">
                    Stock Disponible
                </p>

            </div>

        </div>

    </div>


    {{-- Título módulos --}}
    <h2 class="text-xl font-semibold text-[#50514F] mb-6">
        Módulos del Sistema
    </h2>


    {{-- Tarjetas módulos --}}
    <div class="grid grid-cols-3 gap-5">

        <x-dashboard.module-card
            title="Gestión de Activos"
            description="Inventario general, registro de marcas, modelos, series y garantías de equipos."
            action="Acceder al inventario"
            route="equipos.index"
            icon="laptop"
        />

        <x-dashboard.module-card
            title="Asignaciones y Movimientos"
            description="Control de préstamos, entregas a personal, cambio de ubicación y bajas definitivas."
            action="Gestionar entregas"
            route="asignaciones.index"
            icon="swap"
        />

        <x-dashboard.module-card
            title="Mantenimiento Técnico"
            description="Programación preventiva, registro de reparaciones correctivas e historial de fallas."
            action="Ver intervenciones"
            route="mantenimientos.index"
            icon="tools"
        />

        <x-dashboard.module-card
            title="Reportes"
            description="Generación de reportes ejecutivos en PDF/Excel e indicadores operativos."
            action="Generar reportes"
            route="reportes.index"
            icon="report"
        />

        <x-dashboard.module-card
            title="Usuarios y Roles"
            description="Administración de usuarios internos, roles, permisos y bitácora de auditoría."
            action="Administrar accesos"
            route="usuarios.index"
            icon="users"
        />

        <x-dashboard.module-card
            title="Catálogos Base"
            description="Parametrización de departamentos, áreas del hotel, estados y proveedores."
            action="Configurar parámetros"
            route="catalogos.index"
            icon="list"
        />

    </div>

</div>

@endsection