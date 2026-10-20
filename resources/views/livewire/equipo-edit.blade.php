@php
    /*
     * Rol Lectura: el formulario se muestra completo pero bloqueado
     * (fieldset deshabilitado) y sin acciones de guardar ni baja.
     */
    $soloLectura = \Illuminate\Support\Facades\Gate::denies('gestionar');
@endphp

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

            @unless ($soloLectura)
            {{-- Dar de baja --}}
            <a
                href="{{ route('equipos.baja', $equipoId) }}"
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
                    hover:opacity-90
                    transition-opacity
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
            </a>
            @endunless
        </div>

        {{-- ============================================================
            FORMULARIO
        ============================================================ --}}
        <form
            @submit.prevent="{{ $soloLectura ? '' : 'saveOptimistically()' }}"
            class="relative"
        >
            @if ($soloLectura)
                <div
                    role="status"
                    class="mb-4 flex items-start gap-2 rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3 text-xs text-[var(--theme-text-muted)]"
                >
                    <svg class="mt-px h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="9" rx="1.5"/>
                        <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>

                    <span>
                        Solo lectura: tu rol permite consultar la información del equipo, pero no modificarla.
                    </span>
                </div>
            @endif

            <fieldset
                class="space-y-4 sm:space-y-6"
                @disabled($soloLectura)
            >

            {{-- SELECCIÓN DEL TIPO --}}
            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                        Tipo de equipo
                    </h2>

                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Código de inventario:

                        <span
                            x-text="codigoInventario"
                            class="font-semibold text-[var(--theme-primary)]"
                        ></span>
                    </p>
                </div>

                <div class="max-w-lg">
                    <x-searchable-select
                        x-model="idTipoEquipo"
                        x-options="data.tiposEquipo"
                        label="Tipo de equipo *"
                        placeholder="Buscar tipo de equipo..."
                    />

                    <p
                        x-show="fieldError('idTipoEquipo')"
                        x-text="fieldError('idTipoEquipo')"
                        class="mt-1 text-xs text-[var(--theme-danger)]"
                    ></p>
                </div>

                <p
                    x-show="!typeId"
                    class="mt-3 text-xs text-[var(--theme-text-muted)]"
                >
                    Selecciona un tipo para mostrar sus campos configurados.
                </p>
            </section>

            {{-- SECCIONES Y CAMPOS CONFIGURADOS --}}
            <template
                x-for="section in displaySections"
                :key="typeId + '-' + section.id_seccion"
            >
                <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                    <h2
                        x-text="section.nombre"
                        class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]"
                    ></h2>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <template
                            x-for="field in section.fields"
                            :key="field.key"
                        >
                            <div
                                class="min-w-0"
                                :class="field.type === 'textarea'
                                    ? 'md:col-span-2 xl:col-span-3'
                                    : ''"
                            >
                                <label
                                    :for="'equipment-' + field.key"
                                    x-text="field.label + (field.required ? ' *' : '')"
                                    class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                                ></label>

                                {{-- LISTAS --}}
                                <template x-if="field.type === 'select'">
                                    <div>
                                        <x-searchable-select
                                            x-model="fieldValues(field)[field.model]"
                                        x-bind:readonly="!field.child && field.model === 'propietario'"
                                            x-options="optionsFor(field)"
                                            x-disabled="disabledField(field)"
                                            placeholder="Buscar..."
                                        />
                                    </div>
                                </template>

                                {{-- SÍ / NO --}}
                                <template x-if="field.type === 'boolean'">
                                    <label class="inline-flex items-center gap-2 text-sm text-[var(--theme-text)]">
                                        <input
                                            type="checkbox"
                                            :id="'equipment-' + field.key"
                                            x-model="fieldValues(field)[field.model]"
                                        x-bind:readonly="!field.child && field.model === 'propietario'"
                                            @change="clearValidationError(errorKey(field))"
                                            class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                                        >

                                        <span
                                            x-text="fieldValues(field)[field.model] ? 'Sí' : 'No'"
                                        ></span>
                                    </label>
                                </template>

                                {{-- TEXTO LARGO --}}
                                <template x-if="field.type === 'textarea'">
                                    <textarea
                                        :id="'equipment-' + field.key"
                                        rows="4"
                                        x-model="fieldValues(field)[field.model]"
                                        x-bind:readonly="!field.child && field.model === 'propietario'"
                                        :placeholder="field.placeholder || ''"
                                        @input="clearValidationError(errorKey(field))"
                                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                    ></textarea>
                                </template>

                                {{-- TEXTO, NÚMERO Y FECHA --}}
                                <template
                                    x-if="!['select', 'boolean', 'textarea'].includes(field.type)"
                                >
                                    <input
                                        :id="'equipment-' + field.key"
                                        :type="['date', 'number'].includes(field.type)
                                            ? field.type
                                            : 'text'"
                                        :step="field.type === 'number' ? 'any' : null"
                                        x-model="fieldValues(field)[field.model]"
                                        x-bind:readonly="!field.child && field.model === 'propietario'"
                                        :placeholder="field.placeholder || ''"
                                        @input="clearValidationError(errorKey(field))"
                                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                    >
                                </template>

                                <p
                                    x-show="fieldError(errorKey(field))"
                                    x-text="fieldError(errorKey(field))"
                                    class="mt-1 text-xs text-[var(--theme-danger)]"
                                ></p>
                            </div>
                        </template>
                    </div>
                </section>
            </template>


                {{-- ====================================================
                    ACCIONES
                ==================================================== --}}
                {{--
                    Móvil: botones y debajo las fechas.
                    Escritorio: fechas en la esquina izquierda y
                    botones a la derecha.
                --}}
                <div class="flex flex-col gap-4 pt-1 pb-2 sm:flex-row sm:items-center sm:justify-between">

                <div
                    class="
                        flex
                        flex-col-reverse
                        sm:order-2
                        sm:flex-row
                        sm:items-center
                        sm:justify-end
                        gap-3
                    "
                >
                    <a
                        href="{{ route('equipos.index') }}"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                    >
                        {{ $soloLectura ? 'Volver' : 'Cancelar' }}
                    </a>

                    @unless ($soloLectura)
                    <x-ui.button
                        type="submit"
                        x-loading="savingVisual"
                        size="lg"
                        loading-text="Guardando…"
                        class="w-full sm:w-auto"
                    >
                        Guardar cambios
                    </x-ui.button>
                    @endunless
                </div>

                {{-- FECHAS --}}
                <dl
                    x-data="{ modificado: @js(\App\Support\Fecha::conHora($fechaActualizacion, 'Sin modificaciones')) }"
                    @equipo-fecha-actualizada.window="modificado = $event.detail.fecha"
                    class="grid grid-cols-2 gap-3 text-xs sm:order-1 sm:flex sm:gap-6"
                >
                    <div class="min-w-0">
                        <dt class="text-[var(--theme-text-muted)]">Fecha de creación</dt>
                        <dd class="mt-0.5 font-medium text-[var(--theme-text)]">
                            {{ \App\Support\Fecha::conHora($fechaRegistro, 'Sin registro') }}
                        </dd>
                    </div>

                    <div class="min-w-0">
                        <dt class="text-[var(--theme-text-muted)]">Fecha de modificación</dt>
                        <dd class="mt-0.5 font-medium text-[var(--theme-text)]" x-text="modificado">
                            {{ \App\Support\Fecha::conHora($fechaActualizacion, 'Sin modificaciones') }}
                        </dd>
                    </div>
                </dl>

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

                    idHotel:
                        String(
                            data?.initial
                                ?.idHotel
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

            base: {
                nombre_equipo: {
                    model: 'nombreEquipo',
                    label: 'Nombre del equipo',
                    type: 'text',
                    required: true,
                },

                id_estado_activo: {
                    model: 'idEstadoActivo',
                    label: 'Estado',
                    type: 'select',
                    options: 'estados',
                    required: true,
                },

                id_marca: {
                    model: 'idMarca',
                    label: 'Marca',
                    type: 'select',
                    options: 'marcas',
                    required: true,
                },

                numero_serie: {
                    model: 'numeroSerie',
                    label: 'Número de serie',
                    type: 'text',
                    required: true,
                },

                numero_factura: {
                    model: 'numeroFactura',
                    label: 'Número de factura',
                    type: 'text',
                    required: true,
                },

                id_unidad: {
                    model: 'idHotel',
                    label: @js(app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular']),
                    type: 'select',
                    options: 'hoteles',
                    required: true,
                    sectionKey: 'adicional',
                },

                host: {
                    model: 'host',
                    label: 'Host',
                    type: 'text',
                },

                id_modelo: {
                    model: 'idModelo',
                    label: 'Modelo',
                    type: 'select',
                    options: 'modelos',
                },

                direccion_mac: {
                    model: 'direccionMac',
                    label: 'Dirección MAC',
                    type: 'text',
                },

                fecha_compra: {
                    model: 'fechaCompra',
                    label: 'Fecha de compra',
                    type: 'date',
                },

                id_proveedor: {
                    model: 'idProveedor',
                    label: 'Proveedor',
                    type: 'select',
                    options: 'proveedores',
                },

                propietario: {
                    model: 'propietario',
                    label: 'Propietario / Responsable',
                    type: 'text',
                    sectionKey: 'adicional',
                },

                id_condicion_activo: {
                    model: 'idCondicionActivo',
                    label: 'Condición',
                    type: 'select',
                    options: 'condiciones',
                    sectionKey: 'adicional',
                },

                id_departamento: {
                    alwaysVisible: true,
                    model: 'idDepartamento',
                    label: 'Departamento',
                    type: 'select',
                    options: 'departamentos',
                    sectionKey: 'adicional',
                },

                fecha_fin_garantia: {
                    model: 'fechaFinGarantia',
                    label: 'Vigencia de garantía',
                    type: 'date',
                    sectionKey: 'adicional',
                },

                comentarios: {
                    model: 'comentarios',
                    label: 'Comentarios',
                    type: 'textarea',
                    sectionKey: 'adicional',
                },
            },

            get typeId() {
                return String(this.idTipoEquipo ?? '');
            },

            get displayFields() {
                const configured = this.typeId
                    ? (this.data.baseFieldsByType?.[this.typeId] ?? {})
                    : {};

                const fields = [];

                /*
                 * Campos del sistema.
                 */
                Object.entries(this.base).forEach(
                    ([key, base], index) => {
                        const meta = configured[key];

                        if (!base.required && !base.alwaysVisible && !meta) {
                            return;
                        }

                        fields.push({
                            ...base,
                            key,
                            child: false,

                            required: !!(
                                base.required || meta?.obligatorio
                            ),

                            label: meta?.etiqueta || base.label,
                            sectionId: meta?.id_seccion,
                            sectionKey: base.sectionKey || 'general',
                            order: Number(
                                meta?.orden ?? (index + 1) * 10
                            ),
                        });
                    }
                );

                /*
                 * Campos personalizados y específicos del tipo.
                 */
                const children = this.typeId
                    ? (this.data.childFieldsByType?.[this.typeId] ?? {})
                    : {};

                Object.entries(children).forEach(
                    ([key, type], index) => {
                        const meta =
                            this.data.childFieldMetaByType
                                ?.[this.typeId]?.[key] ?? {};

                        const catalog =
                            typeof type === 'string'
                            && type.startsWith('catalog:');

                        fields.push({
                            key,
                            model: key,
                            child: true,

                            type: catalog
                                ? 'select'
                                : (meta.tipo_control || type),

                            catalog: catalog
                                ? type.substring(8)
                                : key,

                            label: meta.etiqueta
                                || this.data.fieldLabels?.[key]
                                || key,

                            required: !!meta.obligatorio,
                            placeholder: meta.placeholder || '',

                            sectionId: meta.id_seccion,
                            sectionKey: 'informacion_tipo',

                            order: Number(
                                meta.orden ?? 1000 + index
                            ),
                        });
                    }
                );

                return fields;
            },

            get displaySections() {
                const configured = this.typeId
                    ? (this.data.sectionsByType?.[this.typeId] ?? [])
                    : [];

                const sections = configured.map(section => ({
                    ...section,
                    fields: [],
                }));

                const names = {
                    general: 'Información general',
                    informacion_tipo: 'Información del equipo',
                    adicional: 'Información adicional',
                };

                this.displayFields.forEach(field => {
                    let section = sections.find(
                        item =>
                            field.sectionId != null
                            && String(item.id_seccion)
                                === String(field.sectionId)
                    );

                    section ??= sections.find(
                        item => item.clave === field.sectionKey
                    );

                    if (!section) {
                        section = {
                            id_seccion: 'default-' + field.sectionKey,
                            clave: field.sectionKey,
                            nombre: names[field.sectionKey],

                            orden: field.sectionKey === 'general'
                                ? 10
                                : field.sectionKey === 'adicional'
                                    ? 30
                                    : 20,

                            fields: [],
                        };

                        sections.push(section);
                    }

                    section.fields.push(field);
                });

                return sections
                    .filter(section => section.fields.length > 0)
                    .sort((a, b) => a.orden - b.orden)
                    .map(section => ({
                        ...section,

                        fields: section.fields.sort(
                            (a, b) => a.order - b.order
                        ),
                    }));
            },

            optionsFor(field) {
                if (field.child) {
                    return this.data.dynamicCatalogs?.[field.catalog]
                        ?? [];
                }

                if (field.options === 'modelos') {
                    return (this.data.modelos ?? []).filter(
                        modelo =>
                            String(modelo.idMarca)
                                === String(this.idMarca)
                            && String(modelo.idTipoEquipo)
                                === this.typeId
                    );
                }

                if (field.options === 'departamentos') {
                    const hotel = String(
                        this.idHotel ?? ''
                    );

                    if (!hotel) {
                        return [];
                    }

                    return (this.data.departamentos ?? []).filter(
                        departamento =>
                            departamento.atiendeTodosHoteles === true
                            || (departamento.idHoteles ?? []).some(
                                id => String(id) === hotel
                            )
                    );
                }

                return this.data[field.options] ?? [];
            },

            disabledField(field) {
                if (field.child) {
                    return false;
                }

                return (
                    field.options === 'modelos'
                    && (!this.idMarca || !this.typeId)
                ) || (
                    field.options === 'departamentos'
                    && !this.idHotel
                );
            },

            errorKey(field) {
                return field.child
                    ? 'childData.' + field.model
                    : field.model;
            },

            fieldValues(field) {
                return field.child ? this.childData : this;
            },

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
                            'idHotel',
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
                                    'idHotel'
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
                        const hotelId = String(this.idHotel ?? '');
                        if (hotelId === '') return [];
                        return (this.data?.departamentos ?? []).filter(
                            departamento => departamento.atiendeTodosHoteles
                                || (departamento.idHoteles ?? []).some(
                                    id => String(id) === hotelId
                                )
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

                        window.trackitToast?.('success', this.successMessage); /* aviso global */

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

                        window.trackitToast?.('error', this.errorMessage); /* aviso global */

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

                            idHotel:
                                String(
                                    this.idHotel
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
                                payload.idHotel
                                ?? ''
                            ) === ''
                        ) {
                            errors.idHotel = [
                                'Selecciona un hotel.'
                            ];
                        }

                        this.displayFields.forEach(field => {
                            if (!field.required) return;
                            const value = field.child ? payload.childData[field.model] : payload[field.model];
                            if (value == null || String(value).trim() === '') {
                                errors[this.errorKey(field)] = ['Completa ' + field.label + '.'];
                            }
                        });

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