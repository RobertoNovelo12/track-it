<div>

    <form class="space-y-4 sm:space-y-6">

        {{-- ============================================================
            INFORMACIÓN GENERAL
        ============================================================ --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-4
                sm:p-6
            "
        >
            <div
                class="
                    flex
                    flex-col

                    gap-1.5

                    sm:flex-row
                    sm:items-center
                    sm:justify-between

                    mb-5
                "
            >
                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Información general
                </h2>

                <div
                    class="
                        flex
                        items-center
                        gap-2

                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    <span>
                        Código de inventario:
                    </span>

                    <span
                        class="
                            inline-flex
                            items-center

                            rounded-md

                            bg-[var(--theme-primary-soft)]

                            px-2.5
                            py-1

                            font-semibold
                            text-[var(--theme-primary)]
                        "
                    >
                        Preparando...
                    </span>
                </div>
            </div>


            <div
                class="
                    grid
                    grid-cols-1

                    md:grid-cols-2
                    xl:grid-cols-3

                    gap-4
                "
            >

                {{-- NOMBRE --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Nombre del equipo *
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ej. Laptop del área de RRHH"

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- HOST --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Host
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ej. 030-123-456"

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- ESTADO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Estado *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Cargando catálogos...
                        </span>

                        <div
                            class="
                                shrink-0

                                flex
                                items-center
                                justify-center

                                pr-3

                                text-[var(--theme-primary)]
                            "
                            aria-label="Cargando catálogos"
                        >
                            <svg
                                class="
                                    w-4
                                    h-4

                                    animate-spin
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"

                                    stroke="currentColor"
                                    stroke-width="2"

                                    opacity="0.25"
                                />

                                <path
                                    d="M21 12a9 9 0 0 0-9-9"

                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </div>
                    </div>

                </div>


                {{-- TIPO DE EQUIPO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Tipo de equipo *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Cargando tipos...
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>


                {{-- MARCA --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Marca *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Cargando marcas...
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>


                {{-- MODELO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Modelo
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Elige marca y tipo primero
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>


                {{-- MAC --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Dirección MAC
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ej. 4C:CC:6A:18:28:7D"

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- FECHA DE COMPRA --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Fecha de compra
                    </label>

                    <input
                        type="date"
                        disabled

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- FACTURA --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Número de factura *
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ingresar número de factura"

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- SERIE --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Número de serie *
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ingresar número de serie"

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- PROVEEDOR --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Proveedor
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Cargando proveedores...
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>

            </div>
        </section>


        {{-- ============================================================
            INFORMACIÓN ADICIONAL
        ============================================================ --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-4
                sm:p-6
            "
        >
            <h2
                class="
                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]

                    mb-5
                "
            >
                Información adicional
            </h2>


            <div
                class="
                    grid
                    grid-cols-1

                    md:grid-cols-2
                    xl:grid-cols-3

                    gap-4

                    mb-5
                "
            >

                {{-- PROPIETARIO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Propietario / Responsable
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ej. María López RRHH"

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                    <p
                        class="
                            mt-1.5

                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Si lo dejas vacío, el equipo queda como stock disponible.
                    </p>

                </div>


                {{-- ESTATUS --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Estatus
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Cargando condiciones...
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>


                {{-- GARANTÍA --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Vigencia de garantía
                    </label>

                    <input
                        type="date"
                        disabled

                        class="
                            w-full
                            min-w-0

                            text-sm
                            text-[var(--theme-text)]

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            rounded-md

                            px-3
                            py-2.5

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- ÁREA --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Área *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Cargando áreas...
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>


                {{-- DEPARTAMENTO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block

                            text-xs
                            text-[var(--theme-text-muted)]

                            mb-1.5
                        "
                    >
                        Departamento
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full
                            h-[42px]

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <span
                            class="
                                min-w-0
                                flex-1

                                px-3

                                text-sm
                                text-[var(--theme-text-muted)]

                                truncate
                            "
                        >
                            Elige un área primero
                        </span>

                        <svg
                            class="
                                shrink-0

                                w-4
                                h-4

                                mr-3

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>

                </div>

            </div>


            {{-- COMENTARIOS --}}
            <div>

                <label
                    class="
                        block

                        text-xs
                        text-[var(--theme-text-muted)]

                        mb-1.5
                    "
                >
                    Comentarios
                </label>

                <textarea
                    rows="4"
                    disabled

                    placeholder="Escribe algún comentario adicional..."

                    class="
                        w-full
                        min-w-0

                        text-sm
                        text-[var(--theme-text)]

                        bg-[var(--theme-surface)]

                        border
                        border-[var(--theme-border-strong)]

                        rounded-md

                        px-3
                        py-2.5

                        placeholder:text-[var(--theme-text-muted)]

                        resize-none

                        disabled:opacity-100
                        disabled:cursor-default
                    "
                ></textarea>

            </div>
        </section>


        {{-- ============================================================
            ACCIONES
        ============================================================ --}}
        <div
            class="
                flex
                flex-col-reverse

                sm:flex-row
                sm:items-center
                sm:justify-end

                gap-3

                pt-1
                pb-2
            "
        >

            <a
                href="{{ route('equipos.index') }}"
                wire:navigate
                class="
                    w-full
                    sm:w-auto

                    h-11

                    flex
                    items-center
                    justify-center

                    border
                    border-[var(--theme-border-strong)]

                    rounded-lg

                    px-5

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    bg-[var(--theme-surface)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text)]

                    transition-colors
                "
            >
                Cancelar
            </a>


            <button
                type="button"
                disabled

                class="
                    w-full
                    sm:w-auto

                    h-11

                    flex
                    items-center
                    justify-center
                    gap-2

                    bg-[var(--theme-primary)]

                    rounded-lg

                    px-5

                    text-sm
                    font-medium
                    text-white

                    opacity-70
                    cursor-default
                "
            >
                <svg
                    class="
                        w-4
                        h-4

                        animate-spin
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"

                        stroke="currentColor"
                        stroke-width="2"

                        opacity="0.25"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"

                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>

                <span>
                    Cargando...
                </span>
            </button>

        </div>

    </form>

</div>