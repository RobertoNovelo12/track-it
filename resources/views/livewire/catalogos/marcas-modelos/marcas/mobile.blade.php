{{-- ============================================================
    TARJETAS MÓVIL - MARCAS
============================================================ --}}
<div class="md:hidden space-y-3">

    <template
        x-for="
            marca
            in paginatedMarcas
        "

        :key="
            'marca-card-'
            + marca.id
        "
    >
        <article
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-4

                shadow-sm
            "
        >
            <div class="flex items-start gap-3">

                <div class="min-w-0 flex-1">

                    <p
                        class="
                            text-sm
                            font-semibold
                            leading-snug

                            text-[var(--theme-text-strong)]

                            break-words
                        "

                        x-text="
                            marca.nombre
                        "
                    ></p>

                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ID

                        <span
                            x-text="
                                marca.id
                            "
                        ></span>
                    </p>

                </div>


                <div
                    x-data="
                        {
                            open: false
                        }
                    "

                    @click.outside="
                        open = false
                    "

                    class="
                        relative
                        shrink-0
                    "
                >
                    <button
                        type="button"

                        @click="
                            open = !open
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


                    <div
                        x-show="
                            open
                        "

                        x-cloak

                        class="
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
                        <a
                            :href="
                                marca.editUrl
                            "

                            @click="
                                open = false
                            "

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


                        <button
                            type="button"

                            :disabled="
                                togglingMarcaId
                                === marca.id
                            "

                            @click="
                                open = false;

                                toggleMarcaStatus(
                                    marca
                                );
                            "

                            class="
                                w-full

                                block

                                text-left

                                px-4
                                py-2.5

                                text-xs

                                disabled:opacity-50
                            "

                            :class="
                                marca.activo
                                    ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                    : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                            "
                        >
                            <span
                                x-text="
                                    togglingMarcaId
                                    === marca.id
                                        ? 'Actualizando...'
                                        : (
                                            marca.activo
                                                ? 'Desactivar'
                                                : 'Activar'
                                        )
                                "
                            ></span>
                        </button>
                    </div>
                </div>

            </div>


            <div class="mt-3 space-y-2">

                <p
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Descripción:

                    <span
                        class="
                            block
                            truncate

                            text-[var(--theme-text-muted)]
                        "

                        :title="
                            marca.descripcion
                        "

                        x-text="
                            marca.descripcion
                            || '—'
                        "
                    ></span>
                </p>


                <p
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Modelos:

                    <span
                        class="
                            text-[var(--theme-text)]
                        "

                        x-text="
                            marca.totalModelos
                        "
                    ></span>
                </p>


                <p
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Equipos:

                    <span
                        class="
                            text-[var(--theme-text)]
                        "

                        x-text="
                            marca.totalEquipos
                        "
                    ></span>
                </p>


                <div
                    class="
                        flex
                        items-center
                        justify-between

                        gap-3
                    "
                >
                    <span
                        class="
                            inline-flex
                            items-center

                            px-2
                            py-1

                            rounded-full

                            text-[10px]
                            font-medium
                        "

                        :class="
                            marca.activo
                                ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                        "

                        x-text="
                            marca.activo
                                ? 'Activa'
                                : 'Inactiva'
                        "
                    ></span>


                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "

                        x-text="
                            formatDate(
                                marca.fechaCreacion
                            )
                        "
                    ></span>

                </div>

            </div>
        </article>
    </template>


    {{-- SIN RESULTADOS --}}
    <div
        x-show="
            filteredMarcas.length
            === 0
        "

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
        <p
            class="
                text-sm
                text-[var(--theme-text-muted)]
            "
        >
            No se encontraron marcas
        </p>
    </div>

</div>