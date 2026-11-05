{{-- ============================================================
    MARCAS Y MODELOS
    ------------------------------------------------------------
    Los listados se generan en el servidor y se muestran con el
    componente reutilizable <x-data-table>.
============================================================ --}}
@php
    $controlClass = '
        w-full
        h-10

        rounded-md

        border
        border-[var(--theme-border-strong)]

        bg-[var(--theme-surface-soft)]

        text-xs
        text-[var(--theme-text)]

        placeholder:text-[var(--theme-text-muted)]

        focus:outline-none
        focus:border-[var(--theme-primary)]
        focus:ring-2
        focus:ring-[var(--theme-primary-soft)]

        transition-colors
    ';

    // En la zona horaria del usuario (se guardan en UTC).
    $fecha = fn ($valor) => \App\Support\Fecha::dia($valor);

    $estadoTexto = fn ($registro) => $registro->activo ? 'Activo' : 'Inactivo';
@endphp

<div class="min-w-0 space-y-5">

    {{-- ============================================================
        BUSCADOR, FILTROS Y ACCIÓN
        (El título está en Administración; aquí no se repite.)
    ============================================================ --}}
    <x-admin.filter-bar>
        <x-admin.search
            id="mm-search-{{ $vista }}"
            wire:model.live.debounce.350ms="search"
            :placeholder="$vista === 'marcas' ? 'Buscar por marca o descripción...' : 'Buscar por modelo, marca, tipo o descripción...'"
            :label="$vista === 'marcas' ? 'Buscar marca' : 'Buscar modelo'"
        />

        <x-admin.select
            model="estado"
            placeholder="Estado"
            :options="[
                ['value' => 'todos', 'label' => 'Todos los estados'],
                ['value' => 'activos', 'label' => 'Activos'],
                ['value' => 'inactivos', 'label' => 'Inactivos'],
            ]"
        />

        @if ($vista !== 'marcas')
            <x-admin.select
                model="tipo"
                placeholder="Tipo de equipo"
                :options="array_merge(
                    [['value' => 'todos', 'label' => 'Todos los tipos']],
                    $tiposEquipo
                )"
            />
        @endif

        <x-slot:actions>
            {{-- Mismo diseño que Administración › Equipos (Plantilla) e Inventario (Exportar / Añadir) --}}
            @php
                $claseActual = $vista === 'modelos' ? 'modelo' : 'marca';
                $clasesExportar = $vista === 'marcas' ? ['marca' => 'marcas'] : ($vista === 'modelos' ? ['modelo' => 'modelos'] : ['marca' => 'marcas', 'modelo' => 'modelos']);
                $filtrosExportar = array_filter(['search' => $search, 'estado' => $estado, 'tipo' => $tipo], fn ($v) => $v !== '' && $v !== 'todos');
                $botonSecundario = 'inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 py-2.5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto';
                $menu = 'absolute right-0 z-30 mt-1 w-72 overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] py-1 theme-shadow-xl';
                $opcion = 'block w-full px-3 py-2 text-left text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]';
                $flecha = '<svg class="h-3.5 w-3.5 transition-transform" x-bind:class="abierto && \'rotate-180\'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
            @endphp

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                {{-- Plantilla --}}
                <div class="relative" x-data="{ abierto: false }" x-on:click.outside="abierto = false" x-on:keydown.escape.window="abierto = false">
                    <button type="button" x-on:click="abierto = ! abierto" x-bind:aria-expanded="abierto" class="{{ $botonSecundario }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14"/></svg>
                        Plantilla
                        {!! $flecha !!}
                    </button>

                    <div x-show="abierto" x-cloak x-transition.origin.top.right class="{{ $menu }}">
                        <button type="button" x-on:click="abierto = false; window.trackitDescargar(@js(route('catalogos.marcas-modelos.plantilla', ['formato' => 'xlsx'])))" class="{{ $opcion }}">Descargar en Excel <span class="text-xs text-[var(--theme-text-muted)]">(marcas y modelos)</span></button>
                        <button type="button" x-on:click="abierto = false; window.trackitDescargar(@js(route('catalogos.marcas-modelos.plantilla', ['formato' => 'csv', 'clase' => $claseActual])))" class="{{ $opcion }}">Descargar en CSV <span class="text-xs text-[var(--theme-text-muted)]">({{ $claseActual === 'marca' ? 'marcas' : 'modelos' }})</span></button>
                    </div>
                </div>

                {{-- Exportar (con los filtros aplicados) --}}
                <div class="relative" x-data="{ abierto: false }" x-on:click.outside="abierto = false" x-on:keydown.escape.window="abierto = false">
                    <button type="button" x-on:click="abierto = ! abierto" x-bind:aria-expanded="abierto" class="{{ $botonSecundario }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><use href="{{ \App\Support\Icono::url('i-12264a0') }}"></use></svg>
                        Exportar
                        {!! $flecha !!}
                    </button>

                    <div x-show="abierto" x-cloak x-transition.origin.top.right class="{{ $menu }}">
                        @foreach ($clasesExportar as $claseExportar => $etiquetaExportar)
                            @foreach (['xlsx' => 'Excel', 'csv' => 'CSV'] as $formatoExportar => $nombreFormato)
                                <button
                                    type="button"
                                    x-on:click="abierto = false; window.trackitDescargar(@js(route('catalogos.marcas-modelos.exportar', array_merge(['clase' => $claseExportar, 'formato' => $formatoExportar], $filtrosExportar))))"
                                    class="{{ $opcion }}"
                                >Exportar {{ count($clasesExportar) > 1 ? $etiquetaExportar.' ' : '' }}a {{ $nombreFormato }}@if ($filtrosExportar !== []) <span class="text-xs text-[var(--theme-text-muted)]">(con filtros)</span>@endif</button>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                {{-- Añadir: uno en el formulario o varios desde un archivo --}}
                <div class="relative" x-data="{ abierto: false }" x-on:click.outside="abierto = false" x-on:keydown.escape.window="abierto = false">
                    <button
                        type="button"
                        x-on:click="abierto = ! abierto"
                        x-bind:aria-expanded="abierto"
                        aria-haspopup="menu"
                        class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border border-transparent bg-[var(--theme-primary)] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] sm:w-auto"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>
                        {{ $vista === 'marcas' ? 'Añadir marca' : ($vista === 'modelos' ? 'Añadir modelo' : 'Añadir') }}
                        {!! $flecha !!}
                    </button>

                    <div x-show="abierto" x-cloak x-transition.origin.top.right role="menu" class="absolute right-0 z-30 mt-1 w-60 overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] theme-shadow-xl">
                        <a href="{{ route('catalogos.create', ['tipo' => $claseActual]) }}" wire:navigate.hover role="menuitem" class="block px-4 py-2.5 text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]">
                            <span class="block font-medium">{{ $claseActual === 'marca' ? 'Agregar marca' : 'Agregar modelo' }}</span>
                            <span class="block text-xs text-[var(--theme-text-muted)]">Capturar uno en el formulario</span>
                        </a>
                        {{-- Abre el selector (Excel o CSV) y lleva el archivo a la vista previa. --}}
                        <label role="menuitem" class="block cursor-pointer border-t border-[var(--theme-border)] px-4 py-2.5 text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]">
                            <span class="block font-medium">Importar Excel o CSV</span>
                            <span class="block text-xs text-[var(--theme-text-muted)]">Crear o actualizar varios desde un archivo</span>
                            <input
                                type="file"
                                class="sr-only"
                                accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                x-on:change="abierto = false; window.trackitImportarMarcasModelos?.($event.target.files?.[0]); $event.target.value = ''"
                            >
                        </label>
                    </div>
                </div>
            </div>
        </x-slot:actions>

        <x-slot:footer>
            @if ($search !== '' || $estado !== 'todos' || ($vista !== 'marcas' && $tipo !== 'todos'))
                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="text-xs font-medium text-[var(--theme-primary)] hover:underline"
                >
                    Limpiar filtros
                </button>
            @endif
        </x-slot:footer>
    </x-admin.filter-bar>

    @can('gestionar')
        {{--
            Importar arrastrando (igual que en Inventario): al soltar un Excel
            o CSV en la pestaña se abre la vista previa de importación con ese
            archivo (la página lo sube al cargar).
        --}}
        <div
            x-data="{
                visible: false,
                profundidad: 0,
                esArchivo(evento) { return Array.from(evento.dataTransfer?.types ?? []).includes('Files'); },
                enMenu(evento) { return evento.target instanceof Element && !! evento.target.closest('aside, header, nav, #mobileAppHeader, .mi-barra'); },
            }"
            x-init="
                window.trackitImportarMarcasModelos = (archivo) => {
                    if (! archivo) return;

                    if (! /\.(xlsx|csv)$/i.test(archivo.name)) {
                        window.trackitToast?.('error', 'Solo se aceptan archivos Excel (.xlsx) o CSV.', 'Archivo no válido');
                        return;
                    }

                    window.__trackitArchivoMarcasModelos = archivo;
                    window.Livewire?.navigate
                        ? window.Livewire.navigate(@js(route('catalogos.marcas-modelos.importar')))
                        : (window.location.href = @js(route('catalogos.marcas-modelos.importar')));
                };
            "
            x-on:dragenter.window="if (esArchivo($event)) { profundidad++; visible = true; }"
            x-on:dragleave.window="if (esArchivo($event)) { profundidad = Math.max(0, profundidad - 1); if (profundidad === 0) visible = false; }"
            x-on:dragover.window="if (esArchivo($event)) { $event.preventDefault(); $event.dataTransfer.dropEffect = enMenu($event) ? 'none' : 'copy'; }"
            x-on:drop.window="if (esArchivo($event)) { $event.preventDefault(); profundidad = 0; visible = false; if (! enMenu($event)) window.trackitImportarMarcasModelos($event.dataTransfer.files?.[0]); }"
        >
            <div
                x-show="visible"
                x-cloak
                x-transition.opacity
                class="pointer-events-none fixed inset-0 z-[150] flex items-center justify-center bg-[var(--theme-overlay,rgba(0,0,0,0.45))] p-6"
            >
                <div class="flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-[var(--theme-primary)] bg-[var(--theme-surface)] px-10 py-8 text-center shadow-xl">
                    <svg class="h-9 w-9 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M5 21h14"/></svg>
                    <p class="text-sm font-semibold text-[var(--theme-text-strong)]">Suelta tu Excel o CSV para importar {{ $vista === 'modelos' ? 'modelos' : ($vista === 'marcas' ? 'marcas' : 'marcas y modelos') }}</p>
                    <p class="text-xs text-[var(--theme-text-muted)]">Verás una vista previa antes de guardar.</p>
                </div>
            </div>
        </div>
    @endcan


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    <x-admin.metrics :items="array_merge(
        $vista !== 'modelos' ? [
            ['label' => 'Total de marcas', 'value' => $metricas['totalMarcas'] ?? 0],
            ['label' => 'Marcas inactivas', 'value' => $metricas['marcasInactivas'] ?? 0],
        ] : [],
        $vista !== 'marcas' ? [
            ['label' => 'Total de modelos', 'value' => $metricas['totalModelos'] ?? 0],
            ['label' => 'Modelos en uso', 'value' => $metricas['modelosEnUso'] ?? 0],
        ] : [],
    )" />


    {{-- ============================================================
        CONFIRMACIÓN
    ============================================================ --}}
    @if ($mensaje)
        <div
            role="status"
            class="flex items-start justify-between gap-3 rounded-lg border border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] px-4 py-3 text-sm text-[var(--theme-text-strong)]"
        >
            <span>{{ $mensaje }}</span>

            <button
                type="button"
                wire:click="$set('mensaje', '')"
                aria-label="Cerrar mensaje"
                class="shrink-0 text-[var(--theme-text-muted)] hover:text-[var(--theme-text)]"
            >
                ✕
            </button>
        </div>
    @endif


    {{-- ============================================================
        MARCAS
    ============================================================ --}}
    @if ($vista !== 'modelos' && $marcas)
        <x-data-table
            :rows="$marcas"
            row-key="id"
            key-prefix="marca"
            bulk-trash="marca"

            :sort-field="$sortFieldMarcas"
            :sort-direction="$sortMarcas"
            sort-action="sortMarcasBy"

            per-page-model="perPageMarcas"
            sort-model="sortMarcas"
            refresh-action="$refresh"

            page-name="marcasPage"
            result-singular="marca encontrada"
            result-plural="marcas encontradas"
            empty-message="No se encontraron marcas con estos filtros."
            min-width="900px"

            :columns="[
                ['key' => 'id', 'label' => 'ID', 'width' => '90px', 'sort' => 'id', 'mobile' => false],
                ['key' => 'nombre', 'label' => 'Marca', 'width' => '20%', 'sort' => 'nombre', 'strong' => true],
                ['key' => 'descripcion', 'label' => 'Descripción', 'width' => '26%', 'default' => 'Sin descripción', 'mobile' => 'filled'],
                ['key' => 'activo', 'label' => 'Estado', 'width' => '110px', 'sort' => 'activo', 'type' => 'status', 'value' => $estadoTexto],
                ['key' => 'totalModelos', 'label' => 'Modelos', 'width' => '100px', 'sort' => 'totalModelos'],
                ['key' => 'totalEquipos', 'label' => 'Equipos', 'width' => '100px', 'sort' => 'totalEquipos'],
                ['key' => 'fechaCreacion', 'label' => 'Fecha de creación', 'width' => '170px', 'sort' => 'fechaCreacion', 'value' => fn ($m) => $fecha($m->fechaCreacion), 'mobileLabel' => 'Creada'],
            ]"

            :actions="fn ($marca) => [
                'edit' => $marca->editUrl,
                'menu' => array_values(array_filter([
                    ['label' => 'Editar', 'href' => $marca->editUrl],
                    \App\Support\PapeleraEntidades::accion('marca', $marca->id),
                ])),
            ]"
        />
    @endif


    {{-- ============================================================
        MODELOS
    ============================================================ --}}
    @if ($vista !== 'marcas' && $modelos)
        <x-data-table
            :rows="$modelos"
            row-key="id"
            key-prefix="modelo"
            bulk-trash="modelo"

            :sort-field="$sortFieldModelos"
            :sort-direction="$sortModelos"
            sort-action="sortModelosBy"

            per-page-model="perPageModelos"
            sort-model="sortModelos"
            refresh-action="$refresh"

            page-name="modelosPage"
            result-singular="modelo encontrado"
            result-plural="modelos encontrados"
            empty-message="No se encontraron modelos con estos filtros."
            min-width="1050px"

            :columns="[
                ['key' => 'id', 'label' => 'ID', 'width' => '90px', 'sort' => 'id', 'mobile' => false],
                ['key' => 'nombre', 'label' => 'Modelo', 'width' => '19%', 'sort' => 'nombre', 'strong' => true],
                ['key' => 'marcaNombre', 'label' => 'Marca', 'width' => '14%', 'sort' => 'marcaNombre'],
                ['key' => 'tipoEquipoNombre', 'label' => 'Tipo de equipo', 'width' => '15%', 'sort' => 'tipoEquipoNombre'],
                ['key' => 'descripcion', 'label' => 'Descripción', 'width' => '18%', 'default' => 'Sin descripción', 'mobile' => 'filled'],
                ['key' => 'activo', 'label' => 'Estado', 'width' => '110px', 'sort' => 'activo', 'type' => 'status', 'value' => $estadoTexto],
                ['key' => 'totalEquipos', 'label' => 'Equipos', 'width' => '110px', 'sort' => 'totalEquipos'],
                ['key' => 'fechaCreacion', 'label' => 'Fecha de creación', 'width' => '170px', 'sort' => 'fechaCreacion', 'value' => fn ($m) => $fecha($m->fechaCreacion), 'mobileLabel' => 'Creado'],
            ]"

            :actions="fn ($modelo) => [
                'edit' => $modelo->editUrl,
                'menu' => array_values(array_filter([
                    ['label' => 'Editar', 'href' => $modelo->editUrl],
                    \App\Support\PapeleraEntidades::accion('modelo', $modelo->id),
                ])),
            ]"
        />
    @endif

</div>
