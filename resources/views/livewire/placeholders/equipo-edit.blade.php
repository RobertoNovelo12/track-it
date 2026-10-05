<div>

    {{-- ============================================================
        ACCIONES SUPERIORES
    ============================================================ --}}
    <div
        class="
            flex
            flex-col
            sm:flex-row
            sm:items-center
            sm:justify-end
            gap-3
            mb-4
        "
    >
        <button
            type="button"
            disabled
            class="
                w-full
                sm:w-auto
                h-10
                flex
                items-center
                justify-center
                gap-2
                border
                border-[var(--theme-border-strong)]
                rounded-lg
                bg-[var(--theme-surface)]
                px-4
                text-sm
                font-medium
                text-[var(--theme-text-muted)]
                opacity-70
                cursor-default
            "
        >
            <svg
                class="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            >
                <path d="M12 3v12"/>
                <path d="M7 10l5 5 5-5"/>
                <path d="M4 21h16"/>
            </svg>

            Exportar Alta
        </button>

        <button
            type="button"
            disabled
            class="
                w-full
                sm:w-auto
                h-10
                flex
                items-center
                justify-center
                gap-2
                border
                border-[var(--theme-danger)]
                rounded-lg
                bg-[var(--theme-danger-soft)]
                px-4
                text-sm
                font-medium
                text-[var(--theme-danger)]
                opacity-70
                cursor-default
            "
        >
            <svg
                class="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <rect
                    x="5"
                    y="5"
                    width="14"
                    height="14"
                    rx="1.5"
                />

                <path d="M9 9l6 6"/>
                <path d="M15 9l-6 6"/>
            </svg>

            Dar de baja
        </button>
    </div>


    <div class="space-y-4 sm:space-y-6">

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
                    gap-2
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
                            gap-2
                            rounded-md
                            bg-[var(--theme-primary-soft)]
                            px-2.5
                            py-1
                            font-semibold
                            text-[var(--theme-primary)]
                        "
                    >
                        <svg
                            class="w-3.5 h-3.5 animate-spin"
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

                        Cargando...
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
                        placeholder="Cargando información..."
                        class="
                            w-full
                            min-w-0
                            text-sm
                            text-[var(--theme-text-muted)]
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
                        placeholder="Cargando información..."
                        class="
                            w-full
                            min-w-0
                            text-sm
                            text-[var(--theme-text-muted)]
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando estado...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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


                {{-- TIPO --}}
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando tipo...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando marca...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando modelo...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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


                {{-- CÓDIGO --}}
                <div class="min-w-0">
                    <label
                        class="
                            block
                            text-xs
                            text-[var(--theme-text-muted)]
                            mb-1.5
                        "
                    >
                        Código de inventario
                    </label>

                    <div class="relative">
                        <input
                            type="text"
                            disabled
                            placeholder="Cargando..."
                            class="
                                w-full
                                min-w-0
                                text-sm
                                text-[var(--theme-text-muted)]
                                border
                                border-[var(--theme-border)]
                                rounded-md
                                px-3
                                py-2.5
                                pr-9
                                bg-[var(--theme-surface-soft)]
                                placeholder:text-[var(--theme-text-muted)]
                                cursor-not-allowed
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-3
                                top-1/2
                                -translate-y-1/2
                                w-4
                                h-4
                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <rect
                                x="6"
                                y="10"
                                width="12"
                                height="9"
                                rx="1.5"
                            />

                            <path d="M8 10V7a4 4 0 018 0v3"/>
                        </svg>
                    </div>
                </div>


                {{-- FECHA COMPRA --}}
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
                        placeholder="Cargando información..."
                        class="
                            w-full
                            min-w-0
                            text-sm
                            text-[var(--theme-text-muted)]
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
                        placeholder="Cargando información..."
                        class="
                            w-full
                            min-w-0
                            text-sm
                            text-[var(--theme-text-muted)]
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando condición...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando proveedor...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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
                        placeholder="Cargando información..."
                        class="
                            w-full
                            min-w-0
                            text-sm
                            text-[var(--theme-text-muted)]
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

            </div>
        </section>


        {{-- ============================================================
            DETALLES DEL EQUIPO
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
                    items-center
                    gap-2
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
                    Detalles del equipo
                </h2>

                <svg
                    class="
                        w-4
                        h-4
                        animate-spin
                        text-[var(--theme-primary)]
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

            <p
                class="
                    text-sm
                    text-[var(--theme-text-muted)]
                "
            >
                Cargando información específica del equipo...
            </p>
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

                {{-- RESPONSABLE --}}
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
                        placeholder="Cargando responsable..."
                        class="
                            w-full
                            min-w-0
                            text-sm
                            text-[var(--theme-text-muted)]
                            border
                            border-[var(--theme-border)]
                            rounded-md
                            px-3
                            py-2.5
                            bg-[var(--theme-surface-soft)]
                            placeholder:text-[var(--theme-text-muted)]
                            cursor-not-allowed
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
                        El responsable se modifica desde Asignación / Movimientos.
                    </p>
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando área...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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
                            flex
                            items-center
                            justify-between
                            h-[42px]
                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]
                            px-3
                        "
                    >
                        <span
                            class="
                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Cargando departamento...
                        </span>

                        <svg
                            class="
                                w-4
                                h-4
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
                    placeholder="Cargando comentarios..."
                    class="
                        w-full
                        min-w-0
                        text-sm
                        text-[var(--theme-text-muted)]
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

    </div>

</div>