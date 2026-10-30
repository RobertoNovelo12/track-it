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
            <x-admin.action-button :href="route('catalogos.create', ['tipo' => $vista === 'modelos' ? 'modelo' : 'marca'])">
                {{ $vista === 'marcas' ? 'Añadir marca' : ($vista === 'modelos' ? 'Añadir modelo' : 'Añadir') }}
            </x-admin.action-button>
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
                ['key' => 'id', 'label' => 'ID', 'width' => '70px', 'sort' => 'id', 'mobile' => false],
                ['key' => 'nombre', 'label' => 'Marca', 'width' => '20%', 'sort' => 'nombre', 'strong' => true],
                ['key' => 'descripcion', 'label' => 'Descripción', 'width' => '26%', 'default' => 'Sin descripción', 'mobile' => 'filled'],
                ['key' => 'activo', 'label' => 'Estado', 'width' => '110px', 'sort' => 'activo', 'type' => 'status', 'value' => $estadoTexto],
                ['key' => 'totalModelos', 'label' => 'Modelos', 'width' => '100px', 'sort' => 'totalModelos'],
                ['key' => 'totalEquipos', 'label' => 'Equipos', 'width' => '100px', 'sort' => 'totalEquipos'],
                ['key' => 'fechaCreacion', 'label' => 'Fecha de creación', 'width' => '150px', 'sort' => 'fechaCreacion', 'value' => fn ($m) => $fecha($m->fechaCreacion), 'mobileLabel' => 'Creada'],
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
                ['key' => 'id', 'label' => 'ID', 'width' => '70px', 'sort' => 'id', 'mobile' => false],
                ['key' => 'nombre', 'label' => 'Modelo', 'width' => '19%', 'sort' => 'nombre', 'strong' => true],
                ['key' => 'marcaNombre', 'label' => 'Marca', 'width' => '14%', 'sort' => 'marcaNombre'],
                ['key' => 'tipoEquipoNombre', 'label' => 'Tipo de equipo', 'width' => '15%', 'sort' => 'tipoEquipoNombre'],
                ['key' => 'descripcion', 'label' => 'Descripción', 'width' => '18%', 'default' => 'Sin descripción', 'mobile' => 'filled'],
                ['key' => 'activo', 'label' => 'Estado', 'width' => '110px', 'sort' => 'activo', 'type' => 'status', 'value' => $estadoTexto],
                ['key' => 'totalEquipos', 'label' => 'Equipos', 'width' => '95px', 'sort' => 'totalEquipos'],
                ['key' => 'fechaCreacion', 'label' => 'Fecha de creación', 'width' => '150px', 'sort' => 'fechaCreacion', 'value' => fn ($m) => $fecha($m->fechaCreacion), 'mobileLabel' => 'Creado'],
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
