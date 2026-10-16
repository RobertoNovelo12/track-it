{{-- ============================================================
    MODAL DE OPCIONES
============================================================ --}}
<div
    x-show="
        optionsModalOpen
    "

    x-cloak

    @keydown.escape.window="
        optionsModalOpen
            && closeOptionsModal()
    "

    class="
        fixed
        inset-0
        z-[180]

        flex
        items-center
        justify-center

        p-4
    "
>

    {{-- ========================================================
        FONDO
    ======================================================== --}}
    <button
        type="button"

        @click="
            closeOptionsModal()
        "

        class="
            absolute
            inset-0

            w-full
            h-full

            bg-[var(--theme-overlay)]
        "

        aria-label="Cerrar"
    ></button>


    {{-- ========================================================
        CONTENIDO
    ======================================================== --}}
    <section
        @click.stop

        x-transition:enter="
            transition
            ease-out
            duration-150
        "

        x-transition:enter-start="
            opacity-0
            scale-[0.98]
            translate-y-1
        "

        x-transition:enter-end="
            opacity-100
            scale-100
            translate-y-0
        "

        x-transition:leave="
            transition
            ease-in
            duration-100
        "

        x-transition:leave-start="
            opacity-100
            scale-100
        "

        x-transition:leave-end="
            opacity-0
            scale-[0.98]
        "

        class="
            relative
            z-10

            w-full
            max-w-xl

            max-h-[82vh]

            overflow-hidden

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]

            theme-shadow-xl
        "
    >

        {{-- ====================================================
            CABECERA
        ==================================================== --}}
        <div
            class="
                px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                justify-between

                gap-4
            "
        >
            <div
                class="
                    min-w-0
                    flex-1
                "
            >
                <h3
                    x-text="
                        optionsModalTitle
                        || 'Opciones de la lista'
                    "

                    class="
                        truncate

                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                ></h3>


                <p
                    x-text="
                        optionsModalSource
                    "

                    class="
                        mt-1

                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                ></p>
            </div>


            <button
                type="button"

                @click="
                    closeOptionsModal()
                "

                class="
                    w-9
                    h-9

                    shrink-0

                    rounded-md

                    flex
                    items-center
                    justify-center

                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text-strong)]

                    transition-colors
                "

                aria-label="Cerrar"
            >
                <svg
                    class="
                        w-4
                        h-4
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>
        </div>


        {{-- ====================================================
            LISTA PERSONALIZADA
        ==================================================== --}}
        <div
            x-show="
                optionsModalEditable
            "

            x-cloak
        >

            {{-- =================================================
                AGREGAR OPCIÓN
            ================================================= --}}
            <div
                class="
                    px-5
                    py-4

                    border-b
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface-soft)]
                "
            >
                <div
                    class="
                        flex
                        flex-col

                        sm:flex-row
                        sm:items-center

                        gap-2
                    "
                >
                    <input
                        type="text"

                        x-model="
                            newOptionName
                        "

                        @keydown.enter.prevent="
                            addOption()
                        "

                        placeholder="Escribe una nueva opción..."

                        maxlength="150"

                        class="
                            flex-1
                            h-10

                            px-3

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            focus:outline-none
                            focus:border-[var(--theme-primary)]

                            focus:ring-2
                            focus:ring-[var(--theme-primary-soft)]

                            transition-colors
                        "
                    >


                    <button
                        type="button"

                        @click="
                            addOption()
                        "

                        :disabled="
                            newOptionName.trim() === ''
                        "

                        class="
                            h-10

                            px-4

                            shrink-0

                            inline-flex
                            items-center
                            justify-center
                            gap-1.5

                            rounded-md

                            bg-[var(--theme-primary)]
                            text-white

                            text-xs
                            font-medium

                            hover:bg-[var(--theme-primary-hover)]

                            disabled:opacity-40
                            disabled:cursor-not-allowed

                            transition-colors
                        "
                    >
                        <svg
                            class="
                                w-3.5
                                h-3.5
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        >
                            <path d="M12 5v14"/>
                            <path d="M5 12h14"/>
                        </svg>

                        Agregar
                    </button>
                </div>


                <p
                    class="
                        mt-2

                        text-[10px]
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Estas opciones pertenecen solamente a este campo y a este tipo de equipo.
                </p>
            </div>


            {{-- =================================================
                OPCIONES EDITABLES
            ================================================= --}}
            <div
                class="
                    p-4

                    max-h-[48vh]
                    overflow-y-auto
                "
            >
                <div
                    x-show="
                        optionsModalItems.length > 0
                    "

                    class="
                        space-y-2
                    "
                >
                    <template
                        x-for="
                            (option, index)
                            in optionsModalItems
                        "

                        :key="
                            option.id_opcion
                                ? 'option-' + option.id_opcion
                                : 'new-option-' + index
                        "
                    >
                        <div
                            class="
                                flex
                                items-center

                                gap-2

                                p-2

                                rounded-lg

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface)]
                            "
                        >

                            {{-- =============================================
                                POSICIÓN
                            ============================================= --}}
                            <div
                                class="
                                    w-7

                                    shrink-0

                                    text-center

                                    text-[10px]
                                    font-medium
                                    text-[var(--theme-text-muted)]
                                "

                                x-text="
                                    index + 1
                                "
                            ></div>


                            {{-- =============================================
                                ORDEN
                            ============================================= --}}
                            <div
                                class="
                                    flex
                                    flex-col

                                    shrink-0
                                "
                            >
                                <button
                                    type="button"

                                    @click="
                                        moveOption(
                                            index,
                                            -1
                                        )
                                    "

                                    :disabled="
                                        index === 0
                                    "

                                    class="
                                        w-7
                                        h-6

                                        flex
                                        items-center
                                        justify-center

                                        rounded

                                        text-[var(--theme-text-muted)]

                                        hover:bg-[var(--theme-surface-soft)]
                                        hover:text-[var(--theme-text-strong)]

                                        disabled:opacity-20
                                        disabled:cursor-not-allowed

                                        transition-colors
                                    "

                                    title="Subir"
                                >
                                    <svg
                                        class="
                                            w-3.5
                                            h-3.5
                                        "
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M6 15l6-6 6 6"/>
                                    </svg>
                                </button>


                                <button
                                    type="button"

                                    @click="
                                        moveOption(
                                            index,
                                            1
                                        )
                                    "

                                    :disabled="
                                        index
                                        ===
                                        optionsModalItems.length - 1
                                    "

                                    class="
                                        w-7
                                        h-6

                                        flex
                                        items-center
                                        justify-center

                                        rounded

                                        text-[var(--theme-text-muted)]

                                        hover:bg-[var(--theme-surface-soft)]
                                        hover:text-[var(--theme-text-strong)]

                                        disabled:opacity-20
                                        disabled:cursor-not-allowed

                                        transition-colors
                                    "

                                    title="Bajar"
                                >
                                    <svg
                                        class="
                                            w-3.5
                                            h-3.5
                                        "
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M6 9l6 6 6-6"/>
                                    </svg>
                                </button>
                            </div>


                            {{-- =============================================
                                NOMBRE
                            ============================================= --}}
                            <input
                                type="text"

                                x-model="
                                    option.nombre
                                "

                                @input="
                                    syncOptionsToField();
                                    markChanged();
                                "

                                maxlength="150"

                                class="
                                    flex-1
                                    min-w-0
                                    h-10

                                    px-3

                                    rounded-md

                                    border
                                    border-[var(--theme-border)]

                                    bg-[var(--theme-surface-soft)]

                                    text-sm
                                    text-[var(--theme-text)]

                                    focus:outline-none
                                    focus:border-[var(--theme-primary)]

                                    focus:ring-2
                                    focus:ring-[var(--theme-primary-soft)]

                                    transition-colors
                                "
                            >


                            {{-- =============================================
                                ELIMINAR
                            ============================================= --}}
                            <button
                                type="button"

                                @click="
                                    removeOption(
                                        index
                                    )
                                "

                                class="
                                    w-9
                                    h-9

                                    shrink-0

                                    rounded-md

                                    flex
                                    items-center
                                    justify-center

                                    text-[var(--theme-text-muted)]

                                    hover:bg-[var(--theme-surface-soft)]
                                    hover:text-[var(--theme-danger)]

                                    transition-colors
                                "

                                title="Eliminar opción"
                                aria-label="Eliminar opción"
                            >
                                <svg
                                    class="
                                        w-4
                                        h-4
                                    "
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                >
                                    <path d="M6 6l12 12"/>
                                    <path d="M18 6L6 18"/>
                                </svg>
                            </button>

                        </div>
                    </template>
                </div>


                {{-- =================================================
                    LISTA VACÍA
                ================================================= --}}
                <div
                    x-show="
                        optionsModalItems.length === 0
                    "

                    x-cloak

                    class="
                        py-12

                        text-center
                    "
                >
                    <div
                        class="
                            w-10
                            h-10

                            mx-auto

                            rounded-full

                            flex
                            items-center
                            justify-center

                            bg-[var(--theme-surface-soft)]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        <svg
                            class="
                                w-4
                                h-4
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        >
                            <path d="M12 5v14"/>
                            <path d="M5 12h14"/>
                        </svg>
                    </div>


                    <p
                        class="
                            mt-3

                            text-sm
                            font-medium
                            text-[var(--theme-text-strong)]
                        "
                    >
                        La lista está vacía
                    </p>


                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Agrega la primera opción en la parte superior.
                    </p>
                </div>

            </div>

        </div>


        {{-- ====================================================
            LISTA DEL SISTEMA
        ==================================================== --}}
        <div
            x-show="
                ! optionsModalEditable
            "

            x-cloak
        >

            {{-- =================================================
                AVISO
            ================================================= --}}
            <div
                class="
                    px-5
                    py-3

                    border-b
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface-soft)]
                "
            >
                <p
                    class="
                        text-[10px]
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Esta lista utiliza valores compartidos por el sistema.

                    Para evitar modificar otros módulos desde este formulario,
                    aquí solamente se muestran sus opciones.
                </p>
            </div>


            {{-- =================================================
                CONTENIDO
            ================================================= --}}
            <div
                class="
                    p-4

                    max-h-[52vh]
                    overflow-y-auto
                "
            >

                {{-- =============================================
                    CARGANDO
                ============================================= --}}
                <div
                    x-show="
                        optionsModalLoading
                    "

                    x-cloak

                    class="
                        py-12

                        text-center
                    "
                >
                    <svg
                        class="
                            w-6
                            h-6

                            mx-auto

                            animate-spin

                            text-[var(--theme-primary)]
                        "

                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"

                            stroke="currentColor"
                            stroke-width="3"

                            opacity=".25"
                        />

                        <path
                            d="M21 12a9 9 0 00-9-9"

                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                        />
                    </svg>


                    <p
                        class="
                            mt-3

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Cargando opciones...
                    </p>
                </div>


                {{-- =============================================
                    OPCIONES
                ============================================= --}}
                <div
                    x-show="
                        ! optionsModalLoading
                        &&
                        optionsModalItems.length > 0
                    "

                    x-cloak

                    class="
                        space-y-2
                    "
                >
                    <template
                        x-for="
                            (option, index)
                            in optionsModalItems
                        "

                        :key="
                            'system-option-'
                            + index
                            + '-'
                            + (
                                option.nombre
                                ?? ''
                            )
                        "
                    >
                        <div
                            class="
                                flex
                                items-center
                                gap-3

                                px-4
                                py-3

                                rounded-lg

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface-soft)]
                            "
                        >
                            <div
                                class="
                                    w-6

                                    shrink-0

                                    text-center

                                    text-[10px]
                                    font-medium
                                    text-[var(--theme-text-muted)]
                                "

                                x-text="
                                    index + 1
                                "
                            ></div>


                            <p
                                x-text="
                                    option.nombre
                                    ?? ''
                                "

                                class="
                                    min-w-0
                                    flex-1

                                    text-sm
                                    text-[var(--theme-text)]
                                "
                            ></p>
                        </div>
                    </template>
                </div>


                {{-- =============================================
                    SIN OPCIONES
                ============================================= --}}
                <div
                    x-show="
                        ! optionsModalLoading
                        &&
                        optionsModalItems.length === 0
                    "

                    x-cloak

                    class="
                        py-12

                        text-center
                    "
                >
                    <p
                        class="
                            text-sm
                            font-medium
                            text-[var(--theme-text-strong)]
                        "
                    >
                        No hay opciones disponibles
                    </p>


                    <p
                        class="
                            mt-1

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Esta lista no contiene valores activos actualmente.
                    </p>
                </div>

            </div>

        </div>


        {{-- ====================================================
            PIE
        ==================================================== --}}
        <div
            class="
                px-5
                py-4

                border-t
                border-[var(--theme-border)]

                flex
                items-center
                justify-between

                gap-3
            "
        >
            <p
                class="
                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                <span
                    x-text="
                        optionsModalItems.length
                    "
                ></span>

                <span
                    x-text="
                        optionsModalItems.length === 1
                            ? 'opción'
                            : 'opciones'
                    "
                ></span>
            </p>


            <button
                type="button"

                @click="
                    closeOptionsModal()
                "

                class="
                    h-9

                    px-4

                    rounded-md

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-xs
                    font-medium
                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface-soft)]

                    transition-colors
                "
            >
                Listo
            </button>
        </div>

    </section>

</div>