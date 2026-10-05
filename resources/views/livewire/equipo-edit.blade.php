<div>

    <div
        x-data="equipmentEdit(@js($equipmentData))"
        wire:key="equipment-edit-ui"
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
                    Cambios guardados
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
            MENSAJE DE ERROR
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
                    text-[var(--theme-danger)]
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
                    No se pudieron guardar los cambios
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
                    cursor-default
                "
                title="Función no habilitada por el momento"
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
                    cursor-default
                "
                title="Función no habilitada por el momento"
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
                                    rounded-md
                                    bg-[var(--theme-primary-soft)]
                                    px-2.5
                                    py-1
                                    font-semibold
                                    text-[var(--theme-primary)]
                                "
                                x-text="codigoInventario"
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
                                for="edit-equipment-name"
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
                                id="edit-equipment-name"
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
                                for="edit-equipment-host"
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
                                id="edit-equipment-host"
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


                        {{-- TIPO --}}
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


                        {{-- CÓDIGO INVENTARIO --}}
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
                                    :value="codigoInventario"
                                    disabled
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
                                for="edit-equipment-purchase-date"
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
                                id="edit-equipment-purchase-date"
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


                        {{-- FACTURA --}}
                        <div class="min-w-0">
                            <label
                                for="edit-equipment-invoice"
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
                                id="edit-equipment-invoice"
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
                                for="edit-equipment-serial"
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
                                id="edit-equipment-serial"
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


                        {{-- ESTATUS / CONDICIÓN --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                x-model="idCondicionActivo"
                                x-options="data.condiciones"
                                label="Estatus"
                                placeholder="Nuevo / Usado / Reparado..."
                            />
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


                        {{-- MAC --}}
                        <div class="min-w-0">
                            <label
                                for="edit-equipment-mac"
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
                                id="edit-equipment-mac"
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

                    </div>
                </section>


                {{-- ====================================================
                    CAMPOS DINÁMICOS
                ==================================================== --}}
                <section
                    id="edit-equipment-child-fields"
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
                                            :id="'edit_cf_' + entry.field"
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
                                            :for="'edit_cf_' + entry.field"
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


                                {{-- NÚMERO --}}
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


                                {{-- TEXTO --}}
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
                                :value="propietario"
                                readonly
                                placeholder="Sin responsable"
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


                        {{-- GARANTÍA --}}
                        <div class="min-w-0">
                            <label
                                for="edit-equipment-warranty"
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
                                id="edit-equipment-warranty"
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

                    </div>


                    {{-- COMENTARIOS --}}
                    <div>
                        <label
                            for="edit-equipment-comments"
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
                            id="edit-equipment-comments"
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
                        <svg
                            x-show="!savingVisual"
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
                                    : 'Guardar cambios'
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
                'equipmentEdit',
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

                    requestPending: false,
                    queuedPayload: null,

                    validationErrors: {},

                    /*
                     * Evita que los watchers limpien los valores
                     * iniciales cuando Alpine monta los selects.
                     */
                    suspendDependencies: true,

                    equipoId:
                        Number(
                            data?.initial
                                ?.equipoId
                            ?? 0
                        ),

                    codigoInventario:
                        String(
                            data?.initial
                                ?.codigoInventario
                            ?? ''
                        ),

                    tipoEquipoOriginal:
                        String(
                            data?.initial
                                ?.tipoEquipoOriginal
                            ?? ''
                        ),

                    nombreEquipo:
                        String(
                            data?.initial
                                ?.nombreEquipo
                            ?? ''
                        ),

                    host:
                        String(
                            data?.initial
                                ?.host
                            ?? ''
                        ),

                    idEstadoActivo:
                        String(
                            data?.initial
                                ?.idEstadoActivo
                            ?? ''
                        ),

                    idTipoEquipo:
                        String(
                            data?.initial
                                ?.idTipoEquipo
                            ?? ''
                        ),

                    idModelo:
                        String(
                            data?.initial
                                ?.idModelo
                            ?? ''
                        ),

                    fechaCompra:
                        String(
                            data?.initial
                                ?.fechaCompra
                            ?? ''
                        ),

                    idMarca:
                        String(
                            data?.initial
                                ?.idMarca
                            ?? ''
                        ),

                    direccionMac:
                        String(
                            data?.initial
                                ?.direccionMac
                            ?? ''
                        ),

                    numeroFactura:
                        String(
                            data?.initial
                                ?.numeroFactura
                            ?? ''
                        ),

                    numeroSerie:
                        String(
                            data?.initial
                                ?.numeroSerie
                            ?? ''
                        ),

                    idProveedor:
                        String(
                            data?.initial
                                ?.idProveedor
                            ?? ''
                        ),

                    propietario:
                        String(
                            data?.initial
                                ?.propietario
                            ?? ''
                        ),

                    idCondicionActivo:
                        String(
                            data?.initial
                                ?.idCondicionActivo
                            ?? ''
                        ),

                    idArea:
                        String(
                            data?.initial
                                ?.idArea
                            ?? ''
                        ),

                    idDepartamento:
                        String(
                            data?.initial
                                ?.idDepartamento
                            ?? ''
                        ),

                    fechaFinGarantia:
                        String(
                            data?.initial
                                ?.fechaFinGarantia
                            ?? ''
                        ),

                    comentarios:
                        String(
                            data?.initial
                                ?.comentarios
                            ?? ''
                        ),

                    childData:
                        JSON.parse(
                            JSON.stringify(
                                data?.initial
                                    ?.childData
                                ?? {}
                            )
                        ),


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

                                /*
                                 * Al cambiar realmente de tipo:
                                 *
                                 * - el modelo deja de ser válido
                                 * - los campos específicos anteriores
                                 *   dejan de pertenecer al nuevo tipo
                                 */
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

                        /*
                         * Los x-searchable-select terminan de enlazar
                         * su x-model durante el primer ciclo de Alpine.
                         *
                         * Hasta entonces no permitimos limpiezas
                         * dependientes.
                         */
                        this.$nextTick(
                            () => {
                                this.suspendDependencies =
                                    false;
                            }
                        );
                    },


                    get modelosDisponibles() {
                        const marcaId =
                            String(
                                this.idMarca
                                ?? ''
                            );

                        const tipoId =
                            String(
                                this.idTipoEquipo
                                ?? ''
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
                                this.idArea
                                ?? ''
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
                            || 'Los cambios se guardaron correctamente.';

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
                            || 'No fue posible guardar los cambios.';

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
                                    this.nombreEquipo
                                    ?? ''
                                ),

                            host:
                                String(
                                    this.host
                                    ?? ''
                                ),

                            idEstadoActivo:
                                String(
                                    this.idEstadoActivo
                                    ?? ''
                                ),

                            idTipoEquipo:
                                String(
                                    this.idTipoEquipo
                                    ?? ''
                                ),

                            idModelo:
                                String(
                                    this.idModelo
                                    ?? ''
                                ),

                            fechaCompra:
                                String(
                                    this.fechaCompra
                                    ?? ''
                                ),

                            idMarca:
                                String(
                                    this.idMarca
                                    ?? ''
                                ),

                            direccionMac:
                                String(
                                    this.direccionMac
                                    ?? ''
                                ),

                            numeroFactura:
                                String(
                                    this.numeroFactura
                                    ?? ''
                                ),

                            numeroSerie:
                                String(
                                    this.numeroSerie
                                    ?? ''
                                ),

                            idProveedor:
                                String(
                                    this.idProveedor
                                    ?? ''
                                ),

                            /*
                             * Informativo.
                             * El backend no cambia asignaciones aquí.
                             */
                            propietario:
                                String(
                                    this.propietario
                                    ?? ''
                                ),

                            idCondicionActivo:
                                String(
                                    this.idCondicionActivo
                                    ?? ''
                                ),

                            idArea:
                                String(
                                    this.idArea
                                    ?? ''
                                ),

                            idDepartamento:
                                String(
                                    this.idDepartamento
                                    ?? ''
                                ),

                            fechaFinGarantia:
                                String(
                                    this.fechaFinGarantia
                                    ?? ''
                                ),

                            comentarios:
                                String(
                                    this.comentarios
                                    ?? ''
                                ),

                            childData:
                                this.clone(
                                    this.childData
                                ),
                        };
                    },


                    validateSnapshot(payload) {
                        const errors = {};

                        if (
                            String(
                                payload.nombreEquipo
                                ?? ''
                            ).trim() === ''
                        ) {
                            errors.nombreEquipo = [
                                'El nombre del equipo es obligatorio.'
                            ];
                        }

                        if (
                            String(
                                payload.idEstadoActivo
                                ?? ''
                            ) === ''
                        ) {
                            errors.idEstadoActivo = [
                                'Selecciona un estado.'
                            ];
                        }

                        if (
                            String(
                                payload.idTipoEquipo
                                ?? ''
                            ) === ''
                        ) {
                            errors.idTipoEquipo = [
                                'Selecciona un tipo de equipo.'
                            ];
                        }

                        if (
                            String(
                                payload.idMarca
                                ?? ''
                            ) === ''
                        ) {
                            errors.idMarca = [
                                'Selecciona una marca.'
                            ];
                        }

                        if (
                            String(
                                payload.numeroFactura
                                ?? ''
                            ).trim() === ''
                        ) {
                            errors.numeroFactura = [
                                'El número de factura es obligatorio.'
                            ];
                        }

                        if (
                            String(
                                payload.numeroSerie
                                ?? ''
                            ).trim() === ''
                        ) {
                            errors.numeroSerie = [
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


                    async sendPayload(payload) {
                        this.requestPending =
                            true;

                        try {
                            const result =
                                await $wire.save(
                                    payload
                                );

                            if (
                                !result
                                ||
                                result.ok !== true
                            ) {
                                throw new Error(
                                    'Respuesta de guardado no válida.'
                                );
                            }

                            this.validationErrors =
                                {};

                            /*
                             * El servidor ya persistió el nuevo tipo.
                             */
                            if (
                                result?.record
                                    ?.typeId
                                !== undefined
                                &&
                                result?.record
                                    ?.typeId
                                !== null
                            ) {
                                this.tipoEquipoOriginal =
                                    String(
                                        result
                                            .record
                                            .typeId
                                    );
                            }

                            /*
                             * Si existe otro snapshot esperando,
                             * mostramos éxito cuando termine el último.
                             */
                            if (
                                !this.queuedPayload
                            ) {
                                this.showSuccess(
                                    result.message
                                    || 'Los cambios se guardaron correctamente.'
                                );
                            }

                        } catch (error) {
                            this.validationErrors =
                                error?.errors
                                ?? {};

                            const validationMessage =
                                this.firstError(
                                    error
                                );

                            this.showError(
                                validationMessage
                                || 'No fue posible guardar los cambios. Inténtalo nuevamente.'
                            );

                        } finally {
                            this.requestPending =
                                false;

                            if (
                                this.queuedPayload
                            ) {
                                const nextPayload =
                                    this.queuedPayload;

                                this.queuedPayload =
                                    null;

                                await this.sendPayload(
                                    nextPayload
                                );
                            }
                        }
                    },


                    saveOptimistically() {
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

                        /*
                         * Feedback inmediato.
                         */
                        this.pulseSpinner();

                        /*
                         * Si ya existe un UPDATE trabajando,
                         * conservamos solamente la versión más nueva.
                         */
                        if (
                            this.requestPending
                        ) {
                            this.queuedPayload =
                                payload;

                            return;
                        }

                        /*
                         * No hacemos await aquí.
                         * La interfaz vuelve a estar disponible.
                         */
                        this.sendPayload(
                            payload
                        );
                    },
                })
            );
        </script>
    @endscript

</div>