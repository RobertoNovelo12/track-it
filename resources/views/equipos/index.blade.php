@extends('layouts.app')

@section('title', 'Equipos Tecnológicos')

@section('content')

<div>

    {{-- Encabezado --}}
    <div class="flex items-start justify-between mb-6">

        <div>

            <h1
                class="
                    text-xl
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Equipos tecnológicos
            </h1>

            <p
                class="
                    text-xs
                    text-[var(--theme-text-muted)]
                    mt-1
                "
            >
                Equipos Tecnológicos
            </p>

        </div>


        <div class="flex items-center gap-3">

            {{-- ====================================================
                EXPORTAR
            ==================================================== --}}
            <div class="relative">

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
                        right-0
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
                        href="{{ Route::has('equipos.export')
                            ? route('equipos.export', ['format' => 'pdf'])
                            : '#'
                        }}"
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
                        href="{{ Route::has('equipos.export')
                            ? route('equipos.export', ['format' => 'xlsx'])
                            : '#'
                        }}"
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


            {{-- ====================================================
                AÑADIR
            ==================================================== --}}
            <a
                href="{{ Route::has('equipos.create')
                    ? route('equipos.create')
                    : '#'
                }}"
                class="
                    flex
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

        </div>

    </div>


    {{-- ============================================================
        FILTROS, TABLA Y PAGINACIÓN
        Todo vive dentro del componente Livewire.
    ============================================================ --}}
    <livewire:equipos-index defer />

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