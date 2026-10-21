<div>
    <div
        x-data="equipmentCreate(@js($equipmentData))"
        wire:key="equipment-create-ui"
    >
        {{-- MENSAJES --}}
        <div
            x-show="message"
            x-cloak
            role="status"
            :class="failed
                ? 'border-red-500 text-red-500'
                : 'border-emerald-500 text-emerald-500'"
            class="mb-4 flex items-center justify-between gap-3 rounded-xl border bg-[var(--theme-surface)] p-4 text-sm"
        >
            <span x-text="message"></span>

            <button
                type="button"
                @click="message = ''"
                aria-label="Cerrar mensaje"
            >
                ✕
            </button>
        </div>

        <form
            @submit.prevent="saveOptimistically()"
            class="space-y-5"
        >
            {{-- SELECCIÓN DEL TIPO --}}
            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                        Tipo de equipo
                    </h2>

                    <div class="flex flex-col items-start gap-2 sm:items-end">
                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Código de inventario:

                            <span
                                x-text="codigoInventarioPreview"
                                class="font-semibold text-[var(--theme-primary)]"
                            ></span>
                        </p>

                        {{-- Código de barras (solo el número del código de inventario) --}}
                        <x-equipo.codigo-barras
                            x-codigo="codigoInventarioPreview"
                            vista-previa
                            compacto
                            class="sm:items-end"
                        />
                    </div>
                </div>

                <div class="max-w-lg">
                    <x-searchable-select
                        x-model="values.idTipoEquipo"
                        x-options="data.tiposEquipo"
                        label="Tipo de equipo *"
                        placeholder="Buscar tipo de equipo..."
                    />

                    <p
                        x-show="errorFor('idTipoEquipo')"
                        x-text="errorFor('idTipoEquipo')"
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

                                {{--
                                    PROPIETARIO: busca una cuenta o permite escribir
                                    un nombre (sin cuenta: lo resuelve un administrador
                                    en Seguridad y roles).
                                --}}
                                <template x-if="field.model === 'propietario'">
                                    <div>
                                        <x-searchable-select
                                            x-model="values[field.model]"
                                            x-options="data.usuarios ?? []"
                                            :show-clear="false"
                                            allow-custom
                                            placeholder="Busca un usuario o escribe un nombre"
                                        />

                                        <p
                                            x-show="String(values[field.model] ?? '').startsWith('texto:')"
                                            class="mt-1.5 text-xs text-[var(--theme-warning)]"
                                        >
                                            Sin cuenta: el equipo se guardará y un administrador deberá crear la cuenta en Seguridad y roles.
                                        </p>
                                    </div>
                                </template>

                                {{-- LISTAS --}}
                                <template x-if="field.type === 'select'">
                                    <div>
                                        <x-searchable-select
                                            x-model="values[field.model]"
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
                                            x-model="values[field.model]"
                                            @change="delete errors[errorKey(field)]"
                                            class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                                        >

                                        <span
                                            x-text="values[field.model] ? 'Sí' : 'No'"
                                        ></span>
                                    </label>
                                </template>

                                {{-- TEXTO LARGO --}}
                                <template x-if="field.type === 'textarea'">
                                    <textarea
                                        :id="'equipment-' + field.key"
                                        rows="4"
                                        x-model="values[field.model]"
                                        :placeholder="field.placeholder || ''"
                                        @input="delete errors[errorKey(field)]"
                                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                    ></textarea>
                                </template>

                                {{-- TEXTO, NÚMERO Y FECHA --}}
                                <template
                                    x-if="!['select', 'boolean', 'textarea'].includes(field.type) && field.model !== 'propietario'"
                                >
                                    <input
                                        :id="'equipment-' + field.key"
                                        :type="['date', 'number'].includes(field.type)
                                            ? field.type
                                            : 'text'"
                                        :step="field.type === 'number' ? 'any' : null"
                                        x-model="values[field.model]"
                                        :placeholder="field.placeholder || ''"
                                        @input="delete errors[errorKey(field)]"
                                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                    >
                                </template>

                                <p
                                    x-show="errorFor(errorKey(field))"
                                    x-text="errorFor(errorKey(field))"
                                    class="mt-1 text-xs text-[var(--theme-danger)]"
                                ></p>
                            </div>
                        </template>
                    </div>
                </section>
            </template>

            {{-- ACCIONES --}}
            <div class="flex justify-end gap-3">
                <a
                    href="{{ route('equipos.index') }}"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
                >
                    Cancelar
                </a>

                <x-ui.button
                    type="submit"
                    x-loading="savingVisual"
                    size="lg"
                    loading-text="Guardando…"
                    class="w-full sm:w-auto"
                >
                    Añadir equipo
                </x-ui.button>
            </div>
        </form>
    </div>

    @script
    <script>
        Alpine.data('equipmentCreate', data => ({
            data: data ?? {},
            values: {},
            errors: {},

            message: '',
            failed: false,

            savingVisual: false,
            suspendDependencies: false,

            codigoInventarioPreview: String(
                data?.defaults?.codigoInventarioPreview ?? ''
            ),

            /*
             * Estos son los campos comunes que el guardado conoce.
             * Solo los obligatorios aparecen siempre.
             */
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

            emptyValues() {
                return Object.fromEntries([
                    ['idTipoEquipo', ''],

                    ...Object.values(this.base).map(
                        field => [field.model, '']
                    ),
                ]);
            },

            init() {
                this.values = this.emptyValues();

                this.$watch('values.idTipoEquipo', (value, previous) => {
                    if (
                        this.suspendDependencies
                        || String(value) === String(previous)
                    ) {
                        return;
                    }

                    /*
                     * Conservar los obligatorios.
                     * Limpiar los opcionales del tipo anterior.
                     */
                    const next = this.emptyValues();

                    next.idTipoEquipo = value;

                    Object.values(this.base)
                        .filter(field => field.required)
                        .forEach(field => {
                            next[field.model] = this.values[field.model];
                        });

                    this.values = next;
                    this.errors = {};
                });

                this.$watch('values.idMarca', (value, previous) => {
                    if (
                        !this.suspendDependencies
                        && String(value) !== String(previous)
                    ) {
                        this.values.idModelo = '';
                    }
                });

                this.$watch('values.idHotel', (value, previous) => {
                    if (
                        !this.suspendDependencies
                        && String(value) !== String(previous)
                    ) {
                        this.values.idDepartamento = '';
                    }
                });
            },

            get typeId() {
                return String(this.values.idTipoEquipo ?? '');
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

                        if (!base.required && !meta) {
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
                                === String(this.values.idMarca)
                            && String(modelo.idTipoEquipo)
                                === this.typeId
                    );
                }

                if (field.options === 'departamentos') {
                    const hotel = String(
                        this.values.idHotel ?? ''
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
                    && (!this.values.idMarca || !this.typeId)
                ) || (
                    field.options === 'departamentos'
                    && !this.values.idHotel
                );
            },

            errorKey(field) {
                return field.child
                    ? 'childData.' + field.model
                    : field.model;
            },

            errorFor(key) {
                const error = this.errors[key];

                return Array.isArray(error)
                    ? error[0]
                    : (error || '');
            },

            captureSnapshot() {
                const payload = this.emptyValues();

                payload.idTipoEquipo = this.typeId;
                payload.childData = {};

                /*
                 * Enviar únicamente valores de campos mostrados.
                 * Los opcionales ocultos quedan vacíos.
                 */
                this.displayFields.forEach(field => {
                    const value = this.values[field.model]
                        ?? (field.type === 'boolean' ? false : '');

                    if (field.child) {
                        payload.childData[field.model] = value;
                    } else {
                        payload[field.model] = value;
                    }
                });

                return JSON.parse(JSON.stringify(payload));
            },

            validateSnapshot(payload) {
                const errors = {};

                if (!payload.idTipoEquipo) {
                    errors.idTipoEquipo = [
                        'Selecciona un tipo de equipo.',
                    ];
                }

                this.displayFields.forEach(field => {
                    const value = field.child
                        ? payload.childData[field.model]
                        : payload[field.model];

                    if (
                        field.required
                        && (
                            value == null
                            || String(value).trim() === ''
                        )
                    ) {
                        errors[this.errorKey(field)] = [
                            'Completa ' + field.label + '.',
                        ];
                    }
                });

                this.errors = errors;

                if (Object.keys(errors).length > 0) {
                    this.failed = true;
                    this.message = Object.values(errors)[0][0];

                    return false;
                }

                return true;
            },

            clearForm() {
                this.suspendDependencies = true;
                this.values = this.emptyValues();
                this.errors = {};

                this.$nextTick(() => {
                    this.suspendDependencies = false;
                });
            },

            restoreSnapshot(payload) {
                this.suspendDependencies = true;

                const { childData, ...base } = payload;

                this.values = {
                    ...this.emptyValues(),
                    ...base,
                    ...(childData ?? {}),
                };

                this.$nextTick(() => {
                    this.suspendDependencies = false;
                });
            },

            formIsEmpty() {
                return Object.values(this.values).every(
                    value => value == null || value === ''
                );
            },

            async saveOptimistically() {
                const payload = this.captureSnapshot();

                if (!this.validateSnapshot(payload)) {
                    return;
                }

                this.message = '';
                this.savingVisual = true;

                let request;

                try {
                    request = $wire.save(payload);
                } catch (error) {
                    this.savingVisual = false;
                    this.failed = true;
                    this.message = 'No fue posible iniciar el guardado.';

                    return;
                }

                /*
                 * Mantener la captura inmediata del siguiente equipo.
                 */
                this.clearForm();

                setTimeout(() => {
                    this.savingVisual = false;
                }, 350);

                try {
                    const result = await request;

                    if (!result || result.ok !== true) {
                        throw new Error('Respuesta no válida');
                    }

                    if (result.nextCode) {
                        this.codigoInventarioPreview = String(
                            result.nextCode
                        );
                    }

                    this.failed = false;

                    // Confirmación con el aviso global; el recuadro del
                    // formulario queda solo para errores.
                    this.message = '';

                    window.trackitToast?.(
                        'success',
                        result.message || 'Equipo añadido correctamente.'
                    );
                } catch (error) {
                    const errors = error?.errors ?? {};

                    if (this.formIsEmpty()) {
                        this.restoreSnapshot(payload);
                        this.errors = errors;
                    }

                    const first = Object.values(errors)[0];

                    this.failed = true;

                    this.message = (
                        Array.isArray(first)
                            ? first[0]
                            : first
                    ) || 'No fue posible guardar el equipo. Inténtalo nuevamente.';
                }
            },
        }));
    </script>
    @endscript
</div>