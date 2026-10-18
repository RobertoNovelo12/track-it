<div>

    {{-- ============================================================
        MENSAJE DE ÉXITO
    ============================================================ --}}
    @if ($successMessage)

        <div
            wire:key="catalog-success-{{ $successVersion }}"

            x-data="{
                visible: true
            }"

            x-init="
                setTimeout(() => {
                    visible = false;
                }, 3200)
            "

            x-show="visible"
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
            {{-- ICONO --}}
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

                    aria-hidden="true"
                >
                    <path d="M5 12l4 4L19 7"/>
                </svg>
            </div>


            {{-- TEXTO --}}
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-semibold
                        text-emerald-500
                    "
                >
                    Cambios guardados
                </p>

                <p
                    class="
                        mt-0.5

                        text-xs
                        text-[var(--theme-text)]
                    "
                >
                    {{ $successMessage }}
                </p>

            </div>


            {{-- CERRAR --}}
            <button
                type="button"

                @click="visible = false"

                class="
                    shrink-0

                    text-emerald-500/60

                    hover:text-emerald-500

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

                    aria-hidden="true"
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>

        </div>

    @endif


    {{-- ============================================================
        CABECERA (mismo diseño que Nueva Alta de equipo)
    ============================================================ --}}
    <x-form.page-header
        :title="$tipo === 'marca' ? 'Editar marca' : 'Editar modelo'"
        :crumbs="[
            ['label' => 'Administración', 'href' => route('catalogos.index')],
            ['label' => $tipo === 'marca' ? 'Marcas' : 'Modelos', 'href' => route('catalogos.index', ['section' => $tipo === 'marca' ? 'marcas' : 'modelos'])],
            ['label' => 'Editar'],
        ]"
    />


    {{-- ============================================================
        FORMULARIO

        IMPORTANTE:
        ESTE FORMULARIO DEBE ESTAR FUERA DEL @if ($successMessage)
    ============================================================ --}}
    <form
        wire:submit.prevent="save"

        class="relative"
    >
        <fieldset
            wire:loading.attr="disabled"
            wire:target="save"

            class="
                space-y-4
                sm:space-y-6
            "
        >

            {{-- ====================================================
                INFORMACIÓN GENERAL
            ==================================================== --}}
            <section
                class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
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
                            Código interno:
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
                            {{ $codigoInterno }}
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
                    {{-- TIPO DE REGISTRO --}}
                    <div class="min-w-0">

                        <label
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Tipo de registro
                        </label>

                        <input
                            type="text"

                            value="{{ $tipo === 'marca' ? 'Marca' : 'Modelo' }}"

                            readonly

                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] cursor-not-allowed"
                        >

                    </div>


                    {{-- ID INTERNO --}}
                    <div class="min-w-0">

                        <label
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Número de inventario (ID interno)
                        </label>

                        <input
                            type="text"

                            value="{{ $registroId }}"

                            readonly

                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] cursor-not-allowed"
                        >

                    </div>


                    {{-- NOMBRE --}}
                    <div class="min-w-0">

                        <label
                            for="catalog-name"

                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            {{ $tipo === 'marca'
                                ? 'Nombre de la marca'
                                : 'Nombre del modelo'
                            }} *
                        </label>

                        <input
                            id="catalog-name"
                            type="text"

                            wire:model="nombre"

                            placeholder="{{ $tipo === 'marca'
                                ? 'Ej. Apple'
                                : 'Ej. Latitude 5440'
                            }}"

                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                        >

                        @error('nombre')
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


                    {{-- PAÍS / DATOS MODELO --}}
                    @if ($tipo === 'marca')

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

                    @else

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

                    @endif


                    {{-- SITIO WEB --}}
                    <div class="min-w-0">

                        <label
                            for="catalog-web"

                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Enlace a sitio web
                        </label>

                        <input
                            id="catalog-web"
                            type="url"

                            wire:model="sitioWeb"

                            placeholder="https://..."

                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                        >

                        @error('sitioWeb')
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


                    {{-- DESCRIPCIÓN --}}
                    <div
                        class="
                            min-w-0

                            md:col-span-2
                            xl:col-span-3
                        "
                    >
                        <label
                            for="catalog-description"

                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Descripción
                        </label>

                        <textarea
                            id="catalog-description"

                            wire:model="descripcion"

                            rows="3"

                            placeholder="Descripción de esta marca o modelo"

                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-y"
                        ></textarea>

                        @error('descripcion')
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

            </section>


            {{-- ====================================================
                INFORMACIÓN ADICIONAL
            ==================================================== --}}
            <section
                class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
            >
                <h2
                    class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]"
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
                    {{-- PROVEEDOR --}}
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


                    {{-- GARANTÍA --}}
                    <div class="min-w-0">

                        <label
                            for="catalog-warranty"

                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
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

                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] pr-16"
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


                    @if ($tipo === 'modelo')

                        {{-- ÁREA --}}
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

                    @endif


                    {{-- COMENTARIOS --}}
                    <div
                        class="
                            min-w-0

                            md:col-span-2
                            xl:col-span-3
                        "
                    >
                        <label
                            for="catalog-comments"

                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Comentarios
                        </label>

                        <textarea
                            id="catalog-comments"

                            wire:model="comentarios"

                            rows="4"

                            placeholder="Escribe algún comentario adicional..."

                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-y"
                        ></textarea>

                        @error('comentarios')
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

                    class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                >
                    Cancelar
                </a>


                <button
                    type="submit"

                    wire:loading.attr="disabled"
                    wire:target="save"

                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                    {{-- ESTADO NORMAL --}}
                    <span
                        wire:loading.remove
                        wire:target="save"

                        class="
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                        "
                    >
                        <svg
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

                        <span>
                            Guardar cambios
                        </span>
                    </span>


                    {{-- ESTADO GUARDANDO --}}
                    <span
                        wire:loading.flex
                        wire:target="save"

                        class="
                            absolute
                            inset-0

                            items-center
                            justify-center
                        "
                    >
                        <svg
                            class="
                                w-5
                                h-5

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

                        <span class="sr-only">
                            Guardando cambios...
                        </span>
                    </span>

                </button>

            </div>

        </fieldset>

    </form>

</div>