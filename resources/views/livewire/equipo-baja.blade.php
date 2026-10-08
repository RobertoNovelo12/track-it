<div
    x-data="{
        successVisible: false,
        successMessage: '',
        successTimer: null,

        showSuccess(message) {
            this.successMessage = message;
            this.successVisible = true;

            if (this.successTimer) {
                clearTimeout(this.successTimer);
            }

            this.successTimer = setTimeout(() => {
                this.successVisible = false;
            }, 3000);
        }
    }"
    @baja-formulario-validado.window="
        showSuccess($event.detail.message)
    "
>

    {{-- ============================================================
        MENSAJE
    ============================================================ --}}
    <div
        x-show="successVisible"
        x-cloak
        x-transition
        class="
            fixed
            z-[150]

            top-20
            left-4
            right-4

            sm:left-auto
            sm:right-6
            sm:w-96

            flex
            items-start
            gap-3

            rounded-xl

            border
            border-emerald-500/30

            bg-[var(--theme-surface)]

            px-4
            py-3

            shadow-lg
        "
    >
        <div
            class="
                flex
                h-8
                w-8
                shrink-0
                items-center
                justify-center

                rounded-full

                bg-emerald-500/15
                text-emerald-500
            "
        >
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M5 12l4 4L19 7"/>
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <p
                class="
                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Información completa
            </p>

            <p
                class="
                    mt-0.5
                    text-xs
                    text-[var(--theme-text)]
                "
                x-text="successMessage"
            ></p>
        </div>

        <button
            type="button"
            @click="successVisible = false"
            class="
                text-[var(--theme-text-muted)]
                hover:text-[var(--theme-text)]
            "
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M6 6l12 12"/>
                <path d="M18 6L6 18"/>
            </svg>
        </button>
    </div>


    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div
        class="
            mb-5

            flex
            flex-col
            gap-4

            lg:flex-row
            lg:items-start
            lg:justify-between
        "
    >

        <div class="min-w-0">

            <h1
                class="
                    text-xl
                    font-semibold
                    leading-tight

                    text-[var(--theme-text-strong)]
                "
            >
                Dar de baja equipo
            </h1>


            <p
                class="
                    mt-1

                    text-xs
                    leading-relaxed

                    text-[var(--theme-text-muted)]
                "
            >
                <a
                    href="{{ route('equipos.index') }}"
                    class="
                        hover:text-[var(--theme-primary)]
                        transition-colors
                    "
                >
                    Equipos Tecnológicos
                </a>

                <span class="mx-1">
                    ›
                </span>

                <a
                    href="{{ route('equipos.show', $equipoId) }}"
                    class="
                        hover:text-[var(--theme-primary)]
                        transition-colors
                    "
                >
                    Detalle del equipo
                </a>

                <span class="mx-1">
                    ›
                </span>

                <a
                    href="{{ route('equipos.edit', $equipoId) }}"
                    class="
                        hover:text-[var(--theme-primary)]
                        transition-colors
                    "
                >
                    Editar equipo
                </a>

                <span class="mx-1">
                    ›
                </span>

                <span class="text-[var(--theme-text)]">
                    Dar de baja
                </span>
            </p>

        </div>


        <div
            class="
                flex
                items-center
                gap-3
            "
        >

            <a
                href="{{ route('equipos.edit', $equipoId) }}"
                class="
                    h-10

                    inline-flex
                    items-center
                    justify-center

                    rounded-lg

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    px-5

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]

                    transition-colors
                "
            >
                Cancelar
            </a>


            <button
                type="button"
                wire:click="confirmar"
                wire:loading.attr="disabled"
                wire:target="confirmar"
                class="
                    h-10

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-lg

                    border
                    border-[var(--theme-danger)]

                    bg-[var(--theme-danger)]

                    px-5

                    text-sm
                    font-semibold
                    text-white

                    hover:opacity-90

                    disabled:cursor-not-allowed
                    disabled:opacity-60

                    transition-opacity
                "
            >

                <svg
                    wire:loading.remove
                    wire:target="confirmar"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
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


                <svg
                    wire:loading
                    wire:target="confirmar"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="9"
                        stroke="currentColor"
                        stroke-width="3"
                    />

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M21 12a9 9 0 00-9-9v3a6 6 0 016 6h3z"
                    />
                </svg>


                <span
                    wire:loading.remove
                    wire:target="confirmar"
                >
                    Confirmar
                </span>

                <span
                    wire:loading
                    wire:target="confirmar"
                >
                    Validando...
                </span>

            </button>

        </div>

    </div>


    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            gap-4

            xl:grid-cols-[minmax(0,1.55fr)_minmax(340px,0.95fr)]
        "
    >

        {{-- ========================================================
            COLUMNA IZQUIERDA
        ======================================================== --}}
        <div class="space-y-4">

            {{-- ====================================================
                INFORMACIÓN GENERAL
            ==================================================== --}}
            <section
                class="
                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    p-4
                    sm:p-5
                "
            >

                <div
                    class="
                        mb-5

                        flex
                        items-center
                        gap-2
                    "
                >
                    <svg
                        class="
                            h-5
                            w-5

                            text-[var(--theme-text-muted)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 10v6"/>
                        <path d="M12 7h.01"/>
                    </svg>

                    <h2
                        class="
                            text-base
                            font-semibold

                            text-[var(--theme-text-strong)]
                        "
                    >
                        Información general
                    </h2>
                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-x-8
                        gap-y-5

                        sm:grid-cols-2
                        lg:grid-cols-3
                    "
                >

                    {{-- Nombre --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Nombre del activo
                        </p>

                        <p
                            class="
                                mt-1

                                text-sm
                                text-[var(--theme-text-muted)]

                                break-words
                            "
                        >
                            {{ $equipo['nombre_equipo'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Serie --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Número de serie
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['numero_serie'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Registro --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Fecha de registro
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['fecha_registro'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Tipo --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Tipo
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['tipo_equipo'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Código --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Código de inventario
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['codigo_inventario'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Factura --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Factura
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['numero_factura'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Marca --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Marca
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['marca'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Estado --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Estado
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['estado'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Garantía --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Garantía
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['fecha_fin_garantia'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Modelo --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Modelo
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['modelo'] ?: '—' }}
                        </p>
                    </div>


                    {{-- Proveedor actual del equipo --}}
                    <div>
                        <p class="text-xs font-medium text-[var(--theme-text)]">
                            Proveedor
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                            {{ $equipo['proveedor'] ?: '—' }}
                        </p>
                    </div>

                </div>

            </section>


            {{-- ====================================================
                INFORMACIÓN DE BAJA
            ==================================================== --}}
            <section
                class="
                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    p-4
                    sm:p-5
                "
            >

                <h2
                    class="
                        mb-4

                        text-base
                        font-semibold

                        text-[var(--theme-text-strong)]
                    "
                >
                    Información de baja
                </h2>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4

                        md:grid-cols-2
                    "
                >

                    {{-- Motivo --}}
                    <div>
                        <label
                            for="motivo-baja"
                            class="
                                mb-1.5
                                block

                                text-xs
                                font-medium

                                text-[var(--theme-text)]
                            "
                        >
                            Motivo de baja
                            <span class="text-[var(--theme-danger)]">*</span>
                        </label>

                        <input
                            id="motivo-baja"
                            type="text"
                            wire:model="motivoBaja"
                            maxlength="500"
                            placeholder="Escribe el motivo de la baja"
                            class="
                                h-10
                                w-full

                                rounded-lg

                                border
                                border-[var(--theme-border-strong)]

                                bg-[var(--theme-surface)]

                                px-3

                                text-sm
                                text-[var(--theme-text)]

                                outline-none

                                placeholder:text-[var(--theme-text-muted)]

                                focus:border-[var(--theme-primary)]
                            "
                        />

                        @error('motivoBaja')
                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Fecha --}}
                    <div>
                        <label
                            for="fecha-baja"
                            class="
                                mb-1.5
                                block

                                text-xs
                                font-medium

                                text-[var(--theme-text)]
                            "
                        >
                            Fecha de baja
                            <span class="text-[var(--theme-danger)]">*</span>
                        </label>

                        <input
                            id="fecha-baja"
                            type="date"
                            wire:model="fechaBaja"
                            class="
                                h-10
                                w-full

                                rounded-lg

                                border
                                border-[var(--theme-border-strong)]

                                bg-[var(--theme-surface)]

                                px-3

                                text-sm
                                text-[var(--theme-text)]

                                outline-none

                                focus:border-[var(--theme-primary)]
                            "
                        />

                        @error('fechaBaja')
                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>


                {{-- Comentarios --}}
                <div class="mt-4">

                    <label
                        for="comentarios-baja"
                        class="
                            mb-1.5
                            block

                            text-xs
                            font-medium

                            text-[var(--theme-text)]
                        "
                    >
                        Comentarios de baja
                    </label>

                    <textarea
                        id="comentarios-baja"
                        wire:model="comentariosBaja"
                        rows="3"
                        maxlength="2000"
                        placeholder="Ingresa comentarios o detalles adicionales sobre la baja del equipo"
                        class="
                            min-h-[88px]
                            w-full
                            resize-y

                            rounded-lg

                            border
                            border-[var(--theme-border-strong)]

                            bg-[var(--theme-surface)]

                            px-3
                            py-2.5

                            text-sm
                            text-[var(--theme-text)]

                            outline-none

                            placeholder:text-[var(--theme-text-muted)]

                            focus:border-[var(--theme-primary)]
                        "
                    ></textarea>

                    @error('comentariosBaja')
                        <p
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </section>


            {{-- ====================================================
                EVIDENCIAS
            ==================================================== --}}
            <section
                class="
                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    p-4
                    sm:p-5
                "
            >

                <div class="mb-4">

                    <h2
                        class="
                            text-base
                            font-semibold

                            text-[var(--theme-text-strong)]
                        "
                    >
                        Evidencias de baja
                    </h2>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Agrega las cuatro fotografías que se utilizarán
                        en el formato oficial de baja.
                    </p>

                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-3

                        sm:grid-cols-2
                    "
                >

                    {{-- ==================================================
                        FRONTAL
                    ================================================== --}}
                    <div
                        class="
                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            p-3
                        "
                    >

                        <div
                            class="
                                mb-3

                                flex
                                items-center
                                justify-between
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    font-medium

                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Vista frontal
                            </span>

                            @if ($fotoFrontal)
                                <button
                                    type="button"
                                    wire:click="eliminarFoto('frontal')"
                                    class="
                                        text-xs
                                        text-[var(--theme-danger)]

                                        hover:underline
                                    "
                                >
                                    Quitar
                                </button>
                            @endif
                        </div>


                        @if ($fotoFrontal)

                            <div
                                class="
                                    overflow-hidden

                                    rounded-lg

                                    border
                                    border-[var(--theme-border)]

                                    bg-[var(--theme-surface-soft)]
                                "
                            >
                                <img
                                    src="{{ $fotoFrontal->temporaryUrl() }}"
                                    alt="Vista frontal"
                                    class="
                                        h-36
                                        w-full
                                        object-contain
                                    "
                                />
                            </div>

                        @else

                            <label
                                class="
                                    flex
                                    h-36
                                    cursor-pointer
                                    flex-col
                                    items-center
                                    justify-center

                                    rounded-lg

                                    border
                                    border-dashed
                                    border-[var(--theme-border-strong)]

                                    bg-[var(--theme-surface-soft)]

                                    px-3

                                    text-center

                                    hover:border-[var(--theme-primary)]

                                    transition-colors
                                "
                            >

                                <svg
                                    class="
                                        mb-2
                                        h-5
                                        w-5

                                        text-[var(--theme-text-muted)]
                                    "
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M12 16V4"/>
                                    <path d="M7 9l5-5 5 5"/>
                                    <path d="M4 20h16"/>
                                </svg>

                                <span
                                    class="
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    Seleccionar fotografía
                                </span>

                                <span
                                    class="
                                        mt-1
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    JPG, PNG o WEBP
                                </span>

                                <input
                                    type="file"
                                    wire:model="fotoFrontal"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />

                            </label>

                        @endif


                        @error('fotoFrontal')
                            <p class="mt-2 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ==================================================
                        TRASERA
                    ================================================== --}}
                    <div
                        class="
                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            p-3
                        "
                    >

                        <div
                            class="
                                mb-3

                                flex
                                items-center
                                justify-between
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    font-medium

                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Vista trasera
                            </span>

                            @if ($fotoTrasera)
                                <button
                                    type="button"
                                    wire:click="eliminarFoto('trasera')"
                                    class="
                                        text-xs
                                        text-[var(--theme-danger)]

                                        hover:underline
                                    "
                                >
                                    Quitar
                                </button>
                            @endif
                        </div>


                        @if ($fotoTrasera)

                            <div
                                class="
                                    overflow-hidden

                                    rounded-lg

                                    border
                                    border-[var(--theme-border)]

                                    bg-[var(--theme-surface-soft)]
                                "
                            >
                                <img
                                    src="{{ $fotoTrasera->temporaryUrl() }}"
                                    alt="Vista trasera"
                                    class="
                                        h-36
                                        w-full
                                        object-contain
                                    "
                                />
                            </div>

                        @else

                            <label
                                class="
                                    flex
                                    h-36
                                    cursor-pointer
                                    flex-col
                                    items-center
                                    justify-center

                                    rounded-lg

                                    border
                                    border-dashed
                                    border-[var(--theme-border-strong)]

                                    bg-[var(--theme-surface-soft)]

                                    px-3

                                    text-center

                                    hover:border-[var(--theme-primary)]

                                    transition-colors
                                "
                            >

                                <svg
                                    class="
                                        mb-2
                                        h-5
                                        w-5

                                        text-[var(--theme-text-muted)]
                                    "
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M12 16V4"/>
                                    <path d="M7 9l5-5 5 5"/>
                                    <path d="M4 20h16"/>
                                </svg>

                                <span
                                    class="
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    Seleccionar fotografía
                                </span>

                                <span
                                    class="
                                        mt-1
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    JPG, PNG o WEBP
                                </span>

                                <input
                                    type="file"
                                    wire:model="fotoTrasera"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />

                            </label>

                        @endif


                        @error('fotoTrasera')
                            <p class="mt-2 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ==================================================
                        LATERAL 1
                    ================================================== --}}
                    <div
                        class="
                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            p-3
                        "
                    >

                        <div
                            class="
                                mb-3

                                flex
                                items-center
                                justify-between
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    font-medium

                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Vista lateral 1
                            </span>

                            @if ($fotoLateral1)
                                <button
                                    type="button"
                                    wire:click="eliminarFoto('lateral1')"
                                    class="
                                        text-xs
                                        text-[var(--theme-danger)]

                                        hover:underline
                                    "
                                >
                                    Quitar
                                </button>
                            @endif
                        </div>


                        @if ($fotoLateral1)

                            <div
                                class="
                                    overflow-hidden

                                    rounded-lg

                                    border
                                    border-[var(--theme-border)]

                                    bg-[var(--theme-surface-soft)]
                                "
                            >
                                <img
                                    src="{{ $fotoLateral1->temporaryUrl() }}"
                                    alt="Vista lateral 1"
                                    class="
                                        h-36
                                        w-full
                                        object-contain
                                    "
                                />
                            </div>

                        @else

                            <label
                                class="
                                    flex
                                    h-36
                                    cursor-pointer
                                    flex-col
                                    items-center
                                    justify-center

                                    rounded-lg

                                    border
                                    border-dashed
                                    border-[var(--theme-border-strong)]

                                    bg-[var(--theme-surface-soft)]

                                    px-3

                                    text-center

                                    hover:border-[var(--theme-primary)]

                                    transition-colors
                                "
                            >

                                <svg
                                    class="
                                        mb-2
                                        h-5
                                        w-5

                                        text-[var(--theme-text-muted)]
                                    "
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M12 16V4"/>
                                    <path d="M7 9l5-5 5 5"/>
                                    <path d="M4 20h16"/>
                                </svg>

                                <span
                                    class="
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    Seleccionar fotografía
                                </span>

                                <span
                                    class="
                                        mt-1
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    JPG, PNG o WEBP
                                </span>

                                <input
                                    type="file"
                                    wire:model="fotoLateral1"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />

                            </label>

                        @endif


                        @error('fotoLateral1')
                            <p class="mt-2 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ==================================================
                        LATERAL 2
                    ================================================== --}}
                    <div
                        class="
                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            p-3
                        "
                    >

                        <div
                            class="
                                mb-3

                                flex
                                items-center
                                justify-between
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    font-medium

                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Vista lateral 2
                            </span>

                            @if ($fotoLateral2)
                                <button
                                    type="button"
                                    wire:click="eliminarFoto('lateral2')"
                                    class="
                                        text-xs
                                        text-[var(--theme-danger)]

                                        hover:underline
                                    "
                                >
                                    Quitar
                                </button>
                            @endif
                        </div>


                        @if ($fotoLateral2)

                            <div
                                class="
                                    overflow-hidden

                                    rounded-lg

                                    border
                                    border-[var(--theme-border)]

                                    bg-[var(--theme-surface-soft)]
                                "
                            >
                                <img
                                    src="{{ $fotoLateral2->temporaryUrl() }}"
                                    alt="Vista lateral 2"
                                    class="
                                        h-36
                                        w-full
                                        object-contain
                                    "
                                />
                            </div>

                        @else

                            <label
                                class="
                                    flex
                                    h-36
                                    cursor-pointer
                                    flex-col
                                    items-center
                                    justify-center

                                    rounded-lg

                                    border
                                    border-dashed
                                    border-[var(--theme-border-strong)]

                                    bg-[var(--theme-surface-soft)]

                                    px-3

                                    text-center

                                    hover:border-[var(--theme-primary)]

                                    transition-colors
                                "
                            >

                                <svg
                                    class="
                                        mb-2
                                        h-5
                                        w-5

                                        text-[var(--theme-text-muted)]
                                    "
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path d="M12 16V4"/>
                                    <path d="M7 9l5-5 5 5"/>
                                    <path d="M4 20h16"/>
                                </svg>

                                <span
                                    class="
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    Seleccionar fotografía
                                </span>

                                <span
                                    class="
                                        mt-1
                                        text-[11px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    JPG, PNG o WEBP
                                </span>

                                <input
                                    type="file"
                                    wire:model="fotoLateral2"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />

                            </label>

                        @endif


                        @error('fotoLateral2')
                            <p class="mt-2 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- ====================================================
                INFORMACIÓN
            ==================================================== --}}
            <div
                class="
                    flex
                    items-start
                    gap-3

                    rounded-xl

                    border
                    border-sky-500/20

                    bg-sky-500/10

                    px-4
                    py-3
                "
            >

                <svg
                    class="
                        mt-0.5
                        h-4
                        w-4
                        shrink-0

                        text-sky-500
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 10v6"/>
                    <path d="M12 7h.01"/>
                </svg>


                <div>
                    <p
                        class="
                            text-xs
                            font-semibold

                            text-sky-500
                        "
                    >
                        Información
                    </p>

                    <p
                        class="
                            mt-0.5
                            text-xs

                            text-[var(--theme-text)]
                        "
                    >
                        Para procesar correctamente la baja se requieren
                        el motivo, la autorización y las cuatro fotografías
                        del equipo.
                    </p>
                </div>

            </div>

        </div>


        {{-- ========================================================
            COLUMNA DERECHA
        ======================================================== --}}
        <div class="space-y-4">

            {{-- ====================================================
                VISTA RÁPIDA
            ==================================================== --}}
            <section
                class="
                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    p-4
                "
            >

                <div
                    class="
                        mb-4

                        flex
                        items-center
                        justify-between
                    "
                >
                    <h2
                        class="
                            text-base
                            font-semibold

                            text-[var(--theme-text-strong)]
                        "
                    >
                        Vista rápida del documento final
                    </h2>

                    <span
                        class="
                            rounded

                            border
                            border-[var(--theme-border-strong)]

                            px-2
                            py-0.5

                            text-[10px]
                            font-medium

                            text-[var(--theme-text-muted)]
                        "
                    >
                        PDF
                    </span>
                </div>


                {{-- Hoja --}}
                <div
                    class="
                        mx-auto

                        w-full
                        max-w-[420px]

                        overflow-hidden

                        border
                        border-[var(--theme-border)]

                        bg-white

                        shadow-md
                    "
                >

                    <div
                        class="
                            aspect-[0.707/1]

                            p-7

                            text-neutral-800
                        "
                    >

                        {{-- Encabezado ficticio --}}
                        <div
                            class="
                                flex
                                items-start
                                justify-between
                            "
                        >
                            <div
                                class="
                                    text-[8px]
                                    font-semibold
                                    tracking-wide
                                "
                            >
                                ORDEN DE BAJA
                            </div>

                            <div
                                class="
                                    text-right
                                    text-[7px]
                                    font-semibold
                                "
                            >
                                GRAND PALLADIUM
                            </div>
                        </div>


                        <div
                            class="
                                mt-7

                                text-[8px]
                            "
                        >
                            <div>
                                <span class="font-semibold">
                                    Código:
                                </span>

                                {{ $equipo['codigo_inventario'] ?: '—' }}
                            </div>

                            <div class="mt-1">
                                <span class="font-semibold">
                                    Equipo:
                                </span>

                                {{ $equipo['nombre_equipo'] ?: '—' }}
                            </div>

                            <div class="mt-1">
                                <span class="font-semibold">
                                    Motivo:
                                </span>

                                {{ $motivoBaja !== '' ? $motivoBaja : '—' }}
                            </div>
                        </div>


                        {{-- Fotos --}}
                        <div
                            class="
                                mt-7

                                grid
                                grid-cols-2

                                border-l
                                border-t
                                border-neutral-500
                            "
                        >

                            {{-- Frontal --}}
                            <div
                                class="
                                    flex
                                    aspect-[1.35/1]
                                    items-center
                                    justify-center

                                    overflow-hidden

                                    border-b
                                    border-r
                                    border-neutral-500
                                "
                            >
                                @if ($fotoFrontal)
                                    <img
                                        src="{{ $fotoFrontal->temporaryUrl() }}"
                                        alt=""
                                        class="
                                            h-full
                                            w-full
                                            object-contain
                                        "
                                    />
                                @else
                                    <span class="text-[7px] text-neutral-400">
                                        Vista frontal
                                    </span>
                                @endif
                            </div>


                            {{-- Trasera --}}
                            <div
                                class="
                                    flex
                                    aspect-[1.35/1]
                                    items-center
                                    justify-center

                                    overflow-hidden

                                    border-b
                                    border-r
                                    border-neutral-500
                                "
                            >
                                @if ($fotoTrasera)
                                    <img
                                        src="{{ $fotoTrasera->temporaryUrl() }}"
                                        alt=""
                                        class="
                                            h-full
                                            w-full
                                            object-contain
                                        "
                                    />
                                @else
                                    <span class="text-[7px] text-neutral-400">
                                        Vista trasera
                                    </span>
                                @endif
                            </div>


                            {{-- Lateral 1 --}}
                            <div
                                class="
                                    flex
                                    aspect-[1.35/1]
                                    items-center
                                    justify-center

                                    overflow-hidden

                                    border-b
                                    border-r
                                    border-neutral-500
                                "
                            >
                                @if ($fotoLateral1)
                                    <img
                                        src="{{ $fotoLateral1->temporaryUrl() }}"
                                        alt=""
                                        class="
                                            h-full
                                            w-full
                                            object-contain
                                        "
                                    />
                                @else
                                    <span class="text-[7px] text-neutral-400">
                                        Vista lateral 1
                                    </span>
                                @endif
                            </div>


                            {{-- Lateral 2 --}}
                            <div
                                class="
                                    flex
                                    aspect-[1.35/1]
                                    items-center
                                    justify-center

                                    overflow-hidden

                                    border-b
                                    border-r
                                    border-neutral-500
                                "
                            >
                                @if ($fotoLateral2)
                                    <img
                                        src="{{ $fotoLateral2->temporaryUrl() }}"
                                        alt=""
                                        class="
                                            h-full
                                            w-full
                                            object-contain
                                        "
                                    />
                                @else
                                    <span class="text-[7px] text-neutral-400">
                                        Vista lateral 2
                                    </span>
                                @endif
                            </div>

                        </div>


                        <p
                            class="
                                mt-4
                                text-center

                                text-[6px]
                                text-neutral-400
                            "
                        >
                            Evidencias fotográficas del equipo
                        </p>

                    </div>

                </div>


                <p
                    class="
                        mt-3

                        text-center
                        text-[11px]

                        text-[var(--theme-text-muted)]
                    "
                >
                    La vista previa se actualizará conforme agregues
                    las fotografías.
                </p>

            </section>


            {{-- ====================================================
                AUTORIZA
            ==================================================== --}}
            <section
                class="
                    rounded-xl

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    p-4
                "
            >

                <label
                    for="autoriza"
                    class="
                        mb-1.5
                        block

                        text-xs
                        font-medium

                        text-[var(--theme-text)]
                    "
                >
                    Autoriza
                    <span class="text-[var(--theme-danger)]">*</span>
                </label>


                <input
                    id="autoriza"
                    type="text"
                    wire:model="autoriza"
                    maxlength="255"
                    placeholder="Nombre completo de la persona que autoriza la baja"
                    class="
                        h-10
                        w-full

                        rounded-lg

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        px-3

                        text-sm
                        text-[var(--theme-text)]

                        outline-none

                        placeholder:text-[var(--theme-text-muted)]

                        focus:border-[var(--theme-primary)]
                    "
                />


                @error('autoriza')
                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-danger)]
                        "
                    >
                        {{ $message }}
                    </p>
                @enderror

            </section>

        </div>

    </div>

</div>