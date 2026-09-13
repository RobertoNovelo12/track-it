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
    <livewire:dashboard-stats lazy />


    {{-- Título módulos --}}
    <h2 class="text-xl font-semibold text-[#50514F] mb-6">
        Módulos del Sistema
    </h2>


    {{-- Tarjetas módulos --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

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