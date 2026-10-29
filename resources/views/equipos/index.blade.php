
@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

<div>
    {{-- Descargar el PDF sin mostrar un mensaje sobre el botón Volver. --}}
    @if (session('descargar_reporte'))
        <span
            x-data
            x-init="window.location.assign(@js(route('reportes.download', session('descargar_reporte'))))"
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
                </div>
            </div>

            @can('gestionar')

            {{-- AÑADIR --}}
            {{-- Si hay un tipo filtrado, el alta abre con ese tipo. --}}
            <a
                href="{{ Route::has('equipos.create')
                    ? route('equipos.create')
                    : '#'
                }}"
                x-data
                x-on:click="
                    const tipo = new URLSearchParams(window.location.search).get('tipo_activo');
                    const destino = new URL($el.getAttribute('href'), window.location.origin);
                    destino.searchParams.delete('tipo');
                    if (tipo) destino.searchParams.set('tipo', tipo);
                    $el.href = destino.toString();
                "
                class="
                    flex
                    flex-1
                    justify-center
                    sm:flex-none
                    items-center
                    gap-2
                    bg-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-hover)]
                    rounded-md
                    px-4
                    py-2
                    text-sm
                    font-medium
                    text-white
                    transition-colors
                "
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                ><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>

                <span>Añadir</span>
            </a>

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
