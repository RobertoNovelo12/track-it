
@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

<div>
    {{-- Descargar el PDF sin mostrar un mensaje sobre el botón Volver. --}}
    @if (session('descargar_reporte'))
        <span
            x-data
            x-init="window.trackitDescargar(@js(route('reportes.download', session('descargar_reporte'))))"
            hidden
            aria-hidden="true"
        ></span>
    @endif

    {{-- Encabezado --}}
    {{-- En pantallas angostas los botones bajan debajo del título
         y se reparten el ancho, en lugar de salirse de la pantalla. --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-end md:justify-between">
        @include('layouts.partials.boton-volver')

        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:gap-3">

            {{-- EXPORTAR --}}
            <div class="relative flex-1 sm:flex-none">
                <button
                    type="button"
                    onclick="
                        event.stopPropagation();
                        document
                            .getElementById('exportarMenu')
                            .classList
                            .toggle('hidden');
                    "
                    class="
                        flex
                        w-full
                        justify-center
                        sm:w-auto
                        items-center
                        gap-2
                        border
                        border-[var(--theme-border-strong)]
                        rounded-md
                        px-4
                        py-2
                        bg-[var(--theme-surface)]
                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]
                        hover:bg-[var(--theme-surface-soft)]
                        hover:text-[var(--theme-text)]
                        hover:border-[var(--theme-primary-border)]
                        transition-colors
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-12264a0') }}"></use></svg>

                    <span>Exportar</span>
                </button>

                {{-- Menú exportar --}}
                <div
                    id="exportarMenu"
                    onclick="event.stopPropagation()"
                    class="
                        hidden
                        absolute
                        left-0
                        sm:left-auto
                        sm:right-0
                        top-full
                        mt-2
                        w-40
                        bg-[var(--theme-surface)]
                        border
                        border-[var(--theme-border)]
                        rounded-md
                        shadow-lg
                        shadow-[var(--theme-shadow)]
                        z-20
                        text-left
                        overflow-hidden
                    "
                >
                    <a
                        href="#"
                        onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('exportar-inventario', {detail: {formato: 'pdf'}}))"
                        class="
                            block
                            px-4
                            py-2.5
                            text-sm
                            text-[var(--theme-text)]
                            hover:bg-[var(--theme-surface-soft)]
                            hover:text-[var(--theme-primary)]
                            transition-colors
                        "
                    >
                        Exportar a PDF
                    </a>

                    <a
                        href="#"
                        onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('exportar-inventario', {detail: {formato: 'xlsx'}}))"
                        class="
                            block
                            px-4
                            py-2.5
                            text-sm
                            text-[var(--theme-text)]
                            hover:bg-[var(--theme-surface-soft)]
                            hover:text-[var(--theme-primary)]
                            transition-colors
                        "
                    >
                        Exportar a Excel
                    </a>

                    {{-- CSV con el formato de importación (se puede editar y volver a importar). --}}
                    <a
                        href="#"
                        onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('exportar-inventario', {detail: {formato: 'csv'}}))"
                        class="
                            block
                            px-4
                            py-2.5
                            text-sm
                            text-[var(--theme-text)]
                            hover:bg-[var(--theme-surface-soft)]
                            hover:text-[var(--theme-primary)]
                            transition-colors
                        "
                    >
                        Exportar a CSV
                    </a>
                </div>
            </div>

            @can('gestionar')

            {{-- AÑADIR: agregar un equipo o importar varios desde CSV --}}
            {{-- Si hay un tipo filtrado, el alta abre con ese tipo. --}}
            <div
                class="relative flex flex-1 sm:flex-none"
                x-data="{ abierto: false }"
                x-on:click.outside="abierto = false"
                x-on:keydown.escape.window="abierto = false"
            >
                <button
                    type="button"
                    x-on:click="abierto = ! abierto"
                    x-bind:aria-expanded="abierto.toString()"
                    aria-haspopup="menu"
                    class="flex flex-1 items-center justify-center gap-2 rounded-md bg-[var(--theme-primary)] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] sm:flex-none"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>
                    <span>Añadir</span>
                    <svg class="h-4 w-4 transition-transform" x-bind:class="abierto && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <div
                    x-show="abierto"
                    x-cloak
                    role="menu"
                    class="absolute right-0 top-full z-20 mt-2 w-56 overflow-hidden rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] text-left shadow-lg shadow-[var(--theme-shadow)]"
                >
                    <a
                        href="{{ route('equipos.create') }}"
                        wire:navigate
                        role="menuitem"
                        x-on:click="
                            abierto = false;
                            const tipo = new URLSearchParams(window.location.search).get('tipo_activo');
                            const destino = new URL($el.getAttribute('href'), window.location.origin);
                            destino.searchParams.delete('tipo');
                            if (tipo) destino.searchParams.set('tipo', tipo);
                            $el.href = destino.toString();
                        "
                        class="block px-4 py-2.5 text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]"
                    >
                        <span class="block font-medium">Agregar equipo</span>
                        <span class="block text-xs text-[var(--theme-text-muted)]">Capturar uno en el formulario</span>
                    </a>

                    <a
                        href="{{ route('equipos.importar') }}"
                        wire:navigate
                        role="menuitem"
                        x-on:click="abierto = false"
                        class="block border-t border-[var(--theme-border)] px-4 py-2.5 text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]"
                    >
                        <span class="block font-medium">Importar CSV</span>
                        <span class="block text-xs text-[var(--theme-text-muted)]">Dar de alta varios desde un archivo</span>
                    </a>
                </div>
            </div>

            {{--
                Dar de baja, baja múltiple y mover a la papelera están en la
                barra de selección (aparece al marcar equipos en la tabla):
                livewire/equipos/results/barra-seleccion.
            --}}

            @endcan

        </div>
    </div>

    {{-- FILTROS, TABLA Y PAGINACIÓN --}}
    {{-- El componente Livewire administra el inventario. --}}
    <livewire:equipos-index
        defer
        :unidad-predeterminada="request()->query() === []
            ? app(\App\Services\PreferenciasUsuarioService::class)->valor('id_unidad_predeterminada')
            : null"
    />

</div>

@push('scripts')

<script>
    function toggleAllRows(source) {
        document
            .querySelectorAll('.row-checkbox:not(:disabled)')
            .forEach(cb => {
                cb.checked = source.checked;
            });
    }

    function toggleRowMenu(event, menuId) {
        event.stopPropagation();

        document
            .querySelectorAll('.row-menu')
            .forEach(menu => {
                if (menu.id !== menuId) {
                    menu.classList.add('hidden');
                }
            });

        const menu = document.getElementById(menuId);

        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function confirmarBaja(equipoId) {
        if (
            confirm(
                '¿Confirmas dar de baja este equipo?'
            )
        ) {
            /*
             * Aquí puedes enviar la solicitud al backend,
             * por ejemplo mediante Livewire o fetch().
             */
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cerrar menús al hacer clic fuera
    |--------------------------------------------------------------------------
    */
    document.addEventListener('click', function () {

        document
            .querySelectorAll('.row-menu')
            .forEach(menu => {
                menu.classList.add('hidden');
            });

        const exportMenu =
            document.getElementById('exportarMenu');

        if (exportMenu) {
            exportMenu.classList.add('hidden');
        }

    });
</script>

@endpush

@endsection
