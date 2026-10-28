<div>

    <div class="space-y-5">

        {{-- ========================================================
            TIPO DE MOVIMIENTO
        ======================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                p-4
                sm:p-5
            "
        >
            <div
                class="
                    flex
                    flex-col

                    lg:flex-row
                    lg:items-center

                    gap-3
                    lg:gap-6
                "
            >
                <p
                    class="
                        shrink-0

                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Tipo de movimiento
                </p>

                <div
                    class="
                        grid
                        grid-cols-2

                        w-full
                        max-w-2xl
                    "
                >

                    <button
                        type="button"
                        disabled
                        class="
                            h-10

                            flex
                            items-center
                            justify-center
                            gap-2

                            rounded-l-lg

                            border
                            border-[var(--theme-primary-border)]

                            bg-[var(--theme-primary-soft)]

                            text-sm
                            font-medium
                            text-[var(--theme-primary)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        ><use href="{{ \App\Support\Icono::url('i-a66f8d6') }}"></use></svg>

                        Asignación
                    </button>

                    <a
                        href="{{ route('asignaciones.reassign') }}"
                        wire:navigate.hover
                        class="
                            h-10

                            flex
                            items-center
                            justify-center
                            gap-2

                            rounded-r-lg

                            border
                            border-l-0
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            font-medium
                            text-[var(--theme-text-muted)]

                            hover:bg-[var(--theme-surface-soft)]
                            hover:text-[var(--theme-text)]

                            transition-colors
                        "
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>

                        Reasignación
                    </a>

                </div>
            </div>
        </section>


        {{-- ========================================================
            DATOS DE ASIGNACIÓN
        ======================================================== --}}
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
                    grid
                    grid-cols-1
                    lg:grid-cols-2

                    gap-x-5
                    gap-y-4
                "
            >

                {{-- EQUIPO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Nombre de Equipo *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled

                            placeholder="Cargando equipos disponibles..."

                            class="
                                w-full

                                px-3
                                py-2.5
                                pr-10

                                border-0
                                bg-transparent

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                disabled:opacity-100
                                disabled:cursor-default
                            "
                        >

                        <div
                            class="
                                absolute
                                right-3
                                top-1/2
                                -translate-y-1/2

                                flex
                                items-center
                                justify-center

                                text-[var(--theme-primary)]
                            "
                            aria-label="Cargando equipos"
                        >
                            <svg
                                class="w-4 h-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            ><use href="{{ \App\Support\Icono::url('i-ef15586') }}"></use></svg>
                        </div>
                    </div>
                </div>


                {{-- COLABORADOR --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Nombre Completo de Colaborador *
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Nombre completo del colaborador"

                        class="
                            w-full

                            px-3
                            py-2.5

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- NÚMERO DE COLABORADOR --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Número de colaborador
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Ej. 001245"

                        class="
                            w-full

                            px-3
                            py-2.5

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            text-[var(--theme-text)]

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
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Host
                    </label>

                    <input
                        type="text"
                        disabled

                        placeholder="Se obtiene del equipo seleccionado"

                        class="
                            w-full

                            px-3
                            py-2.5

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface-soft)]

                            text-sm
                            text-[var(--theme-text-muted)]

                            placeholder:text-[var(--theme-text-muted)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- HOTEL --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] }} *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled

                            placeholder="Cargando..."

                            class="
                                w-full

                                px-3
                                py-2.5
                                pr-10

                                border-0
                                bg-transparent

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                disabled:opacity-100
                                disabled:cursor-default
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-3

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
                    </div>
                </div>


                {{-- DEPARTAMENTO --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Departamento *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled

                            placeholder="Selecciona un hotel primero"

                            class="
                                w-full

                                px-3
                                py-2.5
                                pr-10

                                border-0
                                bg-transparent

                                text-sm
                                text-[var(--theme-text-muted)]

                                placeholder:text-[var(--theme-text-muted)]

                                disabled:opacity-100
                                disabled:cursor-default
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-3

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
                    </div>
                </div>


                {{-- UBICACIÓN --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Ubicación
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled

                            placeholder="Selecciona un departamento primero"

                            class="
                                w-full

                                px-3
                                py-2.5
                                pr-10

                                border-0
                                bg-transparent

                                text-sm
                                text-[var(--theme-text-muted)]

                                placeholder:text-[var(--theme-text-muted)]

                                disabled:opacity-100
                                disabled:cursor-default
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-3

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
                    </div>
                </div>


                {{-- FECHA --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Fecha de Asignación *
                    </label>

                    <input
                        type="date"
                        disabled

                        class="
                            w-full

                            px-3
                            py-2.5

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            text-[var(--theme-text)]

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    >

                </div>


                {{-- TIPO DE ASIGNACIÓN --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Tipo de asignación *
                    </label>

                    <div
                        class="
                            relative

                            flex
                            items-center

                            w-full

                            rounded-md

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]
                        "
                    >
                        <input
                            type="text"
                            disabled

                            placeholder="Cargando tipos de asignación..."

                            class="
                                w-full

                                px-3
                                py-2.5
                                pr-10

                                border-0
                                bg-transparent

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                disabled:opacity-100
                                disabled:cursor-default
                            "
                        >

                        <svg
                            class="
                                absolute
                                right-3

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
                    </div>
                </div>


                {{-- OBSERVACIONES --}}
                <div class="lg:col-span-2 min-w-0">

                    <label
                        class="
                            block
                            mb-1.5

                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Observaciones

                        <span>
                            (Opcional)
                        </span>
                    </label>

                    <textarea
                        rows="5"
                        disabled

                        placeholder="Escribe alguna observación relacionada con la asignación..."

                        class="
                            w-full

                            px-3
                            py-2.5

                            rounded-lg

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            resize-none

                            disabled:opacity-100
                            disabled:cursor-default
                        "
                    ></textarea>

                </div>

            </div>
        </section>


        {{-- ========================================================
            ACCIONES
        ======================================================== --}}
        <div
            class="
                flex
                flex-col-reverse

                sm:flex-row
                sm:items-center
                sm:justify-end

                gap-3

                pb-2
            "
        >
            <a
                href="{{ route('asignaciones.index') }}"
                wire:navigate.hover
                class="
                    w-full
                    sm:w-auto

                    h-11
                    px-5

                    flex
                    items-center
                    justify-center

                    rounded-lg

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

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
                    px-5

                    flex
                    items-center
                    justify-center
                    gap-2

                    rounded-lg

                    bg-[var(--theme-primary)]

                    text-sm
                    font-medium
                    text-white

                    opacity-70
                    cursor-default
                "
            >
                <svg
                    class="w-4 h-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                ><use href="{{ \App\Support\Icono::url('i-ef15586') }}"></use></svg>

                Cargando...
            </button>
        </div>

    </div>

</div>