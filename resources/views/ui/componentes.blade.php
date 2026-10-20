@extends('layouts.app')

@section('title', 'Guía de componentes')

@section('content')

{{-- ============================================================
    GUÍA DE COMPONENTES DE CARGA (solo Admin)
    ------------------------------------------------------------
    Muestra en vivo los componentes de components/ui para
    revisarlos antes de aplicarlos en las pantallas.
============================================================ --}}
@php
    $tarjeta = 'rounded-[var(--radius-lg)] border border-[var(--theme-border)] bg-[var(--theme-surface)]';
    $cabecera = 'border-b border-[var(--theme-border)] px-4 py-4 sm:px-5';
    $titulo = 'text-sm font-semibold text-[var(--theme-text-strong)]';
    $ayuda = 'mt-1 text-xs text-[var(--theme-text-muted)]';
    $etiqueta = 'mb-2 text-[11px] font-medium uppercase tracking-wide text-[var(--theme-text-muted)]';
@endphp

<x-form.page-header
    title="Guía de componentes"
    :crumbs="[
        ['label' => 'Inicio', 'href' => route('dashboard')],
        ['label' => 'Guía de componentes'],
    ]"
/>

<div class="space-y-5">

    {{-- ========================================================
        1. BOTONES
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="{{ $cabecera }}">
            <h2 class="{{ $titulo }}">Botones con estado</h2>
            <p class="{{ $ayuda }}">
                Pulsa cada botón: muestra el spinner y el texto de la acción, no cambia de ancho y no acepta otro clic mientras procesa.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 p-4 sm:p-5 lg:grid-cols-3">
            @foreach ([
                ['variant' => 'primary', 'texto' => 'Guardar cambios', 'cargando' => 'Guardando…', 'exito' => 'Guardado'],
                ['variant' => 'secondary', 'texto' => 'Crear registro', 'cargando' => 'Creando…', 'exito' => 'Creado'],
                ['variant' => 'danger', 'texto' => 'Eliminar', 'cargando' => 'Eliminando…', 'exito' => 'Eliminado'],
            ] as $demo)
                <div
                    x-data="{
                        cargando: false,
                        listo: false,
                        ejecutar() {
                            if (this.cargando) return;
                            this.cargando = true;
                            setTimeout(() => {
                                this.cargando = false;
                                this.listo = true;
                                setTimeout(() => this.listo = false, 1500);
                            }, 1600);
                        },
                    }"
                >
                    <p class="{{ $etiqueta }}">{{ ucfirst($demo['variant']) }}</p>

                    <x-ui.button
                        :variant="$demo['variant']"
                        x-loading="cargando"
                        x-success="listo"
                        :loading-text="$demo['cargando']"
                        :success-text="$demo['exito']"
                        @click="ejecutar()"
                    >
                        {{ $demo['texto'] }}
                    </x-ui.button>
                </div>
            @endforeach
        </div>
    </section>


    {{-- ========================================================
        2. SPINNER
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="{{ $cabecera }}">
            <h2 class="{{ $titulo }}">Spinner</h2>
            <p class="{{ $ayuda }}">Un solo estilo en tres tamaños. Hereda el color del texto.</p>
        </div>

        <div class="flex flex-wrap items-center gap-8 p-4 text-[var(--theme-primary)] sm:p-5">
            <div class="flex items-center gap-2">
                <x-ui.spinner size="sm" />
                <span class="text-xs text-[var(--theme-text-muted)]">Pequeño · botones</span>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.spinner />
                <span class="text-xs text-[var(--theme-text-muted)]">Mediano · junto a buscadores</span>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.spinner size="lg" />
                <span class="text-xs text-[var(--theme-text-muted)]">Grande · zonas amplias</span>
            </div>
        </div>
    </section>


    {{-- ========================================================
        3. SKELETON
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="{{ $cabecera }}">
            <h2 class="{{ $titulo }}">Skeleton</h2>
            <p class="{{ $ayuda }}">Piezas para dibujar la forma del contenido mientras carga.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 p-4 sm:p-5 md:grid-cols-3">
            {{-- Tarjeta de métrica --}}
            <div>
                <p class="{{ $etiqueta }}">Tarjeta de métrica</p>

                <div class="flex items-center gap-4 rounded-[var(--radius-lg)] border border-[var(--theme-border)] px-4 py-3.5">
                    <x-ui.skeleton class="h-14 w-14 rounded-[var(--radius-lg)]" />

                    <div class="flex-1 space-y-2.5">
                        <x-ui.skeleton class="h-7 w-16" />
                        <x-ui.skeleton class="h-3 w-28" />
                    </div>
                </div>
            </div>

            {{-- Perfil --}}
            <div>
                <p class="{{ $etiqueta }}">Perfil</p>

                <div class="flex items-center gap-4 rounded-[var(--radius-lg)] border border-[var(--theme-border)] px-4 py-3.5">
                    <x-ui.skeleton variant="circle" class="h-12 w-12" />

                    <div class="flex-1 space-y-2.5">
                        <div class="flex items-center gap-2">
                            <x-ui.skeleton class="h-4 w-32" />
                            <x-ui.skeleton variant="pill" class="h-4 w-12" />
                        </div>
                        <x-ui.skeleton class="h-3 w-20" />
                    </div>
                </div>
            </div>

            {{-- Gráfica --}}
            <div>
                <p class="{{ $etiqueta }}">Gráfica</p>
                <x-ui.skeleton variant="block" class="h-[86px]" />
            </div>
        </div>
    </section>


    {{-- ========================================================
        4. SKELETON DE TABLA
    ======================================================== --}}
    <section class="space-y-3">
        <div>
            <h2 class="{{ $titulo }}">Skeleton de tabla</h2>
            <p class="{{ $ayuda }}">Encabezados reales, filas con la misma altura que la tabla final y paginación.</p>
        </div>

        <x-ui.skeleton-table
            :columns="[
                ['label' => 'ID Equipo', 'width' => '130px'],
                ['label' => 'Nombre de equipo'],
                ['label' => 'Marca', 'width' => '120px'],
                ['label' => 'Modelo'],
                ['label' => 'Estado', 'width' => '130px', 'type' => 'status'],
            ]"
            :rows="5"
        />
    </section>


    {{-- ========================================================
        5. ESTADOS VACÍOS Y DE ERROR
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="{{ $cabecera }}">
            <h2 class="{{ $titulo }}">Estados vacíos y de error</h2>
            <p class="{{ $ayuda }}">Lo que se muestra cuando no hay datos, no hay coincidencias o falla la carga.</p>
        </div>

        <div class="grid grid-cols-1 divide-y divide-[var(--theme-border)] md:grid-cols-3 md:divide-x md:divide-y-0">
            <x-ui.empty-state
                compact
                title="Todavía no hay equipos"
                description="Cuando registres un equipo aparecerá aquí."
            />

            <x-ui.empty-state variant="search" compact />

            <x-ui.empty-state variant="error" compact>
                <x-slot:actions>
                    <x-ui.button
                        variant="secondary"
                        size="sm"
                        x-data="{ cargando: false }"
                        x-loading="cargando"
                        loading-text="Reintentando…"
                        @click="cargando = true; setTimeout(() => cargando = false, 1500)"
                    >
                        Reintentar
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        </div>
    </section>


    {{-- ========================================================
        6. AVISOS
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="{{ $cabecera }}">
            <h2 class="{{ $titulo }}">Avisos (toast)</h2>
            <p class="{{ $ayuda }}">
                Aparecen abajo a la derecha. Se pausan al pasar el cursor; los errores duran más para poder leerlos.
            </p>
        </div>

        <div class="flex flex-wrap gap-3 p-4 sm:p-5">
            <x-ui.button variant="secondary" size="sm" @click="window.trackitToast('success', 'Los cambios se guardaron correctamente.')">
                Éxito
            </x-ui.button>

            <x-ui.button variant="secondary" size="sm" @click="window.trackitToast('error', 'No se pudo conectar con el servidor. Inténtalo de nuevo.', 'No se pudo guardar')">
                Error
            </x-ui.button>

            <x-ui.button variant="secondary" size="sm" @click="window.trackitToast('warning', 'El equipo tiene un mantenimiento vencido.')">
                Advertencia
            </x-ui.button>

            <x-ui.button variant="secondary" size="sm" @click="window.trackitToast('info', 'Hay una nueva versión del reporte disponible.')">
                Información
            </x-ui.button>
        </div>
    </section>


    {{-- ========================================================
        7. MOVIMIENTO
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="{{ $cabecera }}">
            <h2 class="{{ $titulo }}">Ritmo de animaciones</h2>
            <p class="{{ $ayuda }}">
                Valores globales: rápido 150 ms (hover y clic), base 180 ms (menús y desplegables), modal 220 ms.
                Si tu sistema tiene activado «Reducir movimiento», las animaciones decorativas se desactivan.
            </p>
        </div>

        <div class="flex flex-wrap gap-3 p-4 sm:p-5">
            @foreach (['fast' => 'Rápido', 'base' => 'Base', 'modal' => 'Modal'] as $clave => $nombre)
                <div
                    x-data="{ abierto: false }"
                    class="relative"
                >
                    <x-ui.button variant="secondary" size="sm" @click="abierto = ! abierto">
                        {{ $nombre }}
                    </x-ui.button>

                    <div
                        x-show="abierto"
                        x-cloak
                        @click.outside="abierto = false"
                        style="transition-duration: var(--motion-{{ $clave }}); transition-timing-function: var(--ease-out);"
                        x-transition:enter="transition"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute left-0 top-full z-20 mt-2 w-48 rounded-[var(--radius-md)] border border-[var(--theme-border)] bg-[var(--theme-surface)] p-3 text-xs text-[var(--theme-text-muted)] theme-shadow-xl"
                    >
                        Desplegable con duración «{{ $nombre }}».
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>

@endsection
