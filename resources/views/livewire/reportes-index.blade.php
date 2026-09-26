<div>
    @php
        $tabs = [
            'general' => 'General',
            'altas' => 'Altas',
            'bajas' => 'Bajas',
            'asignaciones' => 'Asignación',
            'reasignaciones' => 'Reasignación',
            'mantenimientos' => 'Mantenimiento',
        ];

        $descripciones = [
            'general' => 'Genera un reporte completo del inventario de activos TI registrado en el sistema.',
            'altas' => 'Consulta los activos registrados durante un periodo y aplica filtros por tipo, estado, marca o modelo.',
            'bajas' => 'Consulta los equipos incluidos en procesos de baja y el estado actual de cada solicitud.',
            'asignaciones' => 'Consulta las asignaciones de equipos a colaboradores, áreas, departamentos y ubicaciones.',
            'reasignaciones' => 'Consulta los movimientos identificados como reasignaciones dentro del historial de activos.',
            'mantenimientos' => 'Consulta las intervenciones de mantenimiento, sus estados y los equipos atendidos.',
        ];

        $estadoLabel = match ($tipoReporte) {
            'bajas' => 'Estado de baja',
            'asignaciones' => 'Estado de asignación',
            'mantenimientos' => 'Estado de mantenimiento',
            default => 'Estado del activo',
        };
    @endphp

    {{-- Tipos de reporte --}}


    <div class="mt-4 grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(300px,1fr)]">
        <div class="space-y-4">



            {{-- Mensaje correcto --}}
            @if ($successMessage)
                <div class="flex items-center gap-2 rounded-lg border border-[var(--theme-success-border)] bg-[var(--theme-success-soft)] px-4 py-3 text-sm text-[var(--theme-success)]">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m8 12 2.5 2.5L16 9"/>
                    </svg>

                    {{ $successMessage }}
                </div>
            @endif

            {{-- Mensaje de error --}}
            @if ($errorMessage)
                <div class="flex items-start gap-2 rounded-lg border border-[var(--theme-danger)] bg-[var(--theme-danger-soft)] px-4 py-3 text-sm text-[var(--theme-danger)]">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v6M12 17h.01"/>
                    </svg>

                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Configuración del reporte --}}
            <form
                wire:submit="aplicarFiltros"
                class="grid gap-6 rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-4 sm:p-5 lg:grid-cols-[minmax(220px,0.85fr)_minmax(0,2fr)]"
                >
                <div class="flex items-center justify-between gap-4 lg:col-span-2">
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                        Configuración del reporte
                    </h2>

                    @if ($busquedaActivo || $tipoEquipo || $estado || $fechaInicio || $fechaFin || $modelo || $marca)
                        <button
                            type="button"
                            wire:click="limpiarFiltros"
                            class="flex items-center gap-1.5 text-xs font-medium text-[var(--theme-primary)] hover:underline"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M4 7h16M9 12h6M11 17h2"/>
                                <path d="m18 4 2 2-2 2"/>
                            </svg>

                            Limpiar filtros
                        </button>
                    @endif
                </div>
<section>
    @php
        $resumenes = [
            'general' => 'Inventario completo de activos TI.',
            'altas' => 'Equipos registrados en el sistema.',
            'bajas' => 'Equipos incluidos en procesos de baja.',
            'asignaciones' => 'Equipos asignados a usuarios o áreas.',
            'reasignaciones' => 'Cambios de asignación de equipos.',
            'mantenimientos' => 'Historial de mantenimientos.',
        ];
    @endphp

    <div class="mb-3 flex items-center gap-2">
        <svg
            class="h-5 w-5 text-[var(--theme-primary)]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path d="M6 3h9l4 4v14H6z"/>
            <path d="M14 3v5h5M9 12h6M9 16h6"/>
        </svg>

        <div>
            <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Tipos de reporte
            </h3>
            <p class="text-xs text-[var(--theme-text-muted)]">
                Selecciona un tipo de reporte.
            </p>
        </div>
    </div>

   <<div
    class="space-y-2"
    x-data="{
        tipos: [@js($tiposReporte[0] ?? 'general')],

        alternar(tipo) {
            this.tipos = [tipo];

            $wire.set(
                'tiposReporte',
                this.tipos,
                false
            );

            $wire.set('estado', '', false);
        },

        seleccionado(tipo) {
            return this.tipos.includes(tipo);
        }
    }"
>
    @foreach ($tabs as $valor => $etiqueta)
        <button
            type="button"
            x-on:click="alternar(@js($valor))"
            wire:key="selector-reporte-{{ $valor }}"
            class="flex w-full items-center gap-3 rounded-lg border px-3 py-2.5 text-left transition-colors"
            x-bind:class="seleccionado(@js($valor))
                ? 'border-[var(--theme-primary-border-strong)] bg-[var(--theme-primary-soft)]'
                : 'border-[var(--theme-border-strong)] bg-[var(--theme-surface)] hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-primary-soft-subtle)]'"
            x-bind:aria-pressed="seleccionado(@js($valor))"
        >
            <span
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border"
                x-bind:class="seleccionado(@js($valor))
                    ? 'border-[var(--theme-primary)] bg-[var(--theme-primary)] text-white'
                    : 'border-[var(--theme-border-strong)] text-transparent'"
            >
                <svg
                    class="h-3.5 w-3.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    aria-hidden="true"
                >
                    <path d="m5 12 4 4L19 6"/>
                </svg>
            </span>

                <span class="text-[var(--theme-primary)]">
                    @switch($valor)
                        @case('general')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M6 3h9l4 4v14H6z"/>
                                <path d="M14 3v5h5M9 12h6M9 16h6"/>
                            </svg>
                            @break

                        @case('altas')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 8v8M8 12h8"/>
                            </svg>
                            @break

                        @case('bajas')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M8 12h8"/>
                            </svg>
                            @break

                        @case('asignaciones')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20c0-4 2-7 6-7 2.2 0 3.8.9 4.8 2.3"/>
                                <path d="M18 12v7M14.5 15.5h7"/>
                            </svg>
                            @break

                        @case('reasignaciones')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 7h15M16 4l3 3-3 3"/>
                                <path d="M20 17H5M8 14l-3 3 3 3"/>
                            </svg>
                            @break

                        @case('mantenimientos')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2-2z"/>
                            </svg>
                            @break
                    @endswitch
                </span>

                <span class="min-w-0">
                    <span class="block text-sm font-medium text-[var(--theme-text-strong)]">
                        {{ $etiqueta }}
                    </span>
                    <span class="block text-[11px] leading-4 text-[var(--theme-text-muted)]">
                        {{ $resumenes[$valor] }}
                    </span>
                </span>
            </button>
        @endforeach
    </div>
</section>
    <section class="flex self-stretch flex-col">
    <div class="grid content-start gap-x-4 gap-y-4 md:grid-cols-2">
                    <div class="flex items-center gap-2 md:col-span-2">
    <svg
        class="h-5 w-5 text-[var(--theme-primary)]"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
    >
        <path d="M4 5h16l-6 7v5l-4 2v-7z"/>
    </svg>

    <div>
        <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">
            Filtros
        </h3>
        <p class="text-xs text-[var(--theme-text-muted)]">
            Define los criterios para tu reporte.
        </p>
    </div>
</div>

{{-- Buscar activo --}}
<label class="block md:col-span-2">
    <span class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">
        Buscar activo
    </span>

    <div class="relative">
        <svg
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--theme-text-muted)]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
        </svg>

        <input
            type="search"
            wire:model="busquedaActivo"
            placeholder="Nombre del equipo o número de serie"
            autocomplete="off"
            class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] pl-10 pr-3 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)] focus:ring-1 focus:ring-[var(--theme-primary)]"
        >
    </div>
</label>


                    {{-- Tipo de equipo --}}
<x-searchable-select
    label="Tipo de equipo"
    wire-model="tipoEquipo"
    :options="collect($tiposEquipo)->map(fn ($tipo) => [
        'value' => $tipo->id_tipo_equipo,
        'label' => $tipo->nombre,
    ])"
    placeholder="Buscar tipo..."
/>

                    {{-- Estado --}}
<x-searchable-select
    :label="$estadoLabel"
    wire-model="estado"
    :options="collect($estados)->map(fn ($item) => [
        'value' => $item['id_valor'],
        'label' => $item['nombre'],
    ])"
    placeholder="Buscar estado..."
/>

                    {{-- Fecha inicial --}}
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">
                            Fecha inicial
                        </span>

                        <div class="relative">
                            <input
                                type="date"
                                wire:model="fechaInicio"
                                class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] focus:border-[var(--theme-primary)] focus:ring-1 focus:ring-[var(--theme-primary)]"
                            >
                        </div>

                        @error('fechaInicio')
                            <span class="mt-1 block text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </span>
                        @enderror
                    </label>

                    {{-- Fecha final --}}
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">
                            Fecha final
                        </span>

                        <div class="relative">
                            <input
                                type="date"
                                wire:model="fechaFin"
                                class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] focus:border-[var(--theme-primary)] focus:ring-1 focus:ring-[var(--theme-primary)]"
                            >

                        </div>

                        @error('fechaFin')
                            <span class="mt-1 block text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </span>
                        @enderror
                    </label>

                    {{-- Modelo --}}
<x-searchable-select
    label="Modelo"
    wire-model="modelo"
    :options="collect($modelos)->map(fn ($item) => [
        'value' => data_get($item, 'id_modelo')
            ?? data_get($item, 'id_valor')
            ?? data_get($item, 'nombre'),
        'label' => data_get($item, 'nombre'),
    ])"
    placeholder="Buscar modelo..."
/>

                    {{-- Marca --}}
<x-searchable-select
    label="Marca"
    wire-model="marca"
    :options="collect($marcas)->map(fn ($item) => [
        'value' => data_get($item, 'id_marca')
            ?? data_get($item, 'id_valor')
            ?? data_get($item, 'nombre'),
        'label' => data_get($item, 'nombre'),
    ])"
    placeholder="Buscar marca..."
/>
</div>

                <div class="mt-4 flex items-center justify-end gap-2 border-t border-[var(--theme-border)] pt-4">

    {{-- Limpiar filtros --}}
    <button
        type="button"
        wire:click="limpiarFiltros"
        wire:loading.attr="disabled"
        wire:target="limpiarFiltros"
        class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-semibold text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-primary-soft-subtle)] disabled:cursor-wait disabled:opacity-60"
    >
        <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path d="M4 4l16 16M20 4 4 20"/>
        </svg>

        <span wire:loading.remove wire:target="limpiarFiltros">
            Limpiar filtros
        </span>

        <span wire:loading wire:target="limpiarFiltros">
            Limpiando…
        </span>
    </button>

    {{-- Aplicar filtros --}}
    <button
        type="submit"
        wire:loading.attr="disabled"
        wire:target="aplicarFiltros"
        class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-[var(--theme-primary-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-semibold text-[var(--theme-primary)] transition-colors hover:bg-[var(--theme-primary-soft)] disabled:cursor-wait disabled:opacity-60"
    >
        <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path d="M4 5h16l-6 7v5l-4 2v-7z"/>
        </svg>

        <span wire:loading.remove wire:target="aplicarFiltros">
            Aplicar filtros
        </span>

        <span wire:loading wire:target="aplicarFiltros">
            Aplicando…
        </span>
    </button>

</div>

<div class="mt-auto flex justify-end pt-4">


    <div class="flex flex-wrap items-center gap-2">
        {{-- Excel --}}
        <button
            type="button"
            wire:click="generarHojaCalculo('xlsx')"
            wire:loading.attr="disabled"
            wire:target="generarHojaCalculo"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 transition-colors hover:bg-emerald-100 disabled:cursor-wait disabled:opacity-60 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="M6 2h8l4 4v16H6z"/>
                <path d="M14 2v5h5"/>
                <path d="M8 12h8M8 16h8M11 10v8M15 10v8"/>
            </svg>
            Excel
        </button>

        {{-- PDF --}}
        <button
            type="button"
            wire:click="generarPdf"
            wire:loading.attr="disabled"
            wire:target="generarPdf"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 transition-colors hover:bg-red-100 disabled:cursor-wait disabled:opacity-60 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="M6 2h8l4 4v16H6z"/>
                <path d="M14 2v5h5M9 12h6M9 16h6"/>
            </svg>

            <span wire:loading.remove wire:target="generarPdf">
                PDF
            </span>

            <span wire:loading wire:target="generarPdf">
                Generando…
            </span>
        </button>

        {{-- CSV --}}
        <button
            type="button"
            wire:click="generarHojaCalculo('csv')"
            wire:loading.attr="disabled"
            wire:target="generarHojaCalculo"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 transition-colors hover:bg-amber-100 disabled:cursor-wait disabled:opacity-60 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="M6 2h8l4 4v16H6z"/>
                <path d="M14 2v5h5"/>
                <path d="M9 12h6M9 15h6M9 18h4"/>
            </svg>
            CSV
        </button>
    </div>

</div>

    </section>

            </form>
        </div>

        {{-- Vista previa --}}
        <aside class="rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    Vista rápida del documento final
                </h2>

                <span class="rounded border border-[var(--theme-border-strong)] px-2 py-1 text-[10px] font-semibold text-[var(--theme-text-muted)]">
                    PDF
                </span>
            </div>

            <div class="aspect-[1/1.12] overflow-hidden border border-[var(--theme-border)] bg-white p-5 shadow-md">
                <div class="flex items-start justify-between border-b-2 border-[#247ba0] pb-3">
                    <div>
                        <p class="text-[9px] font-bold text-[#25344a]">
                            REPORTE DE {{ mb_strtoupper($tipoEtiqueta) }}
                        </p>

                        <p class="mt-0.5 text-[6px] text-[#7a7b79]">
                            Sistema de Control de Activos TI
                        </p>
                    </div>

                    <img
                        src="{{ asset('images/logo-grand-palladium.png') }}"
                        alt="Grand Palladium"
                        class="h-auto w-20"
                    >
                </div>

                <div class="mt-3 grid grid-cols-3 gap-1.5 text-[6px] text-[#50514f]">
                    <div class="border border-[#dde4e8] bg-[#f6f9fa] p-1.5">
                        <b class="block text-[#25344a]">TIPO</b>
                        {{ $tipoEtiqueta }}
                    </div>

                    <div class="border border-[#dde4e8] bg-[#f6f9fa] p-1.5">
                        <b class="block text-[#25344a]">PERIODO</b>
                        {{ $fechaInicio ?: 'Inicio' }} — {{ $fechaFin ?: 'Hoy' }}
                    </div>

                    <div class="border border-[#dde4e8] bg-[#f6f9fa] p-1.5">
                        <b class="block text-[#25344a]">REGISTROS</b>
                        {{ number_format($previewTotal) }}
                    </div>
                </div>

                @if (! $previewReady)
                    <div class="mt-8 rounded border border-dashed border-[#9ab5c0] bg-[#f6f9fa] px-3 py-6 text-center text-[8px] text-[#507180]">
                        Hay cambios pendientes. Presiona “Aplicar filtros” para actualizar la vista previa.
                    </div>
                @elseif (count($previewRecords) === 0)
                    <div class="mt-8 rounded border border-dashed border-[#ccd6da] px-3 py-6 text-center text-[8px] text-[#7a7b79]">
                        No se encontraron registros con estos filtros.
                    </div>
                @else
                    <table class="mt-3 w-full table-fixed border-collapse text-[5.5px] text-[#50514f]">
                        <thead>
                            <tr class="bg-[#247ba0] text-white">
                                <th class="p-1 text-left">Código</th>
                                <th class="p-1 text-left">Equipo</th>
                                <th class="p-1 text-left">Tipo</th>
                                <th class="p-1 text-left">Estado</th>
                                <th class="p-1 text-left">Fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($previewRecords as $fila)
                                <tr class="border-b border-[#e4eaed]">
                                    <td class="truncate p-1">{{ $fila['codigo'] ?: '—' }}</td>
                                    <td class="truncate p-1">{{ $fila['elemento'] ?: '—' }}</td>
                                    <td class="truncate p-1">{{ $fila['tipo'] ?: '—' }}</td>
                                    <td class="truncate p-1">{{ $fila['estado'] ?: '—' }}</td>
                                    <td class="truncate p-1">{{ $fila['fecha'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </aside>
    </div>

    {{-- Historial de reportes --}}
    <section class="mt-5 overflow-hidden rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)]">
        <div class="flex flex-col gap-3 border-b border-[var(--theme-border)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Historial de reportes
            </h2>

            <div class="flex items-center gap-3 text-xs text-[var(--theme-text-muted)]">
                <label class="flex items-center gap-2">
                    <span>Mostrar</span>

                    <select
                        wire:model.live="perPage"
                        class="h-8 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-2 text-xs text-[var(--theme-text)]"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </label>

                <button
                    type="button"
                    wire:click="$set('sort', '{{ $sort === 'asc' ? 'desc' : 'asc' }}')"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md border border-[var(--theme-border-strong)] px-2.5 hover:bg-[var(--theme-surface-soft)]"
                    aria-label="Cambiar orden del historial"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M8 4v16M5 7l3-3 3 3M16 20V4M13 17l3 3 3-3"/>
                    </svg>

                    {{ strtoupper($sort) }}
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Tipo de reporte</th>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Fecha inicial</th>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Fecha final</th>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Generado por</th>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Registros</th>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Formato</th>
                        <th class="whitespace-nowrap px-4 py-3 font-medium">Fecha de creación</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center font-medium">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--theme-border)] text-[var(--theme-text)]">
                    @forelse ($historial as $reporte)
                        <tr
                            wire:key="historial-reporte-{{ $reporte->id_reporte }}"
                            class="hover:bg-[var(--theme-primary-soft-subtle)]"
                        >
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-[var(--theme-text-strong)]">
                                {{ $tabs[$reporte->tipo_reporte] ?? ucfirst($reporte->tipo_reporte) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $reporte->fecha_desde?->format('d/m/Y') ?? 'Sin límite' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $reporte->fecha_hasta?->format('d/m/Y') ?? 'Sin límite' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ trim(($reporte->usuario?->nombres ?? '').' '.($reporte->usuario?->apellido_paterno ?? '')) ?: 'Usuario' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ number_format($reporte->total_registros) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded bg-[var(--theme-danger-soft)] px-2 py-1 text-[10px] font-semibold text-[var(--theme-danger)]">
                                    PDF
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $reporte->fecha_generacion?->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">

                                    {{-- Ver PDF --}}
                                    <a
                                        href="{{ route('reportes.show', $reporte) }}"
                                        target="_blank"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-[var(--theme-text-muted)] hover:bg-[var(--theme-primary-soft)] hover:text-[var(--theme-primary)]"
                                        title="Ver PDF"
                                        aria-label="Ver PDF"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </a>

                                    {{-- Descargar PDF --}}
                                    <a
                                        href="{{ route('reportes.download', $reporte) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-[var(--theme-text-muted)] hover:bg-[var(--theme-primary-soft)] hover:text-[var(--theme-primary)]"
                                        title="Descargar PDF"
                                        aria-label="Descargar PDF"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M12 3v12M7 10l5 5 5-5M4 21h16"/>
                                        </svg>
                                    </a>

                                    {{-- Eliminar PDF --}}
                                    <button
                                        type="button"
                                        wire:click="eliminarReporte({{ $reporte->id_reporte }})"
                                        wire:confirm="¿Deseas eliminar este reporte y su archivo PDF?"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-[var(--theme-text-muted)] hover:bg-[var(--theme-danger-soft)] hover:text-[var(--theme-danger)]"
                                        title="Eliminar reporte"
                                        aria-label="Eliminar reporte"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-[var(--theme-text-muted)]">
                                <svg class="mx-auto mb-3 h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M6 2h9l5 5v15H6zM14 2v6h6M9 13h6M9 17h6"/>
                                </svg>

                                Todavía no se han generado reportes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($historial->hasPages())
            <div class="border-t border-[var(--theme-border)] px-4 py-3">
                {{ $historial->links() }}
            </div>
        @endif
    </section>
</div>
