
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
                DAR DE BAJA / BAJA MÚLTIPLE:
                0 seleccionados = deshabilitado.
                1 seleccionado = baja individual.
                2 a 10 = menú de baja múltiple.
            --}}
            <div
                class="relative flex-1 sm:flex-none"
                x-data="{
                    ids: [],
                    incluyeBajas: false,
                    menu: false,

                    contar() {
                        const escritorio = [...document.querySelectorAll('.row-checkbox:checked:not(:disabled)')]
                            .filter(el => el.getClientRects().length)
                            .map(el => el.value);

                        const movil = [...document.querySelectorAll('[data-export-selected]')]
                            .filter(el => el.getClientRects().length)
                            .map(el => el.dataset.exportSelected);

                        this.ids = [...new Set([...escritorio, ...movil])].filter(Boolean);
                        this.incluyeBajas = [...document.querySelectorAll('.row-checkbox:checked, [data-export-selected]')]
                            .some(el => el.getClientRects().length && el.dataset.dadoDeBaja === '1');

                        if (this.ids.length < 2 || this.incluyeBajas) {
                            this.menu = false;
                        }
                    },

                    pulsar() {
                        this.contar();

                        if (this.ids.length === 0 || this.incluyeBajas) {
                            return;
                        }

                        if (this.ids.length > 10) {
                            window.trackitToast?.(
                                'warning',
                                'Puedes dar de baja hasta 10 equipos a la vez. Desmarca ' + (this.ids.length - 10) + '.',
                                'Demasiados equipos'
                            );
                            return;
                        }

                        if (this.ids.length === 1) {
                            this.ir(null);
                            return;
                        }

                        this.menu = !this.menu;
                    },

                    ir(modo) {
                        this.contar();
                        if (this.ids.length === 0 || this.incluyeBajas) return;
                        this.menu = false;

                        const destino = new URL(
                            @js(route('equipos.baja-multiple')),
                            window.location.origin
                        );

                        destino.searchParams.set('equipos', this.ids.join(','));

                        if (modo) {
                            destino.searchParams.set('modo', modo);
                        }

                        window.Livewire?.navigate
                            ? Livewire.navigate(destino.toString())
                            : (window.location.href = destino.toString());
                    },

                    init() {
                        /* Limpiar oyentes anteriores al navegar con Livewire. */
                        if (window.__recontarBaja) {
                            document.removeEventListener(
                                'change',
                                window.__recontarBaja
                            );

                            document.removeEventListener(
                                'click',
                                window.__recontarBaja
                            );
                        }

                        window.__recontarBaja = () =>
                            setTimeout(
                                () => this.$el.isConnected && this.contar(),
                                0
                            );

                        document.addEventListener(
                            'change',
                            window.__recontarBaja
                        );

                        document.addEventListener(
                            'click',
                            window.__recontarBaja
                        );

                        if (!window.__recontarBajaHook) {
                            window.__recontarBajaHook = true;

                            window.Livewire?.hook?.(
                                'commit',
                                ({ succeed }) =>
                                    succeed(() => window.__recontarBaja?.())
                            );
                        }
                    },
                }"
                x-on:click.outside="menu = false"
                x-on:keydown.escape="menu = false"
            >
                <button
                    type="button"
                    x-on:click="pulsar()"
                    x-bind:disabled="ids.length === 0 || incluyeBajas"
                    x-bind:title="incluyeBajas ? 'La selección incluye equipos que ya están dados de baja' : ids.length === 0 ? 'Marca en la tabla los equipos que quieres dar de baja' : ''"
                    x-bind:aria-haspopup="ids.length > 1 ? 'menu' : null"
                    x-bind:aria-expanded="ids.length > 1 ? menu.toString() : null"
                    class="flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[var(--theme-danger-soft)] px-4 py-2 text-sm font-medium text-[var(--theme-danger)] transition-opacity hover:opacity-80 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:opacity-50 sm:w-auto"
                    disabled
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        aria-hidden="true"
                    ><use href="{{ \App\Support\Icono::url('i-2d2ab80') }}"></use></svg>

                    <span x-text="ids.length > 1 ? 'Baja múltiple (' + ids.length + ')' : 'Dar de baja'">
                        Dar de baja
                    </span>

                    <svg
                        x-show="ids.length > 1"
                        x-cloak
                        class="h-4 w-4 transition-transform"
                        x-bind:class="menu && 'rotate-180'"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                {{-- Menú baja múltiple --}}
                <div
                    x-show="menu"
                    x-cloak
                    x-transition.opacity.duration.100ms
                    role="menu"
                    class="absolute right-0 top-full z-20 mt-2 w-60 overflow-hidden rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] text-left shadow-lg shadow-[var(--theme-shadow)]"
                >
                    <button
                        type="button"
                        role="menuitem"
                        x-on:click="ir('conjunta')"
                        class="block w-full px-4 py-2.5 text-left text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]"
                    >
                        Dar de baja a todos

                        <span class="block text-xs text-[var(--theme-text-muted)]">
                            Un solo formulario y una acta
                        </span>
                    </button>

                    <button
                        type="button"
                        role="menuitem"
                        x-on:click="ir('lote')"
                        class="block w-full px-4 py-2.5 text-left text-sm text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-primary)]"
                    >
                        Baja uno por uno

                        <span class="block text-xs text-[var(--theme-text-muted)]">
                            Pasa al siguiente al confirmar
                        </span>
                    </button>
                </div>
            </div>

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
