@extends('layouts.app')

@section('title', 'Administración')

@section('content')

{{-- Marcas y Modelos ya no usan el script Alpine de catalogos.partials.marcas-modelos-alpine: sus tablas se generan en el servidor. --}}

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
            'crumb' => 'Tipos de equipo',
            'label' => 'Equipos',
        ],
        [
            'key' => 'marcas',
            'crumb' => 'Marcas',
            'label' => 'Marcas',
        ],
        [
            'key' => 'modelos',
            'crumb' => 'Modelos',
            'label' => 'Modelos',
        ],
        [
            // Software que se instala en los equipos (tipos con «Incluye programas»).
            'key' => 'programas',
            'crumb' => 'Programas',
            'label' => 'Programas',
        ],
        [
            'key' => 'hoteles',
            'crumb' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_plural'],
            'label' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_plural'],
        ],
        [
            'key' => 'departamentos',
            'crumb' => 'Departamentos',
            'label' => 'Departamentos',
        ],
        [
            'key' => 'ubicaciones',
            'crumb' => 'Ubicaciones',
            'label' => 'Ubicaciones',
        ],
        [
            'key' => 'plantillas',
            'crumb' => 'Plantillas',
            'label' => 'Plantillas',
        ],
        [
            'key' => 'organizacion',
            'crumb' => 'Organización',
            'label' => 'Organización',
        ],
    ];

    /*
     * Estándar solo administra los catálogos de equipo. Hoteles,
     * departamentos, plantillas y organización son exclusivos de Admin.
     */
    $seccionesOrganizacion = ['hoteles', 'departamentos', 'ubicaciones', 'plantillas', 'organizacion'];

    $puedeAdministrar = \Illuminate\Support\Facades\Gate::allows('administrar');

    if (! $puedeAdministrar) {
        $administrationSections = array_values(array_filter(
            $administrationSections,
            fn (array $seccion): bool => ! in_array($seccion['key'], $seccionesOrganizacion, true)
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Carga por sección
    |--------------------------------------------------------------------------
    |
    | Solo la sección abierta se procesa con la página. Las demás se
    | cargan (lazy) la primera vez que el usuario las muestra: al estar
    | ocultas con x-show, su x-intersect no se activa hasta entonces.
    |
    */

    $seccionInicial = collect($administrationSections)
        ->pluck('key')
        ->contains(request()->query('section'))
            ? request()->query('section')
            : 'equipos';

    $diferir = fn (string $seccion): bool => $seccionInicial !== $seccion;
@endphp


<div
    class="
        w-full
        max-w-[1600px]
        mx-auto
    "
    x-data="{
        section: @js($seccionInicial),

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

        sectionCrumb(section = null) {
            const key = section ?? this.section;
            const item = this.sections.find(sectionItem => sectionItem.key === key);

            return item?.crumb ?? item?.label ?? '';
        },
    }"
>


    {{-- La sección activa se agrega a la ruta de la barra superior
        (layouts/partials/encabezado). --}}
    <div
        hidden
        x-effect="$dispatch('encabezado-seccion', { miga: sectionCrumb() })"
    ></div>


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
            <livewire:catalogos.tipos-equipo
                :key="'administracion-tipos-equipo'"
                :lazy="$diferir('equipos')"
            />
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
                :lazy="$diferir('marcas')"
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
                :lazy="$diferir('modelos')"
            />
        </div>


        {{-- ========================================================
            PROGRAMAS (software)
        ======================================================== --}}
        <div x-show="section === 'programas'" x-cloak>
            <livewire:catalogos.programas
                :key="'administracion-programas'"
                :lazy="$diferir('programas')"
            />
        </div>


        @if ($puedeAdministrar)

        {{-- ========================================================
            HOTELES
        ======================================================== --}}
        <div x-show="section === 'hoteles'" x-cloak>
            <livewire:catalogos.hoteles
                :key="'administracion-hoteles'"
                :lazy="$diferir('hoteles')"
            />
        </div>


        {{-- ========================================================
            DEPARTAMENTOS
        ======================================================== --}}
        <div
            x-show="section === 'departamentos'"
            x-cloak
        >
            <livewire:catalogos.departamentos
                :key="'administracion-departamentos'"
                :lazy="$diferir('departamentos')"
            />
        </div>


        {{-- ========================================================
            UBICACIONES (cocinas, clubes, cuartos de comunicaciones…)
        ======================================================== --}}
        <div x-show="section === 'ubicaciones'" x-cloak>
            <livewire:catalogos.ubicaciones
                :key="'administracion-ubicaciones'"
                :lazy="$diferir('ubicaciones')"
            />
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
            @include('catalogos.plantillas.panel')
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
            <livewire:catalogos.organizacion
                :key="'administracion-organizacion'"
                :lazy="$diferir('organizacion')"
            />
        </div>

        @endif

    </div>

</div>

@endsection