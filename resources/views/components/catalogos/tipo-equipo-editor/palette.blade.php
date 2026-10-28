{{-- ============================================================
    PALETA DEL CONSTRUCTOR
============================================================ --}}
<div
    class="
        space-y-4
    "
>

    {{-- ========================================================
        TIPOS DE CAMPO
    ======================================================== --}}
    <section
        class="
            overflow-hidden

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]
        "
    >

        {{-- CABECERA --}}
        <div
            class="
                px-4
                py-4

                border-b
                border-[var(--theme-border)]
            "
        >
            <h3
                class="
                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Agregar campo
            </h3>

            <p
                class="
                    mt-1

                    text-[10px]
                    leading-relaxed
                    text-[var(--theme-text-muted)]
                "
            >
                Selecciona el tipo de información que deseas agregar al formulario.
            </p>
        </div>


        {{-- TIPOS --}}
        <div
            class="
                p-3

                grid
                grid-cols-2

                gap-2
            "
        >

            {{-- ====================================================
                TEXTO
            ==================================================== --}}
            <button
                type="button"

                @click="
                    addControl(
                        'text'
                    )
                "

                class="
                    min-h-[86px]

                    p-3

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-left

                    hover:border-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    active:scale-[0.98]

                    transition
                "
            >
                <div
                    class="
                        w-8
                        h-8

                        rounded-md

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]

                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    T
                </div>

                <p
                    class="
                        mt-2

                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Texto
                </p>

                <p
                    class="
                        mt-0.5

                        text-[9px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Una línea
                </p>
            </button>


            {{-- ====================================================
                TEXTO LARGO
            ==================================================== --}}
            <button
                type="button"

                @click="
                    addControl(
                        'textarea'
                    )
                "

                class="
                    min-h-[86px]

                    p-3

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-left

                    hover:border-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    active:scale-[0.98]

                    transition
                "
            >
                <div
                    class="
                        w-8
                        h-8

                        rounded-md

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]

                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    ¶
                </div>

                <p
                    class="
                        mt-2

                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Texto largo
                </p>

                <p
                    class="
                        mt-0.5

                        text-[9px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Varias líneas
                </p>
            </button>


            {{-- ====================================================
                NÚMERO
            ==================================================== --}}
            <button
                type="button"

                @click="
                    addControl(
                        'number'
                    )
                "

                class="
                    min-h-[86px]

                    p-3

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-left

                    hover:border-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    active:scale-[0.98]

                    transition
                "
            >
                <div
                    class="
                        w-8
                        h-8

                        rounded-md

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]

                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    #
                </div>

                <p
                    class="
                        mt-2

                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Número
                </p>

                <p
                    class="
                        mt-0.5

                        text-[9px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Valores numéricos
                </p>
            </button>


            {{-- ====================================================
                FECHA
            ==================================================== --}}
            <button
                type="button"

                @click="
                    addControl(
                        'date'
                    )
                "

                class="
                    min-h-[86px]

                    p-3

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-left

                    hover:border-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    active:scale-[0.98]

                    transition
                "
            >
                <div
                    class="
                        w-8
                        h-8

                        rounded-md

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]

                        text-[11px]
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    01
                </div>

                <p
                    class="
                        mt-2

                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Fecha
                </p>

                <p
                    class="
                        mt-0.5

                        text-[9px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Selector de fecha
                </p>
            </button>


            {{-- ====================================================
                SÍ / NO
            ==================================================== --}}
            <button
                type="button"

                @click="
                    addControl(
                        'boolean'
                    )
                "

                class="
                    min-h-[86px]

                    p-3

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-left

                    hover:border-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    active:scale-[0.98]

                    transition
                "
            >
                <div
                    class="
                        w-8
                        h-8

                        rounded-md

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]

                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    ✓
                </div>

                <p
                    class="
                        mt-2

                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Sí / No
                </p>

                <p
                    class="
                        mt-0.5

                        text-[9px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Interruptor
                </p>
            </button>


            {{-- ====================================================
                LISTA
            ==================================================== --}}
            <button
                type="button"

                @click="
                    addControl(
                        'select'
                    )
                "

                class="
                    min-h-[86px]

                    p-3

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    text-left

                    hover:border-[var(--theme-primary)]
                    hover:bg-[var(--theme-primary-soft)]

                    active:scale-[0.98]

                    transition
                "
            >
                <div
                    class="
                        w-8
                        h-8

                        rounded-md

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]

                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    ▾
                </div>

                <p
                    class="
                        mt-2

                        text-xs
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Lista
                </p>

                <p
                    class="
                        mt-0.5

                        text-[9px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Opciones propias
                </p>
            </button>

        </div>

    </section>


    {{-- ========================================================
        CAMPOS EXISTENTES DISPONIBLES
    ======================================================== --}}
    <section
        class="
            overflow-hidden

            rounded-xl

            border
            border-[var(--theme-border)]

            bg-[var(--theme-surface)]
        "
    >

        {{-- CABECERA --}}
        <div
            class="
                px-4
                py-4

                border-b
                border-[var(--theme-border)]
            "
        >
            <h3
                class="
                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Campos disponibles
            </h3>

            <p
                class="
                    mt-1

                    text-[10px]
                    leading-relaxed
                    text-[var(--theme-text-muted)]
                "
            >
                Campos existentes que no forman parte actualmente de este formulario.
            </p>


            {{-- BUSCADOR --}}
            <div
                class="
                    relative
                    mt-3
                "
            >
                <div
                    class="
                        pointer-events-none

                        absolute
                        inset-y-0
                        left-0

                        w-9

                        flex
                        items-center
                        justify-center

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
                        stroke-width="1.7"
                        stroke-linecap="round"
                    ><use href="{{ \App\Support\Icono::url('i-f6ba916') }}"></use></svg>
                </div>


                <input
                    type="search"

                    x-model="availableSearch"

                    placeholder="Buscar campo..."

                    class="
                        w-full
                        h-9

                        pl-9
                        pr-3

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface-soft)]

                        text-xs
                        text-[var(--theme-text)]

                        placeholder:text-[var(--theme-text-muted)]

                        focus:outline-none
                        focus:border-[var(--theme-primary)]

                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]

                        transition-colors
                    "
                >
            </div>
        </div>


        {{-- ====================================================
            LISTA
        ==================================================== --}}
        <div
            class="
                p-2

                max-h-[380px]
                overflow-y-auto
            "
        >

            <template
                x-for="
                    field in availableFields
                "

                :key="
                    field.uid
                "
            >
                <div
                    class="
                        flex
                        items-center
                        justify-between

                        gap-3

                        px-3
                        py-3

                        rounded-lg

                        hover:bg-[var(--theme-surface-soft)]

                        transition-colors
                    "
                >

                    {{-- INFORMACIÓN --}}
                    <div
                        class="
                            min-w-0
                            flex-1
                        "
                    >
                        <div
                            class="
                                flex
                                flex-wrap
                                items-center
                                gap-1.5
                            "
                        >
                            <p
                                x-text="
                                    field.etiqueta
                                    || field.nombre_base
                                    || 'Campo'
                                "

                                class="
                                    truncate

                                    text-xs
                                    font-medium
                                    text-[var(--theme-text-strong)]
                                "
                            ></p>


                            <span
                                x-text="
                                    controlLabel(
                                        field.tipo_control
                                    )
                                "

                                class="
                                    px-1.5
                                    py-0.5

                                    rounded

                                    bg-[var(--theme-surface-soft)]

                                    text-[9px]
                                    font-medium
                                    text-[var(--theme-text-muted)]
                                "
                            ></span>
                        </div>


                        <p
                            x-show="
                                field.clave
                            "

                            x-text="
                                field.clave
                            "

                            class="
                                mt-1

                                truncate

                                font-mono
                                text-[9px]
                                text-[var(--theme-text-muted)]
                            "
                        ></p>


                        {{-- VER LISTA --}}
                        <button
                            type="button"

                            x-show="
                                field.tipo_control
                                    === 'select'
                            "

                            @click.stop="
                                openOptions(
                                    field.uid
                                )
                            "

                            class="
                                mt-1.5

                                text-[10px]
                                font-medium
                                text-[var(--theme-primary)]

                                hover:underline
                            "
                        >
                            Ver opciones
                        </button>
                    </div>


                    {{-- AGREGAR --}}
                    <button
                        type="button"

                        @click="
                            addExistingField(
                                field.uid
                            )
                        "

                        class="
                            h-8

                            px-3

                            shrink-0

                            inline-flex
                            items-center
                            justify-center
                            gap-1

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface)]

                            text-[10px]
                            font-medium
                            text-[var(--theme-primary)]

                            hover:border-[var(--theme-primary)]
                            hover:bg-[var(--theme-primary-soft)]

                            active:scale-[0.97]

                            transition
                        "
                    >
                        <svg
                            class="
                                w-3
                                h-3
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        ><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>

                        Agregar
                    </button>

                </div>
            </template>


            {{-- ====================================================
                VACÍO
            ==================================================== --}}
            <div
                x-show="
                    availableFields.length === 0
                "

                x-cloak

                class="
                    px-4
                    py-10

                    text-center
                "
            >
                <p
                    class="
                        text-xs
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    No hay campos disponibles
                </p>

                <p
                    class="
                        mt-1

                        text-[10px]
                        text-[var(--theme-text-muted)]
                    "
                >
                    Todos los campos existentes ya forman parte del formulario.
                </p>
            </div>

        </div>

    </section>

</div>