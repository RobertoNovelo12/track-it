@extends('layouts.app')

@section('title', 'Administración')

@section('content')

@include('catalogos.partials.marcas-modelos-alpine')

@php
    /*
    |--------------------------------------------------------------------------
    | Etiquetas iniciales
    |--------------------------------------------------------------------------
    |
    | Estas son únicamente las etiquetas predeterminadas.
    |
    | Más adelante serán obtenidas desde la plantilla activa sin cambiar
    | las claves internas utilizadas por el sistema.
    |
    | IMPORTANTE:
    | "areas" seguirá siendo la clave interna aunque visualmente pueda
    | llamarse "Hoteles", "Áreas", "Propiedades", etc.
    |
    */

    $administrationSections = [
        [
            'key' => 'equipos',
            'label' => 'Equipos',
        ],
        [
            'key' => 'marcas',
            'label' => 'Marcas',
        ],
        [
            'key' => 'modelos',
            'label' => 'Modelos',
        ],
        [
            'key' => 'areas',
            'label' => 'Hoteles',
        ],
        [
            'key' => 'departamentos',
            'label' => 'Departamentos',
        ],
        [
            'key' => 'plantillas',
            'label' => 'Plantillas',
        ],
        [
            'key' => 'organizacion',
            'label' => 'Organización',
        ],
    ];
@endphp


<div
    class="
        w-full
        max-w-[1600px]
        mx-auto
    "
    x-data="{
        section: 'equipos',

        mobileMenuOpen: false,

        sections: @js($administrationSections),

        init() {
            const params =
                new URLSearchParams(
                    window.location.search
                );

            const requestedSection =
                params.get('section');

            const exists =
                this.sections.some(
                    item =>
                        item.key === requestedSection
                );

            if (exists) {
                this.section =
                    requestedSection;
            }
        },

        setSection(section) {
            const exists =
                this.sections.some(
                    item =>
                        item.key === section
                );

            if (!exists) {
                return;
            }

            this.section =
                section;

            this.mobileMenuOpen =
                false;

            const url =
                new URL(
                    window.location.href
                );

            url.searchParams.set(
                'section',
                section
            );

            window.history.replaceState(
                {},
                '',
                url
            );
        },

        sectionLabel(section = null) {
            const key =
                section ?? this.section;

            const item =
                this.sections.find(
                    sectionItem =>
                        sectionItem.key === key
                );

            return item?.label ?? '';
        },
    }"
>


    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div class="mb-6">

        <h1
            class="
                text-xl
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Administración
        </h1>

    </div>


    {{-- ============================================================
        NAVEGACIÓN ESCRITORIO
    ============================================================ --}}
    <div
        class="
            hidden
            md:flex
            md:flex-wrap
            md:items-center
            gap-2

            mb-5
        "
    >
        <template
            x-for="item in sections"
            :key="item.key"
        >
            <button
                type="button"

                @click="setSection(item.key)"

                class="
                    inline-flex
                    items-center
                    justify-center

                    min-h-9

                    px-4
                    py-2

                    border

                    rounded-md

                    text-xs
                    font-medium

                    transition-colors
                    duration-150
                "

                :class="
                    section === item.key
                        ? `
                            border-[var(--theme-primary)]
                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]
                        `
                        : `
                            border-[var(--theme-border)]
                            bg-[var(--theme-surface)]
                            text-[var(--theme-text-muted)]

                            hover:text-[var(--theme-text)]
                            hover:border-[var(--theme-border-strong)]
                            hover:bg-[var(--theme-surface-soft)]
                        `
                "
            >
                <span
                    x-text="item.label"
                ></span>
            </button>
        </template>
    </div>


    {{-- ============================================================
        NAVEGACIÓN MÓVIL
    ============================================================ --}}
    <div
        class="
            md:hidden
            relative

            mb-5
        "

        @click.outside="
            mobileMenuOpen = false
        "
    >
        <button
            type="button"

            @click="
                mobileMenuOpen =
                    !mobileMenuOpen
            "

            class="
                w-full
                min-h-11

                flex
                items-center
                justify-between
                gap-3

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border-strong)]

                rounded-xl

                px-4
                py-3

                text-left

                transition-colors

                hover:bg-[var(--theme-surface-soft)]
            "
        >
            <div
                class="
                    flex
                    items-center
                    gap-3
                    min-w-0
                "
            >
                <div
                    class="
                        shrink-0

                        w-7
                        h-7

                        rounded-lg

                        bg-[var(--theme-primary-soft)]

                        flex
                        items-center
                        justify-center

                        text-[var(--theme-primary)]
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M4 7h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 17h16"/>
                    </svg>
                </div>

                <span
                    class="
                        truncate

                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "

                    x-text="
                        sectionLabel()
                    "
                ></span>
            </div>


            <svg
                class="
                    w-4
                    h-4

                    shrink-0

                    text-[var(--theme-text-muted)]

                    transition-transform
                    duration-150
                "

                :class="
                    mobileMenuOpen
                        ? 'rotate-180'
                        : ''
                "

                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </button>


        {{-- MENÚ DESPLEGABLE --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak

            x-transition:enter="
                transition
                ease-out
                duration-150
            "

            x-transition:enter-start="
                opacity-0
                -translate-y-1
            "

            x-transition:enter-end="
                opacity-100
                translate-y-0
            "

            x-transition:leave="
                transition
                ease-in
                duration-100
            "

            x-transition:leave-start="
                opacity-100
                translate-y-0
            "

            x-transition:leave-end="
                opacity-0
                -translate-y-1
            "

            class="
                absolute
                z-50

                top-full
                left-0
                right-0

                mt-2

                overflow-hidden

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                shadow-lg
            "
        >
            <div
                class="
                    p-1.5
                "
            >
                <template
                    x-for="item in sections"
                    :key="item.key"
                >
                    <button
                        type="button"

                        @click="
                            setSection(
                                item.key
                            )
                        "

                        class="
                            w-full

                            flex
                            items-center
                            justify-between
                            gap-3

                            rounded-lg

                            px-3
                            py-2.5

                            text-left
                            text-sm

                            transition-colors
                        "

                        :class="
                            section === item.key
                                ? `
                                    bg-[var(--theme-primary-soft)]
                                    text-[var(--theme-primary)]
                                    font-medium
                                `
                                : `
                                    text-[var(--theme-text)]

                                    hover:bg-[var(--theme-surface-soft)]
                                `
                        "
                    >
                        <span
                            x-text="
                                item.label
                            "
                        ></span>


                        <svg
                            x-show="
                                section === item.key
                            "

                            x-cloak

                            class="
                                w-4
                                h-4

                                shrink-0
                            "

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="m5 12 4 4L19 7"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </div>


    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <div class="min-w-0">


        {{-- ========================================================
            EQUIPOS
        ======================================================== --}}
        <div
            x-show="
                section === 'equipos'
            "
            x-cloak
        >
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >
                <div
                    class="
                        px-4
                        sm:px-5

                        py-4

                        border-b
                        border-[var(--theme-border)]
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                        x-text="
                            sectionLabel(
                                'equipos'
                            )
                        "
                    ></h2>

                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Administra los tipos de equipo y su configuración.
                    </p>
                </div>

                <div
                    class="
                        px-4
                        sm:px-5

                        py-8
                    "
                >
                    <p
                        class="
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        La configuración de equipos se construirá en esta sección.
                    </p>
                </div>
            </section>
        </div>


        {{-- ========================================================
            MARCAS
        ======================================================== --}}
        <div
            x-show="
                section === 'marcas'
            "
            x-cloak
        >
            <livewire:catalogos.marcas-modelos
                vista="marcas"
                :key="'administracion-marcas'"
            />
        </div>


        {{-- ========================================================
            MODELOS
        ======================================================== --}}
        <div
            x-show="
                section === 'modelos'
            "
            x-cloak
        >
            <livewire:catalogos.marcas-modelos
                vista="modelos"
                :key="'administracion-modelos'"
            />
        </div>


        {{-- ========================================================
            HOTELES / ÁREAS
        ======================================================== --}}
        <div
            x-show="
                section === 'areas'
            "
            x-cloak
        >
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >
                <div
                    class="
                        px-4
                        sm:px-5

                        py-4

                        border-b
                        border-[var(--theme-border)]
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                        x-text="
                            sectionLabel(
                                'areas'
                            )
                        "
                    ></h2>

                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Administra las áreas operativas asociadas a las sedes.
                    </p>
                </div>

                <div
                    class="
                        px-4
                        sm:px-5

                        py-8
                    "
                >
                    <p
                        class="
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Esta sección se conectará con la tabla interna de áreas.
                    </p>
                </div>
            </section>
        </div>


        {{-- ========================================================
            DEPARTAMENTOS
        ======================================================== --}}
        <div
            x-show="
                section === 'departamentos'
            "
            x-cloak
        >
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >
                <div
                    class="
                        px-4
                        sm:px-5

                        py-4

                        border-b
                        border-[var(--theme-border)]
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                        x-text="
                            sectionLabel(
                                'departamentos'
                            )
                        "
                    ></h2>

                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Administra los departamentos asociados a cada área.
                    </p>
                </div>

                <div
                    class="
                        px-4
                        sm:px-5

                        py-8
                    "
                >
                    <p
                        class="
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        La administración de departamentos se construirá aquí.
                    </p>
                </div>
            </section>
        </div>


        {{-- ========================================================
            PLANTILLAS
        ======================================================== --}}
        <div
            x-show="
                section === 'plantillas'
            "
            x-cloak
        >
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >
                <div
                    class="
                        px-4
                        sm:px-5

                        py-4

                        border-b
                        border-[var(--theme-border)]
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Plantillas
                    </h2>

                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Configura la estructura y comportamiento de los registros.
                    </p>
                </div>

                <div
                    class="
                        px-4
                        sm:px-5

                        py-8
                    "
                >
                    <p
                        class="
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Desde aquí se configurarán los nombres y campos utilizados por cada plantilla.
                    </p>
                </div>
            </section>
        </div>


        {{-- ========================================================
            ORGANIZACIÓN
        ======================================================== --}}
        <div
            x-show="
                section === 'organizacion'
            "
            x-cloak
        >
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    overflow-hidden
                "
            >
                <div
                    class="
                        px-4
                        sm:px-5

                        py-4

                        border-b
                        border-[var(--theme-border)]
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Organización
                    </h2>

                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Configuración general de la estructura organizacional.
                    </p>
                </div>

                <div
                    class="
                        px-4
                        sm:px-5

                        py-8
                    "
                >
                    <p
                        class="
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        La configuración organizacional se construirá en esta sección.
                    </p>
                </div>
            </section>
        </div>


    </div>

</div>

@endsection