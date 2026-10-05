{{-- ============================================================
    TARJETAS - MÓVIL
============================================================ --}}
<div class="md:hidden space-y-3">

    @forelse ($equipos as $equipo)

        <article
            x-data="{ selected: false }"

            @click="
                selected = !selected
            "

            wire:key="
                equipo-card-{{ $equipo->id_equipo }}
            "

            class="
                bg-[var(--theme-surface)]

                border

                rounded-xl

                p-4

                shadow-sm

                transition-all

                cursor-pointer
                select-none
            "

            :class="
                selected
                    ? 'border-[var(--theme-primary-border-strong)] ring-1 ring-[var(--theme-primary-soft)]'
                    : 'border-[var(--theme-border)]'
            "
        >

            {{-- =================================================
                CABECERA
            ================================================= --}}
            <div class="flex items-start gap-3">

                {{-- =============================================
                    SELECTOR VISUAL
                ============================================= --}}
                <button
                    type="button"

                    @click.stop="
                        selected = !selected
                    "

                    class="
                        shrink-0

                        mt-0.5

                        w-5
                        h-5

                        rounded-full

                        border-2

                        flex
                        items-center
                        justify-center

                        transition-all
                    "

                    :class="
                        selected
                            ? 'border-[var(--theme-primary)] bg-[var(--theme-primary)]'
                            : 'border-[var(--theme-border-strong)] bg-[var(--theme-surface)]'
                    "

                    aria-label="Seleccionar equipo"
                >
                    <svg
                        x-show="selected"
                        x-cloak

                        class="w-3 h-3 text-white"

                        viewBox="0 0 24 24"

                        fill="none"
                        stroke="currentColor"
                        stroke-width="3"
                    >
                        <path d="M5 12l4 4L19 7"/>
                    </svg>
                </button>


                {{-- =============================================
                    NOMBRE / INVENTARIO
                ============================================= --}}
                <div class="min-w-0 flex-1">

                    <p
                        class="
                            text-sm
                            font-semibold
                            leading-snug

                            text-[var(--theme-text-strong)]

                            break-words
                        "
                    >
                        {{ $equipo->tipo_equipo_nombre ?? 'Equipo' }}

                        @if ($equipo->marca_nombre)
                            {{ $equipo->marca_nombre }}
                        @endif

                        @if ($equipo->modelo_nombre)
                            {{ $equipo->modelo_nombre }}
                        @endif
                    </p>


                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ $equipo->codigo_inventario }}
                    </p>

                </div>


                {{-- =============================================
                    MÁS ACCIONES
                ============================================= --}}
                <div class="relative shrink-0">

                    <button
                        type="button"

                        onclick="
                            toggleRowMenu(
                                event,
                                'menu-m-{{ $equipo->id_equipo }}'
                            )
                        "

                        class="
                            w-8
                            h-8

                            flex
                            items-center
                            justify-center

                            rounded-md

                            text-[var(--theme-text)]

                            hover:bg-[var(--theme-surface-soft)]

                            transition-colors
                        "

                        aria-label="Más acciones"
                    >
                        <svg
                            class="w-5 h-5"

                            viewBox="0 0 24 24"

                            fill="currentColor"
                        >
                            <circle cx="12" cy="5" r="1.5"/>
                            <circle cx="12" cy="12" r="1.5"/>
                            <circle cx="12" cy="19" r="1.5"/>
                        </svg>
                    </button>


                    {{-- =========================================
                        MENÚ
                    ========================================= --}}
                    <div
                        id="menu-m-{{ $equipo->id_equipo }}"

                        class="
                            row-menu
                            hidden

                            absolute

                            right-0
                            top-9

                            w-40

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border)]

                            rounded-lg

                            shadow-lg

                            z-20

                            overflow-hidden

                            text-left
                        "
                    >

                        {{-- VER DETALLE --}}
                        <a
                            href="{{
                                Route::has('equipos.show')
                                    ? route(
                                        'equipos.show',
                                        $equipo->id_equipo
                                    )
                                    : '#'
                            }}"

                            class="
                                block

                                px-4
                                py-2.5

                                text-xs
                                text-[var(--theme-text)]

                                hover:bg-[var(--theme-surface-soft)]
                            "
                        >
                            Ver detalle
                        </a>


                        {{-- EDITAR --}}
                        <a
                            href="{{
                                Route::has('equipos.edit')
                                    ? route(
                                        'equipos.edit',
                                        $equipo->id_equipo
                                    )
                                    : '#'
                            }}"

                            class="
                                block

                                px-4
                                py-2.5

                                text-xs
                                text-[var(--theme-text)]

                                hover:bg-[var(--theme-surface-soft)]
                            "
                        >
                            Editar
                        </a>


                        {{-- DAR DE BAJA --}}
                        <button
                            type="button"

                            class="
                                w-full

                                block

                                text-left

                                px-4
                                py-2.5

                                text-xs

                                text-[var(--theme-danger)]

                                hover:bg-[var(--theme-danger-soft)]

                                cursor-default
                            "
                        >
                            Dar de baja
                        </button>

                    </div>

                </div>

            </div>


            {{-- =================================================
                INFORMACIÓN
            ================================================= --}}
            <div class="mt-3 space-y-2">

                {{-- NÚMERO DE SERIE --}}
                <p class="text-xs text-[var(--theme-text-muted)]">

                    <span class="text-[var(--theme-text-muted)]">
                        SN:
                    </span>

                    <span class="break-all">
                        {{ $equipo->numero_serie ?? 'NA' }}
                    </span>

                </p>


                {{-- SERVICE TAG --}}
                <p class="text-xs text-[var(--theme-text-muted)]">

                    <span class="text-[var(--theme-text-muted)]">
                        Service Tag:
                    </span>

                    <span class="break-all">
                        {{ $equipo->service_tag ?? 'NA' }}
                    </span>

                </p>


                {{-- DIRECCIÓN IP --}}
                @if ($equipo->direccion_ip)

                    <p class="text-xs text-[var(--theme-text-muted)]">

                        <span class="text-[var(--theme-text-muted)]">
                            IP:
                        </span>

                        {{ $equipo->direccion_ip }}

                    </p>

                @endif


                {{-- ESTADO --}}
                <div class="flex items-center gap-2">

                    <span class="text-xs text-[var(--theme-text-muted)]">
                        Estado:
                    </span>

                    <x-status-badge
                        :status="
                            $equipo->estado_nombre
                            ?? 'Sin estado'
                        "
                    />

                </div>


                {{-- ÁREA --}}
                <p class="text-xs text-[var(--theme-text-muted)]">

                    <span class="text-[var(--theme-text-muted)]">
                        Área:
                    </span>

                    {{
                        $equipo->ubicacion_organizacional
                        ?? '—'
                    }}

                </p>

            </div>

        </article>

    @empty

        {{-- =====================================================
            SIN RESULTADOS
        ===================================================== --}}
        <div
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                px-5
                py-12

                text-center
            "
        >

            <svg
                class="
                    w-9
                    h-9

                    mx-auto
                    mb-3

                    text-[var(--theme-text-muted)]
                "

                viewBox="0 0 24 24"

                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M3 6h12"/>
                <path d="M3 11h9"/>
                <path d="M3 16h6"/>
                <circle cx="17" cy="16" r="3"/>
                <path d="M19.5 18.5L22 21"/>
            </svg>


            <p class="text-sm text-[var(--theme-text-muted)]">
                No se encontraron resultados
            </p>

        </div>

    @endforelse

</div>