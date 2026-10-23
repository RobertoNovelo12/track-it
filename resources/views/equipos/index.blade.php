@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

<div>
    @if (session('descargar_reporte'))
        <div x-data x-init="window.location.assign(@js(route('reportes.download', session('descargar_reporte'))))" class="mb-4 text-sm text-[var(--theme-text)]" role="status">
            PDF generado y guardado en Reportes.
            <a href="{{ route('reportes.download', session('descargar_reporte')) }}" class="text-[var(--theme-primary)] underline" download>Descargar PDF</a>
        </div>
    @endif

    {{-- Encabezado --}}
    {{-- En pantallas angostas los botones bajan debajo del título
         y se reparten el ancho, en lugar de salirse de la pantalla. --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-end md:justify-between">
        @include('layouts.partials.boton-volver')



        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:gap-3">

            {{-- ====================================================
                EXPORTAR
            ==================================================== --}}
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
                    >
                        <path d="M12 3v12"/>
                        <path d="M7 10l5 5 5-5"/>
                        <path d="M4 21h16"/>
                    </svg>

                    <span>
                        Exportar
                    </span>

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
                        href="#" onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('exportar-inventario', {detail: {formato: 'pdf'}}))"
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
                        href="#" onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('exportar-inventario', {detail: {formato: 'xlsx'}}))"
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
            {{-- ====================================================
                AÑADIR
            ==================================================== --}}
            {{-- Si hay un tipo filtrado (?tipo_activo=), el alta abre con ese tipo (?tipo=). --}}
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
                >
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                <span>
                    Añadir
                </span>

            </a>
            <a href="{{ route('equipos.baja-multiple') }}" wire:navigate
                class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[var(--theme-danger-soft)] px-4 py-2 text-sm font-medium text-[var(--theme-danger)] transition-colors hover:opacity-80 sm:flex-none">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5"/></svg>
                Baja múltiple
            </a>
            @endcan

        </div>

    </div>


    {{-- ============================================================
        FILTROS, TABLA Y PAGINACIÓN
        Todo vive dentro del componente Livewire.
    ============================================================ --}}
    {{--
        Hotel predeterminado (Ajustes › Preferencias): solo cuando la
        página se abre sin filtros, para no pisar enlaces como los de la
        Vista general.
    --}}
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
            .querySelectorAll('.row-checkbox')
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
