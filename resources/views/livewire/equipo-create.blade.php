<div>

    <div
        x-data="equipmentCreate(@js($equipmentData))"
        wire:key="equipment-create-ui"
    >

        {{-- ============================================================
            AVISO DE ÉXITO
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

                    flex
                    items-center
                    justify-center

                    text-emerald-500
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
                        text-emerald-500
                    "
                >
                    Registro completado
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
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>
        </div>


        {{-- ============================================================
            AVISO DE ERROR
        ============================================================ --}}
        <div
            x-show="errorVisible"
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
                border-[var(--theme-danger)]

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

                    bg-[var(--theme-danger-soft)]

                    flex
                    items-center
                    justify-center

                    text-[var(--theme-danger)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 8v5"/>
                    <path d="M12 17h.01"/>
                    <circle cx="12" cy="12" r="9"/>
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
                    No se pudo guardar
                </p>

                <p
                    class="
                        mt-0.5
                        text-xs
                        text-[var(--theme-text)]
                    "
                    x-text="errorMessage"
                ></p>
            </div>

            <button
                type="button"
                @click="errorVisible = false"
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
            FORMULARIO
        ============================================================ --}}
        <form
            @submit.prevent="saveOptimistically()"
            class="relative"
        >
            <fieldset class="space-y-4 sm:space-y-6">

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
                                id="equipment-code-preview"
                                x-text="codigoInventarioPreview"
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
                            ></span>
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
                                for="equipment-name"
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
                                id="equipment-name"
                                type="text"

                                x-model="nombreEquipo"

                                @input="
                                    clearValidationError(
                                        'nombreEquipo'
                                    )
                                "

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

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
                                "
                            >

                            <p
                                x-show="fieldError('nombreEquipo')"
                                x-cloak
                                x-text="fieldError('nombreEquipo')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- HOST --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-host"
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
                                id="equipment-host"
                                type="text"

                                x-model="host"

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

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
                                "
                            >

                        </div>


                        {{-- ESTADO --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idEstadoActivo"
                                x-options="data.estados"

                                label="Estado *"
                                placeholder="Buscar estado..."
                            />

                            <p
                                x-show="fieldError('idEstadoActivo')"
                                x-cloak
                                x-text="fieldError('idEstadoActivo')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- TIPO DE EQUIPO --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idTipoEquipo"
                                x-options="data.tiposEquipo"

                                label="Tipo de equipo *"
                                placeholder="Buscar tipo de equipo..."
                            />

                            <p
                                x-show="fieldError('idTipoEquipo')"
                                x-cloak
                                x-text="fieldError('idTipoEquipo')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- MARCA --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idMarca"
                                x-options="data.marcas"

                                label="Marca *"
                                placeholder="Buscar marca..."
                            />

                            <p
                                x-show="fieldError('idMarca')"
                                x-cloak
                                x-text="fieldError('idMarca')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- MODELO --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idModelo"
                                x-options="modelosDisponibles"

                                label="Modelo"

                                placeholder="Elige marca y tipo primero"

                                x-placeholder="
                                    idMarca === ''
                                    || idTipoEquipo === ''
                                        ? 'Elige marca y tipo primero'
                                        : 'Buscar modelo...'
                                "

                                x-disabled="
                                    idMarca === ''
                                    || idTipoEquipo === ''
                                "
                            />

                        </div>


                        {{-- DIRECCIÓN MAC --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-mac"
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
                                id="equipment-mac"
                                type="text"

                                x-model="direccionMac"

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

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
                                "
                            >

                        </div>


                        {{-- FECHA DE COMPRA --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-purchase-date"
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
                                id="equipment-purchase-date"
                                type="date"

                                x-model="fechaCompra"

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

                        </div>


                        {{-- NÚMERO DE FACTURA --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-invoice"
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
                                id="equipment-invoice"
                                type="text"

                                x-model="numeroFactura"

                                @input="
                                    clearValidationError(
                                        'numeroFactura'
                                    )
                                "

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

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
                                "
                            >

                            <p
                                x-show="fieldError('numeroFactura')"
                                x-cloak
                                x-text="fieldError('numeroFactura')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- NÚMERO DE SERIE --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-serial"
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
                                id="equipment-serial"
                                type="text"

                                x-model="numeroSerie"

                                @input="
                                    clearValidationError(
                                        'numeroSerie'
                                    )
                                "

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

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
                                "
                            >

                            <p
                                x-show="fieldError('numeroSerie')"
                                x-cloak
                                x-text="fieldError('numeroSerie')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- PROVEEDOR --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idProveedor"
                                x-options="data.proveedores"

                                label="Proveedor"
                                placeholder="Buscar proveedor..."
                            />

                        </div>

                    </div>
                </section>


                {{-- ====================================================
                    CAMPOS DINÁMICOS
                ==================================================== --}}
                <section
                    id="equipment-child-fields"

                    x-show="hasChildFields"
                    x-cloak

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
                        Detalles de
                        <span x-text="selectedTypeLabel"></span>
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
                        <template
                            x-for="entry in childFieldEntries"
                            :key="entry.field"
                        >
                            <div
                                class="min-w-0"
                                :class="
                                    entry.type === 'boolean'
                                        ? 'flex items-center gap-2 md:pt-6'
                                        : ''
                                "
                            >

                                {{-- BOOLEAN --}}
                                <template
                                    x-if="entry.type === 'boolean'"
                                >
                                    <div
                                        class="
                                            flex
                                            items-center
                                            gap-2
                                        "
                                    >
                                        <input
                                            type="checkbox"

                                            :id="'cf_' + entry.field"

                                            x-model="
                                                childData[
                                                    entry.field
                                                ]
                                            "

                                            class="
                                                rounded

                                                border-[var(--theme-border-strong)]

                                                bg-[var(--theme-surface)]

                                                text-[var(--theme-primary)]

                                                focus:ring-[var(--theme-primary)]
                                            "
                                        >

                                        <label
                                            :for="'cf_' + entry.field"

                                            class="
                                                text-sm
                                                text-[var(--theme-text)]
                                            "

                                            x-text="
                                                fieldLabel(
                                                    entry.field
                                                )
                                            "
                                        ></label>
                                    </div>
                                </template>


                                {{-- CATÁLOGO --}}
                                <template
                                    x-if="
                                        isCatalogField(
                                            entry.type
                                        )
                                    "
                                >
                                    <div>
                                        <label
                                            class="
                                                block

                                                text-xs
                                                text-[var(--theme-text-muted)]

                                                mb-1.5
                                            "

                                            x-text="
                                                fieldLabel(
                                                    entry.field
                                                )
                                            "
                                        ></label>

                                        <x-searchable-select
                                            x-model="childData[entry.field]"

                                            x-options="
                                                dynamicCatalogOptions(
                                                    entry.type
                                                )
                                            "

                                            placeholder="Buscar..."
                                        />
                                    </div>
                                </template>


                                {{-- FECHA --}}
                                <template
                                    x-if="entry.type === 'date'"
                                >
                                    <div>
                                        <label
                                            class="
                                                block

                                                text-xs
                                                text-[var(--theme-text-muted)]

                                                mb-1.5
                                            "

                                            x-text="
                                                fieldLabel(
                                                    entry.field
                                                )
                                            "
                                        ></label>

                                        <input
                                            type="date"

                                            x-model="
                                                childData[
                                                    entry.field
                                                ]
                                            "

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
                                    </div>
                                </template>


                                {{-- NUMBER --}}
                                <template
                                    x-if="entry.type === 'number'"
                                >
                                    <div>
                                        <label
                                            class="
                                                block

                                                text-xs
                                                text-[var(--theme-text-muted)]

                                                mb-1.5
                                            "

                                            x-text="
                                                fieldLabel(
                                                    entry.field
                                                )
                                            "
                                        ></label>

                                        <input
                                            type="number"

                                            x-model="
                                                childData[
                                                    entry.field
                                                ]
                                            "

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
                                    </div>
                                </template>


                                {{-- TEXT --}}
                                <template
                                    x-if="entry.type === 'text'"
                                >
                                    <div>
                                        <label
                                            class="
                                                block

                                                text-xs
                                                text-[var(--theme-text-muted)]

                                                mb-1.5
                                            "

                                            x-text="
                                                fieldLabel(
                                                    entry.field
                                                )
                                            "
                                        ></label>

                                        <input
                                            type="text"

                                            x-model="
                                                childData[
                                                    entry.field
                                                ]
                                            "

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
                                    </div>
                                </template>

                            </div>
                        </template>
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

                            mb-5
                        "
                    >

                        {{-- PROPIETARIO --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-owner"
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
                                id="equipment-owner"
                                type="text"

                                x-model="propietario"

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

                                    focus:ring-1
                                    focus:ring-[var(--theme-primary)]
                                    focus:border-[var(--theme-primary)]

                                    outline-none
                                    transition-colors
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

                            <x-searchable-select
                                x-model="idCondicionActivo"
                                x-options="data.condiciones"

                                label="Estatus"
                                placeholder="Nuevo / Usado / Reparado..."
                            />

                        </div>


                        {{-- GARANTÍA --}}
                        <div class="min-w-0">

                            <label
                                for="equipment-warranty"
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
                                id="equipment-warranty"
                                type="date"

                                x-model="fechaFinGarantia"

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

                        </div>


                        {{-- ÁREA --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idArea"
                                x-options="data.areas"

                                label="Área *"
                                placeholder="Buscar área..."
                            />

                            <p
                                x-show="fieldError('idArea')"
                                x-cloak
                                x-text="fieldError('idArea')"
                                class="
                                    mt-1
                                    text-xs
                                    text-[var(--theme-danger)]
                                "
                            ></p>

                        </div>


                        {{-- DEPARTAMENTO --}}
                        <div class="min-w-0">

                            <x-searchable-select
                                x-model="idDepartamento"
                                x-options="departamentosDisponibles"

                                label="Departamento"

                                placeholder="Elige un área primero"

                                x-placeholder="
                                    idArea === ''
                                        ? 'Elige un área primero'
                                        : 'Buscar departamento...'
                                "

                                x-disabled="idArea === ''"
                            />

                        </div>

                    </div>


                    {{-- COMENTARIOS --}}
                    <div>

                        <label
                            for="equipment-comments"
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
                            id="equipment-comments"

                            x-model="comentarios"

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
                        type="submit"

                        :disabled="savingVisual"

                        :aria-busy="
                            savingVisual
                                ? 'true'
                                : 'false'
                        "

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

                        {{-- ICONO NORMAL --}}
                        <svg
                            x-show="!savingVisual"

                            class="w-4 h-4"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 5v14"/>
                            <path d="M5 12h14"/>
                        </svg>


                        {{-- SPINNER --}}
                        <svg
                            x-show="savingVisual"
                            x-cloak

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
                            x-text="
                                savingVisual
                                    ? 'Guardando...'
                                    : 'Añadir equipo'
                            "
                        ></span>

                    </button>
                </div>

            </fieldset>
        </form>

    </div>


    {{-- ============================================================
        ALPINE
    ============================================================ --}}
    @script
        <script>
            Alpine.data(
                'equipmentCreate',
                (data) => ({
                    data: data ?? {},

                    successVisible: false,
                    successMessage: '',
                    successTimer: null,

                    errorVisible: false,
                    errorMessage: '',
                    errorTimer: null,

                    savingVisual: false,
                    spinnerTimer: null,

                    validationErrors: {},

                    suspendDependencies: false,

                    codigoInventarioPreview:
                        String(
                            data?.defaults
                                ?.codigoInventarioPreview
                            ?? ''
                        ),

                    nombreEquipo: '',
                    host: '',

                    idEstadoActivo: '',
                    idTipoEquipo: '',
                    idModelo: '',

                    fechaCompra: '',
                    idMarca: '',
                    direccionMac: '',
                    numeroFactura: '',
                    numeroSerie: '',
                    idProveedor: '',

                    propietario: '',
                    idCondicionActivo: '',
                    idArea: '',
                    idDepartamento: '',
                    fechaFinGarantia: '',
                    comentarios: '',

                    childData: {},


                    init() {
                        this.$watch(
                            'idEstadoActivo',
                            () => {
                                this.clearValidationError(
                                    'idEstadoActivo'
                                );
                            }
                        );

                        this.$watch(
                            'idTipoEquipo',
                            (value, previous) => {
                                if (
                                    this.suspendDependencies
                                ) {
                                    return;
                                }

                                if (
                                    String(
                                        value ?? ''
                                    )
                                    ===
                                    String(
                                        previous ?? ''
                                    )
                                ) {
                                    return;
                                }

                                this.idModelo = '';
                                this.childData = {};

                                this.clearValidationError(
                                    'idTipoEquipo'
                                );
                            }
                        );

                        this.$watch(
                            'idMarca',
                            (value, previous) => {
                                if (
                                    this.suspendDependencies
                                ) {
                                    return;
                                }

                                if (
                                    String(
                                        value ?? ''
                                    )
                                    ===
                                    String(
                                        previous ?? ''
                                    )
                                ) {
                                    return;
                                }

                                this.idModelo = '';

                                this.clearValidationError(
                                    'idMarca'
                                );
                            }
                        );

                        this.$watch(
                            'idArea',
                            (value, previous) => {
                                if (
                                    this.suspendDependencies
                                ) {
                                    return;
                                }

                                if (
                                    String(
                                        value ?? ''
                                    )
                                    ===
                                    String(
                                        previous ?? ''
                                    )
                                ) {
                                    return;
                                }

                                this.idDepartamento = '';

                                this.clearValidationError(
                                    'idArea'
                                );
                            }
                        );
                    },


                    get modelosDisponibles() {
                        const marcaId =
                            String(
                                this.idMarca ?? ''
                            );

                        const tipoId =
                            String(
                                this.idTipoEquipo ?? ''
                            );

                        if (
                            marcaId === ''
                            || tipoId === ''
                        ) {
                            return [];
                        }

                        return (
                            this.data?.modelos
                            ?? []
                        ).filter(
                            modelo =>
                                String(
                                    modelo?.idMarca
                                    ?? ''
                                ) === marcaId
                                &&
                                String(
                                    modelo?.idTipoEquipo
                                    ?? ''
                                ) === tipoId
                        );
                    },


                    get departamentosDisponibles() {
                        const areaId =
                            String(
                                this.idArea ?? ''
                            );

                        if (areaId === '') {
                            return [];
                        }

                        return (
                            this.data
                                ?.departamentos
                            ?? []
                        ).filter(
                            departamento =>
                                String(
                                    departamento
                                        ?.idArea
                                    ?? ''
                                ) === areaId
                        );
                    },


                    get selectedTypeLabel() {
                        const tipoId =
                            String(
                                this.idTipoEquipo
                                ?? ''
                            );

                        if (tipoId === '') {
                            return '';
                        }

                        const tipo =
                            (
                                this.data
                                    ?.tiposEquipo
                                ?? []
                            ).find(
                                item =>
                                    String(
                                        item?.value
                                        ?? ''
                                    ) === tipoId
                            );

                        return String(
                            tipo?.label
                            ?? ''
                        );
                    },


                    get childFields() {
                        const tipoId =
                            String(
                                this.idTipoEquipo
                                ?? ''
                            );

                        if (tipoId === '') {
                            return {};
                        }

                        return (
                            this.data
                                ?.childFieldsByType
                                ?.[tipoId]
                            ?? {}
                        );
                    },


                    get childFieldEntries() {
                        return Object.entries(
                            this.childFields
                        ).map(
                            ([field, type]) => ({
                                field,
                                type,
                            })
                        );
                    },


                    get hasChildFields() {
                        return (
                            this.childFieldEntries
                                .length > 0
                        );
                    },


                    fieldLabel(field) {
                        return String(
                            this.data
                                ?.fieldLabels
                                ?.[field]
                            ?? field
                        );
                    },


                    isCatalogField(type) {
                        return (
                            typeof type
                                === 'string'
                            &&
                            type.startsWith(
                                'catalog:'
                            )
                        );
                    },


                    catalogKey(type) {
                        if (
                            !this.isCatalogField(
                                type
                            )
                        ) {
                            return '';
                        }

                        return type.substring(
                            8
                        );
                    },


                    dynamicCatalogOptions(type) {
                        const key =
                            this.catalogKey(
                                type
                            );

                        if (key === '') {
                            return [];
                        }

                        return (
                            this.data
                                ?.dynamicCatalogs
                                ?.[key]
                            ?? []
                        );
                    },


                    clearValidationError(field) {
                        if (
                            !this.validationErrors
                            ||
                            typeof this
                                .validationErrors
                                !== 'object'
                            ||
                            !Object
                                .prototype
                                .hasOwnProperty
                                .call(
                                    this
                                        .validationErrors,
                                    field
                                )
                        ) {
                            return;
                        }

                        const errors = {
                            ...this
                                .validationErrors
                        };

                        delete errors[field];

                        this.validationErrors =
                            errors;
                    },


                    showSuccess(message) {
                        this.successMessage =
                            message
                            || 'Equipo añadido correctamente.';

                        this.successVisible =
                            true;

                        if (
                            this.successTimer
                        ) {
                            clearTimeout(
                                this.successTimer
                            );
                        }

                        this.successTimer =
                            setTimeout(
                                () => {
                                    this.successVisible =
                                        false;
                                },
                                3200
                            );
                    },


                    showError(message) {
                        this.errorMessage =
                            message
                            || 'No fue posible guardar el equipo.';

                        this.errorVisible =
                            true;

                        if (
                            this.errorTimer
                        ) {
                            clearTimeout(
                                this.errorTimer
                            );
                        }

                        this.errorTimer =
                            setTimeout(
                                () => {
                                    this.errorVisible =
                                        false;
                                },
                                5000
                            );
                    },


                    firstError(error) {
                        const errors =
                            error?.errors
                            ?? null;

                        if (
                            !errors
                            ||
                            typeof errors
                                !== 'object'
                        ) {
                            return null;
                        }

                        const keys =
                            Object.keys(
                                errors
                            );

                        if (
                            keys.length === 0
                        ) {
                            return null;
                        }

                        const value =
                            errors[
                                keys[0]
                            ];

                        if (
                            Array.isArray(
                                value
                            )
                        ) {
                            return (
                                value[0]
                                ?? null
                            );
                        }

                        return (
                            typeof value
                                === 'string'
                                ? value
                                : null
                        );
                    },


                    fieldError(field) {
                        const value =
                            this
                                .validationErrors
                                ?.[field];

                        if (!value) {
                            return null;
                        }

                        if (
                            Array.isArray(
                                value
                            )
                        ) {
                            return (
                                value[0]
                                ?? null
                            );
                        }

                        return (
                            typeof value
                                === 'string'
                                ? value
                                : null
                        );
                    },


                    clone(value) {
                        return JSON.parse(
                            JSON.stringify(
                                value ?? {}
                            )
                        );
                    },


                    captureSnapshot() {
                        return {
                            nombreEquipo:
                                String(
                                    this
                                        .nombreEquipo
                                    ?? ''
                                ),

                            host:
                                String(
                                    this.host
                                    ?? ''
                                ),

                            idEstadoActivo:
                                String(
                                    this
                                        .idEstadoActivo
                                    ?? ''
                                ),

                            idTipoEquipo:
                                String(
                                    this
                                        .idTipoEquipo
                                    ?? ''
                                ),

                            idModelo:
                                String(
                                    this.idModelo
                                    ?? ''
                                ),

                            fechaCompra:
                                String(
                                    this
                                        .fechaCompra
                                    ?? ''
                                ),

                            idMarca:
                                String(
                                    this.idMarca
                                    ?? ''
                                ),

                            direccionMac:
                                String(
                                    this
                                        .direccionMac
                                    ?? ''
                                ),

                            numeroFactura:
                                String(
                                    this
                                        .numeroFactura
                                    ?? ''
                                ),

                            numeroSerie:
                                String(
                                    this
                                        .numeroSerie
                                    ?? ''
                                ),

                            idProveedor:
                                String(
                                    this
                                        .idProveedor
                                    ?? ''
                                ),

                            propietario:
                                String(
                                    this
                                        .propietario
                                    ?? ''
                                ),

                            idCondicionActivo:
                                String(
                                    this
                                        .idCondicionActivo
                                    ?? ''
                                ),

                            idArea:
                                String(
                                    this.idArea
                                    ?? ''
                                ),

                            idDepartamento:
                                String(
                                    this
                                        .idDepartamento
                                    ?? ''
                                ),

                            fechaFinGarantia:
                                String(
                                    this
                                        .fechaFinGarantia
                                    ?? ''
                                ),

                            comentarios:
                                String(
                                    this
                                        .comentarios
                                    ?? ''
                                ),

                            childData:
                                this.clone(
                                    this.childData
                                ),
                        };
                    },


                    validateSnapshot(
                        payload
                    ) {
                        const errors = {};

                        if (
                            String(
                                payload
                                    .nombreEquipo
                                ?? ''
                            ).trim() === ''
                        ) {
                            errors
                                .nombreEquipo = [
                                    'El nombre del equipo es obligatorio.'
                                ];
                        }

                        if (
                            String(
                                payload
                                    .idEstadoActivo
                                ?? ''
                            ) === ''
                        ) {
                            errors
                                .idEstadoActivo = [
                                    'Selecciona un estado.'
                                ];
                        }

                        if (
                            String(
                                payload
                                    .idTipoEquipo
                                ?? ''
                            ) === ''
                        ) {
                            errors
                                .idTipoEquipo = [
                                    'Selecciona un tipo de equipo.'
                                ];
                        }

                        if (
                            String(
                                payload.idMarca
                                ?? ''
                            ) === ''
                        ) {
                            errors
                                .idMarca = [
                                    'Selecciona una marca.'
                                ];
                        }

                        if (
                            String(
                                payload
                                    .numeroFactura
                                ?? ''
                            ).trim() === ''
                        ) {
                            errors
                                .numeroFactura = [
                                    'El número de factura es obligatorio.'
                                ];
                        }

                        if (
                            String(
                                payload
                                    .numeroSerie
                                ?? ''
                            ).trim() === ''
                        ) {
                            errors
                                .numeroSerie = [
                                    'El número de serie es obligatorio.'
                                ];
                        }

                        if (
                            String(
                                payload.idArea
                                ?? ''
                            ) === ''
                        ) {
                            errors.idArea = [
                                'Selecciona un área.'
                            ];
                        }

                        this.validationErrors =
                            errors;

                        const keys =
                            Object.keys(
                                errors
                            );

                        if (
                            keys.length === 0
                        ) {
                            return true;
                        }

                        const first =
                            errors[
                                keys[0]
                            ];

                        this.showError(
                            Array.isArray(
                                first
                            )
                                ? first[0]
                                : 'Revisa los campos requeridos.'
                        );

                        return false;
                    },


                    pulseSpinner() {
                        this.savingVisual =
                            true;

                        if (
                            this.spinnerTimer
                        ) {
                            clearTimeout(
                                this.spinnerTimer
                            );
                        }

                        this.spinnerTimer =
                            setTimeout(
                                () => {
                                    this.savingVisual =
                                        false;
                                },
                                350
                            );
                    },


                    clearFormInstantly() {
                        this.validationErrors =
                            {};

                        this.suspendDependencies =
                            true;

                        this.nombreEquipo = '';
                        this.host = '';

                        this.idEstadoActivo = '';
                        this.idTipoEquipo = '';
                        this.idModelo = '';

                        this.fechaCompra = '';
                        this.idMarca = '';
                        this.direccionMac = '';
                        this.numeroFactura = '';
                        this.numeroSerie = '';
                        this.idProveedor = '';

                        this.propietario = '';
                        this.idCondicionActivo = '';
                        this.idArea = '';
                        this.idDepartamento = '';
                        this.fechaFinGarantia = '';
                        this.comentarios = '';

                        this.childData = {};

                        this.$nextTick(
                            () => {
                                this.suspendDependencies =
                                    false;
                            }
                        );
                    },


                    formIsStillEmpty() {
                        const values = [
                            this.nombreEquipo,
                            this.host,

                            this.idEstadoActivo,
                            this.idTipoEquipo,
                            this.idModelo,

                            this.fechaCompra,
                            this.idMarca,
                            this.direccionMac,
                            this.numeroFactura,
                            this.numeroSerie,
                            this.idProveedor,

                            this.propietario,
                            this.idCondicionActivo,
                            this.idArea,
                            this.idDepartamento,
                            this.fechaFinGarantia,
                            this.comentarios,
                        ];

                        const hasValue =
                            values.some(
                                value =>
                                    value
                                        !== null
                                    &&
                                    value
                                        !== undefined
                                    &&
                                    String(
                                        value
                                    ).trim()
                                        !== ''
                            );

                        if (hasValue) {
                            return false;
                        }

                        return (
                            Object.keys(
                                this.childData
                                ?? {}
                            ).length === 0
                        );
                    },


                    restoreSnapshot(
                        payload
                    ) {
                        this.suspendDependencies =
                            true;

                        this.nombreEquipo =
                            payload
                                .nombreEquipo
                            ?? '';

                        this.host =
                            payload.host
                            ?? '';

                        this.idEstadoActivo =
                            payload
                                .idEstadoActivo
                            ?? '';

                        this.idTipoEquipo =
                            payload
                                .idTipoEquipo
                            ?? '';

                        this.idModelo =
                            payload.idModelo
                            ?? '';

                        this.fechaCompra =
                            payload
                                .fechaCompra
                            ?? '';

                        this.idMarca =
                            payload.idMarca
                            ?? '';

                        this.direccionMac =
                            payload
                                .direccionMac
                            ?? '';

                        this.numeroFactura =
                            payload
                                .numeroFactura
                            ?? '';

                        this.numeroSerie =
                            payload
                                .numeroSerie
                            ?? '';

                        this.idProveedor =
                            payload
                                .idProveedor
                            ?? '';

                        this.propietario =
                            payload
                                .propietario
                            ?? '';

                        this.idCondicionActivo =
                            payload
                                .idCondicionActivo
                            ?? '';

                        this.idArea =
                            payload.idArea
                            ?? '';

                        this.idDepartamento =
                            payload
                                .idDepartamento
                            ?? '';

                        this.fechaFinGarantia =
                            payload
                                .fechaFinGarantia
                            ?? '';

                        this.comentarios =
                            payload
                                .comentarios
                            ?? '';

                        this.childData =
                            this.clone(
                                payload
                                    .childData
                                ?? {}
                            );

                        this.$nextTick(
                            () => {
                                this.suspendDependencies =
                                    false;
                            }
                        );
                    },


                    async saveOptimistically() {
                        const payload =
                            this.captureSnapshot();

                        if (
                            !this.validateSnapshot(
                                payload
                            )
                        ) {
                            return;
                        }

                        this.errorVisible =
                            false;

                        this.pulseSpinner();

                        let request;

                        try {
                            request =
                                $wire.save(
                                    payload
                                );

                        } catch (error) {
                            this.savingVisual =
                                false;

                            this.showError(
                                'No fue posible iniciar el guardado.'
                            );

                            return;
                        }

                        /*
                         * Limpieza inmediata.
                         *
                         * El navegador queda listo para capturar
                         * otro equipo mientras termina PostgreSQL.
                         */
                        this.clearFormInstantly();

                        try {
                            const result =
                                await request;

                            if (
                                !result
                                ||
                                result.ok !== true
                            ) {
                                throw new Error(
                                    'Respuesta de guardado no válida.'
                                );
                            }

                            if (
                                result.nextCode
                            ) {
                                this
                                    .codigoInventarioPreview =
                                    String(
                                        result
                                            .nextCode
                                    );
                            }

                            this.showSuccess(
                                result.message
                                || 'Equipo añadido correctamente.'
                            );

                        } catch (error) {
                            const serverErrors =
                                error?.errors
                                ?? {};

                            const validationMessage =
                                this.firstError(
                                    error
                                );

                            /*
                             * Si el usuario no empezó otro registro,
                             * restauramos la fotografía que falló.
                             */
                            if (
                                this.formIsStillEmpty()
                            ) {
                                this.restoreSnapshot(
                                    payload
                                );

                                this
                                    .validationErrors =
                                    serverErrors;

                            } else {
                                /*
                                 * No pisamos un registro nuevo
                                 * que el usuario ya esté capturando.
                                 */
                                this
                                    .validationErrors =
                                    {};
                            }

                            this.showError(
                                validationMessage
                                || 'No fue posible guardar el equipo. Inténtalo nuevamente.'
                            );
                        }
                    },
                })
            );
        </script>
    @endscript

</div>