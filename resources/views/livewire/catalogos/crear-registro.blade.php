<div>
<div
    x-data="{
        successVisible: false,
        successMessage: '',
        successTimer: null,

        showSuccess(message) {
            this.successMessage = message ?? 'El registro se creó correctamente.';
            this.successVisible = true;

            if (this.successTimer) {
                clearTimeout(this.successTimer);
            }

            this.successTimer = setTimeout(() => {
                this.successVisible = false;
            }, 3200);
        }
    }"

    @catalog-record-created.window="
        showSuccess(
            $event.detail.message ?? 'El registro se creó correctamente.'
        )
    "
>

    {{-- ============================================================
        MENSAJE DE ÉXITO
    ============================================================ --}}
    <div
        x-show="successVisible"
        x-cloak

        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"

        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"

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

            bg-[var(--theme-surface)]

            border
            border-emerald-500/30

            rounded-xl
            shadow-lg

            px-4
            py-3
        "
    >
        <div
            class="
                shrink-0

                w-8
                h-8

                rounded-full

                bg-emerald-500/15
                text-emerald-500

                flex
                items-center
                justify-center
            "
        >
            <svg
                class="w-5 h-5"
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
                Registro creado
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
                shrink-0

                text-[var(--theme-text-muted)]

                hover:text-[var(--theme-text)]

                transition-colors
            "

            aria-label="Cerrar mensaje"
        >
            <svg
                class="w-4 h-4"
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
        CABECERA
    ============================================================ --}}
    <div class="mb-5">

        <h1
            class="
                text-xl
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Gestión de marcas y modelos
        </h1>


        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2

                mt-1

                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            <span>
                Catálogo base
            </span>

            <span>
                ›
            </span>

            <span class="text-[var(--theme-text)]">
                Nuevo registro
            </span>
        </div>

    </div>


    {{-- ============================================================
        FORMULARIO
    ============================================================ --}}
    <form
        wire:submit.prevent="save"
        class="relative"
    >
        <fieldset
            wire:loading.attr="disabled"
            wire:target="save"

            wire:loading.class="
                pointer-events-none
                opacity-60
            "

            class="
                space-y-4
                sm:space-y-6

                transition-opacity
                duration-150
            "
        >

            {{-- ====================================================
                INFORMACIÓN GENERAL
            ==================================================== --}}
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
                    Información general
                </h2>


                <div
                    class="
                        grid
                        grid-cols-1

                        md:grid-cols-2
                        xl:grid-cols-3

                        gap-4
                    "
                >
                    {{-- TIPO DE REGISTRO --}}
                    <div class="min-w-0">

                        <label
                            for="tipoRegistro"

                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Tipo de registro *
                        </label>

                        <select
                            id="tipoRegistro"

                            wire:model.live="tipoRegistro"

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

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                outline-none
                                transition-colors
                            "
                        >
                            <option value="marca">
                                Marca
                            </option>

                            <option value="modelo">
                                Modelo
                            </option>
                        </select>

                    </div>


                    {{-- CÓDIGO INTERNO AUTOMÁTICO --}}
                    <div class="min-w-0">

                        <label
                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Código interno
                        </label>

                        <div class="relative">

                            <input
                                type="text"

                                value="Se genera automáticamente"

                                readonly

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
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>

                        </div>

                    </div>


                    {{-- ESTADO INICIAL --}}
                    <div class="min-w-0">

                        <label
                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Estado inicial
                        </label>

                        <div
                            class="
                                min-h-[42px]

                                flex
                                items-center

                                px-3
                                py-2.5

                                rounded-md

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface-soft)]
                            "
                        >
                            <span
                                class="
                                    inline-flex
                                    items-center

                                    px-2
                                    py-1

                                    rounded-full

                                    bg-[var(--theme-success-soft)]
                                    text-[var(--theme-success)]

                                    text-[10px]
                                    font-medium
                                "
                            >
                                Activo
                            </span>
                        </div>

                    </div>


                    {{-- NOMBRE --}}
                    <div class="min-w-0">

                        <label
                            for="catalog-name"

                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            {{ $tipoRegistro === 'marca' ? 'Nombre de la marca' : 'Nombre del modelo' }} *
                        </label>

                        <input
                            id="catalog-name"
                            type="text"

                            wire:model="nombre"

                            placeholder="{{ $tipoRegistro === 'marca' ? 'Ej. Apple' : 'Ej. Latitude 5440' }}"

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

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                outline-none
                                transition-colors
                            "
                        >

                        @error('nombre')

                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    @if ($tipoRegistro === 'modelo')

                        {{-- MARCA --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                wire-model="idMarca"

                                :options="$marcas->map(fn ($marca) => [
                                    'value' => $marca->id_marca,
                                    'label' => $marca->nombre,
                                ])->values()->all()"

                                label="Marca *"
                                placeholder="Buscar marca..."
                            />

                            @error('idMarca')

                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- TIPO DE DISPOSITIVO --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                wire-model="idTipoEquipo"

                                :options="$tiposEquipo->map(fn ($tipoEquipo) => [
                                    'value' => $tipoEquipo->id_tipo_equipo,
                                    'label' => $tipoEquipo->nombre,
                                ])->values()->all()"

                                label="Tipo de dispositivo *"
                                placeholder="Buscar tipo de dispositivo..."
                            />

                            @error('idTipoEquipo')

                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    @else

                        {{-- PAÍS DE ORIGEN --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                wire-model="idPaisOrigen"

                                :options="$paisesOrigen->map(fn ($pais) => [
                                    'value' => $pais->id_valor,
                                    'label' => $pais->nombre,
                                ])->values()->all()"

                                label="País de origen"
                                placeholder="Seleccionar un país..."
                            />

                            @error('idPaisOrigen')

                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ESPACIO VISUAL EN DESKTOP --}}
                        <div class="hidden xl:block"></div>

                    @endif


                    {{-- SITIO WEB --}}
                    <div class="min-w-0">

                        <label
                            for="catalog-web"

                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Enlace a sitio web
                        </label>

                        <input
                            id="catalog-web"
                            type="url"

                            wire:model="sitioWeb"

                            placeholder="https://..."

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

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                outline-none
                                transition-colors
                            "
                        >

                        @error('sitioWeb')

                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    @if ($tipoRegistro === 'modelo')

                        {{-- PAÍS DE ORIGEN HEREDADO --}}
                        <div class="min-w-0">

                            <label
                                class="
                                    block
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                    mb-1.5
                                "
                            >
                                País de origen
                            </label>

                            <input
                                type="text"

                                value="{{ $paisOrigenModelo ?: 'Se toma de la marca seleccionada' }}"

                                readonly

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

                                    cursor-not-allowed
                                "
                            >

                        </div>

                    @endif


                    {{-- DESCRIPCIÓN --}}
                    <div class="min-w-0 xl:col-span-3">

                        <label
                            for="catalog-description"

                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Descripción
                        </label>

                        <textarea
                            id="catalog-description"

                            wire:model="descripcion"

                            rows="3"

                            placeholder="Descripción de esta marca o modelo"

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

                                resize-y

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                outline-none
                                transition-colors
                            "
                        ></textarea>

                        @error('descripcion')

                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>
                </div>
            </section>


            {{-- ====================================================
                INFORMACIÓN ADICIONAL
            ==================================================== --}}
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
                    "
                >
                    {{-- PROVEEDOR SUGERIDO --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idProveedorSugerido"

                            :options="$proveedores->map(fn ($proveedor) => [
                                'value' => $proveedor->id_proveedor,
                                'label' => $proveedor->nombre,
                            ])->values()->all()"

                            label="Proveedor sugerido"
                            placeholder="Buscar proveedor..."
                        />

                        @error('idProveedorSugerido')

                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- GARANTÍA --}}
                    <div class="min-w-0">

                        <label
                            for="catalog-warranty"

                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Garantía estándar
                        </label>

                        <div class="relative">

                            <input
                                id="catalog-warranty"
                                type="number"

                                min="1"
                                max="600"

                                wire:model="garantiaEstandarMeses"

                                placeholder="Ej. 36"

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
                                    pr-16

                                    placeholder:text-[var(--theme-text-muted)]

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
                                "
                            >

                            <span
                                class="
                                    absolute
                                    right-3
                                    top-1/2
                                    -translate-y-1/2

                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                meses
                            </span>
                        </div>

                        @error('garantiaEstandarMeses')

                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- CONTACTO DEL PROVEEDOR --}}
                    <div class="min-w-0">

                        <label
                            class="
                                block
                                text-xs
                                text-[var(--theme-text-muted)]
                                mb-1.5
                            "
                        >
                            Contacto de proveedor
                        </label>

                        <input
                            type="text"

                            value="{{ $contactoProveedor ?: 'Selecciona un proveedor' }}"

                            readonly

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

                                cursor-not-allowed
                            "
                        >

                    </div>


                    @if ($tipoRegistro === 'modelo')

                        {{-- ÁREA DE USO COMÚN --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                wire-model="idAreaUsoComun"

                                :options="$areas->map(fn ($area) => [
                                    'value' => $area->id_area,
                                    'label' => $area->nombre,
                                ])->values()->all()"

                                label="Área de uso común"
                                placeholder="Buscar área..."
                            />

                            @error('idAreaUsoComun')

                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    @endif


                    {{-- COMENTARIOS --}}
                    <div
                        class="
                            min-w-0

                            {{ $tipoRegistro === 'modelo'
                                ? 'md:col-span-2 xl:col-span-2'
                                : 'md:col-span-2 xl:col-span-3'
                            }}
                        "
                    >
                        <label
                            for="catalog-comments"

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
                            id="catalog-comments"

                            wire:model="comentarios"

                            rows="4"

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

                                resize-y

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                outline-none
                                transition-colors
                            "
                        ></textarea>

                        @error('comentarios')

                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>
                </div>
            </section>


            {{-- ====================================================
                ACCIONES
            ==================================================== --}}
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
                    href="{{ route('catalogos.index') }}"

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
                    type="submit"

                    wire:loading.attr="disabled"
                    wire:target="save"

                    class="
                        w-full
                        sm:w-auto

                        h-11

                        flex
                        items-center
                        justify-center
                        gap-2

                        bg-[var(--theme-primary)]
                        hover:bg-[var(--theme-primary-hover)]

                        disabled:opacity-60
                        disabled:cursor-not-allowed

                        rounded-lg

                        px-5

                        text-sm
                        font-medium
                        text-white

                        transition-colors
                    "
                >
                    <svg
                        wire:loading.remove
                        wire:target="save"

                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M5 4h12l2 2v14H5z"/>
                        <path d="M8 4v6h8V4"/>
                        <path d="M8 20v-6h8v6"/>
                    </svg>


                    <svg
                        wire:loading
                        wire:target="save"

                        class="
                            w-4
                            h-4
                            animate-spin
                        "

                        viewBox="0 0 24 24"
                        fill="none"
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


                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        Guardar registro
                    </span>

                    <span
                        wire:loading
                        wire:target="save"
                    >
                        Guardando...
                    </span>
                </button>
            </div>
        </fieldset>


        {{-- ========================================================
            OVERLAY GUARDANDO
        ======================================================== --}}
        <div
            wire:loading.flex
            wire:target="save"

            class="
                fixed
                inset-0
                z-[140]

                items-center
                justify-center

                bg-[var(--theme-overlay)]

                backdrop-blur-[1px]
            "
        >
            <div
                class="
                    flex
                    items-center
                    gap-3

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl
                    shadow-lg

                    px-5
                    py-4
                "
            >
                <svg
                    class="
                        w-5
                        h-5

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
                        stroke-width="2"

                        opacity="0.2"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"

                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>


                <div>

                    <p
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Guardando registro...
                    </p>

                    <p
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]

                            mt-0.5
                        "
                    >
                        Espera un momento
                    </p>

                </div>
            </div>
        </div>
    </form>
</div>
</div>