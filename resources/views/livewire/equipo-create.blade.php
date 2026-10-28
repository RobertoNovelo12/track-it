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
            data-avisar-cambios
            @submit.prevent="saveOptimistically()"
            class="space-y-5"
        >
            {{-- Duplicar equipo: de dónde vienen los datos copiados --}}
            <template x-if="data.duplicado?.origen">
                <div role="status" class="flex items-start gap-3 rounded-lg border border-[var(--theme-primary-border)] bg-[var(--theme-primary-soft-subtle)] px-4 py-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-b9ae114') }}"></use></svg>
                    <p class="text-xs leading-relaxed text-[var(--theme-text)]">
                        Copiamos los datos de <strong class="font-semibold text-[var(--theme-text-strong)]" x-text="data.duplicado.origen"></strong>.
                        Completa el nombre, el número de serie y los datos propios de este equipo.
                    </p>
                </div>
            </template>

            {{-- SELECCIÓN DEL TIPO
                Título y select quedan arriba a la izquierda; el código de
                barras va en su propia columna y no mueve el título. --}}
            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <h2 class="mb-4 text-sm font-semibold text-[var(--theme-text-strong)]">
                            Tipo de equipo
                        </h2>

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
                    </div>

                    <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
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
                                    RESPONSABLE: se busca por nombre y el selector
                                    muestra únicamente el nombre (sin número de
                                    colaborador). También acepta nombres nuevos,
                                    que quedan pendientes de crear una cuenta en
                                    Seguridad y roles.
                                --}}
                                <template x-if="field.model === 'propietario'">
                                    <div>
                                        <x-searchable-select
                                            x-model="values[field.model]"
                                            x-options="opcionesResponsables"
                                            :show-clear="true"
                                            clear-label="Todos"
                                            :empty-as-null="false"
                                            allow-custom
                                            custom-hint="sin cuenta"
                                            placeholder="Busca o escribe el nombre del responsable"
                                        />

                                        <p
                                            x-show="String(values[field.model] ?? '').startsWith('texto:')"
                                            class="mt-1.5 text-xs text-[var(--theme-warning)]"
                                        >
                                            Sin cuenta: el equipo se guardará y un administrador podrá crear la cuenta en Seguridad y roles.
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
                                        :placeholder="field.placeholder || trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).ejemplo || ''"
                                        :inputmode="field.type === 'text' ? (trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).inputmode ?? null) : null"
                                        :maxlength="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).maxlength ?? null"
                                        :autocapitalize="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).mayusculas ? 'characters' : null"
                                        :spellcheck="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).sinCorrector ? 'false' : null"
                                        :autocomplete="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).sinCorrector ? 'off' : null"
                                        :aria-describedby="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).ayuda ? 'ayuda-equipment-' + field.key : null"
                                        @input="delete errors[errorKey(field)]"
                                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                    >
                                </template>

                                {{-- Formato esperado, visible antes de escribir --}}
                                <p
                                    x-show="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).ayuda && ! errorFor(errorKey(field))"
                                    :id="'ayuda-equipment-' + field.key"
                                    x-text="trackitFormatoCampo(field.key + ' ' + (field.label ?? '')).ayuda"
                                    class="mt-1 text-[11px] text-[var(--theme-text-muted)]"
                                ></p>

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

            {{-- PROGRAMAS (si el tipo «Incluye programas») --}}
            @include('livewire.equipos.partials.programas')

            {{-- ACCIONES --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <label class="mr-auto inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--theme-text)]" title="Al guardar se quedan tipo, marca, modelo, factura, hotel y especificaciones; se vacían nombre, serie y datos propios.">
                    <input type="checkbox" x-model="conservarDatos" data-sin-aviso class="rounded border-[var(--theme-border-strong)] text-[var(--theme-primary)]">
                    Conservar datos para el siguiente equipo
                </label>

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

            // Altas en serie: al guardar se conservan los datos comunes.
            conservarDatos: false,

            // Programas elegidos (ids) y búsqueda de la sección Programas.
            programas: [],
            programasBuscar: '',

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
                    // Va con la marca: siempre visible (opcional).
                    alwaysVisible: true,
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
                    label: 'Responsable',
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

                // Ubicación opcional en todos los tipos de equipo.
                // Se guarda en equipos.id_ubicacion (sin exigir configurar el campo por tipo).
                id_ubicacion: {
                    alwaysVisible: true,
                    model: 'idUbicacion',
                    label: 'Ubicación',
                    type: 'select',
                    options: 'ubicaciones',
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

                /*
                 * Tipo preseleccionado (?tipo=ID): llega desde Equipos
                 * cuando hay un tipo elegido en los filtros o en el menú.
                 */
                const tipoInicial = new URLSearchParams(window.location.search).get('tipo') ?? '';

                if (
                    tipoInicial !== ''
                    && (this.data.tiposEquipo ?? []).some(opcion => String(opcion.value) === tipoInicial)
                ) {
                    this.values.idTipoEquipo = tipoInicial;
                }

                /*
                 * Número de serie (?numero_serie=): llega desde el escáner
                 * cuando se leyó un código que no está en el inventario.
                 */
                const serieInicial = (new URLSearchParams(window.location.search).get('numero_serie') ?? '').trim();

                if (serieInicial !== '') {
                    this.values.numeroSerie = serieInicial.slice(0, 255);
                }

                /*
                 * Duplicar equipo (?duplicar=ID): datos comunes de otro
                 * equipo. Se cargan antes de vigilar tipo/marca/hotel para
                 * que no se limpien.
                 */
                const duplicado = this.data.duplicado ?? null;

                if (duplicado?.base) {
                    this.values = {
                        ...this.emptyValues(),
                        ...duplicado.base,
                        ...(duplicado.childData ?? {}),
                    };
                }

                // Programas: los del equipo duplicado o los predeterminados del tipo.
                this.programas = Array.isArray(duplicado?.programas)
                    ? duplicado.programas.map(String)
                    : this.programasPredeterminados();

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

                    // Al cambiar de tipo vienen marcados sus predeterminados.
                    this.programas = this.programasPredeterminados(value);
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
                        this.values.idUbicacion = '';
                    }
                });

                // La ubicación depende del departamento (si tiene departamentos).
                this.$watch('values.idDepartamento', (value, previous) => {
                    if (
                        !this.suspendDependencies
                        && String(value) !== String(previous)
                        && this.values.idUbicacion
                        && !this.optionsFor({ options: 'ubicaciones' }).some(
                            opcion => String(opcion.value) === String(this.values.idUbicacion)
                        )
                    ) {
                        this.values.idUbicacion = '';
                    }
                });
            },

            get typeId() {
                return String(this.values.idTipoEquipo ?? '');
            },

            /*
            |--------------------------------------------------------------------------
            | Programas (partials/programas)
            |--------------------------------------------------------------------------
            */

            get tipoIncluyeProgramas() {
                return Boolean(this.data.programas?.porTipo?.[this.typeId]?.incluye);
            },

            programasPredeterminados(tipo = this.typeId) {
                return (this.data.programas?.porTipo?.[String(tipo)]?.predeterminados ?? []).map(String);
            },

            marcarPredeterminados() {
                this.programas = [...new Set([...this.programas, ...this.programasPredeterminados()])];
            },

            programaElegido(id) {
                return this.programas.includes(String(id));
            },

            alternarPrograma(id) {
                const clave = String(id);

                this.programas = this.programaElegido(clave)
                    ? this.programas.filter(valor => valor !== clave)
                    : [...this.programas, clave];
            },

            programasFiltrados() {
                const normalizar = texto => String(texto ?? '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');

                const buscar = normalizar(this.programasBuscar).trim();
                const opciones = this.data.programas?.opciones ?? [];

                return buscar === ''
                    ? opciones
                    : opciones.filter(opcion => normalizar(opcion.label + ' ' + opcion.detalle + ' ' + opcion.categoria).includes(buscar));
            },

            // Mostrar solo el nombre del responsable; el identificador
            // interno se conserva para vincularlo con su cuenta al guardar.
            get opcionesResponsables() {
                return (this.data.usuarios ?? []).map(usuario => ({
                    ...usuario,
                    label: String(usuario.nombre || usuario.label || '').trim(),
                    search: String(usuario.nombre || usuario.label || '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .trim(),
                }));
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

                            // Ubicación siempre es información adicional opcional.
                            required: key === 'id_ubicacion'
                                ? false
                                : !!(base.required || meta?.obligatorio),

                            label: key === 'propietario'
                                ? 'Responsable'
                                : (meta?.etiqueta || base.label),
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
                    // Mantener Hotel, Departamento, Responsable y Ubicación
                    // dentro de Información adicional, sin cambiar su configuración.
                    const campoAdicional = ['id_unidad', 'id_departamento', 'propietario', 'id_ubicacion']
                        .includes(field.key);

                    let section = campoAdicional
                        ? sections.find(item => item.clave === 'adicional')
                        : null;

                    section ??= sections.find(
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

                        fields: section.fields.sort((a, b) => {
                            if (section.clave === 'adicional') {
                                // Primera fila: Hotel, Departamento, Responsable.
                                // Segunda fila: Ubicación debajo de Hotel;
                                // Comentarios queda después de estos campos.
                                const prioridad = {
                                    id_unidad: 0,
                                    id_departamento: 1,
                                    propietario: 2,
                                    id_ubicacion: 3,
                                    comentarios: 5,
                                };

                                const ordenA = prioridad[a.key] ?? 4;
                                const ordenB = prioridad[b.key] ?? 4;

                                if (ordenA !== ordenB) {
                                    return ordenA - ordenB;
                                }
                            }

                            return a.order - b.order;
                        }),
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

                /*
                 * Ubicaciones del hotel (o sin hotel) y, si ya hay
                 * departamento, las que no tienen departamentos o lo incluyen.
                 */
                if (field.options === 'ubicaciones') {
                    const hotel = String(this.values.idHotel ?? '');
                    const departamento = String(this.values.idDepartamento ?? '');

                    if (!hotel) {
                        return [];
                    }

                    return (this.data.ubicaciones ?? []).filter(
                        ubicacion =>
                            (!ubicacion.unidad || String(ubicacion.unidad) === hotel)
                            && (
                                !departamento
                                || (ubicacion.departamentos ?? []).length === 0
                                || ubicacion.departamentos.some(id => String(id) === departamento)
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
                    ['departamentos', 'ubicaciones'].includes(field.options)
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

                // Programas: solo si el tipo los incluye (null = no tocar).
                payload.programas = this.tipoIncluyeProgramas ? [...this.programas] : null;

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

            /*
             * Campos propios de cada equipo (mismo criterio que
             * EquipoCreate::esCampoUnico): no se conservan.
             */
            esCampoUnico(clave) {
                const texto = (String(clave) + ' ' + String(this.data.fieldLabels?.[clave] ?? ''))
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');

                return ['nombreEquipo', 'numeroSerie', 'direccionMac', 'host', 'propietario', 'comentarios'].includes(clave)
                    || /imei|serie|serial|service.?tag|telefon|celular|host|(^|[\s_])mac($|[\s_])|(^|[\s_])ip($|[\s_])/.test(texto);
            },

            /*
             * «Conservar datos para el siguiente equipo»: se quedan tipo,
             * marca, modelo, factura, hotel, especificaciones…; se vacían
             * nombre, serie y demás datos propios.
             */
            prepararSiguiente(payload) {
                this.restoreSnapshot(payload);

                this.$nextTick(() => {
                    Object.keys(this.values)
                        .filter((clave) => this.esCampoUnico(clave))
                        .forEach((clave) => {
                            this.values[clave] = typeof this.values[clave] === 'boolean' ? false : '';
                        });

                    this.errors = {};
                    document.getElementById('equipment-nombre_equipo')?.focus();
                });
            },

            clearForm() {
                this.suspendDependencies = true;
                this.values = this.emptyValues();
                this.programas = [];
                this.programasBuscar = '';
                this.errors = {};

                this.$nextTick(() => {
                    this.suspendDependencies = false;
                });
            },

            restoreSnapshot(payload) {
                this.suspendDependencies = true;

                const { childData, programas, ...base } = payload;

                this.values = {
                    ...this.emptyValues(),
                    ...base,
                    ...(childData ?? {}),
                };

                // Conservar datos / error al guardar: se recuperan los programas.
                this.programas = Array.isArray(programas)
                    ? programas.map(String)
                    : this.programasPredeterminados(base.idTipoEquipo);

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
                 * Mantener la captura inmediata del siguiente equipo
                 * (con los datos comunes si se pidió conservarlos).
                 */
                this.conservarDatos ? this.prepararSiguiente(payload) : this.clearForm();

                setTimeout(() => {
                    this.savingVisual = false;
                }, 350);

                try {
                    const result = await request;

                    if (!result || result.ok !== true) {
                        // Falla inesperada en el servidor: detalle en la consola.
                        const fallo = new Error(result?.message || 'Respuesta no válida');
                        fallo.debug = result?.debug ?? result;
                        throw fallo;
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

                    // Diagnóstico: error exacto en la consola del navegador.
                    console.error('[Track-it] Error al guardar el equipo', {
                        estado: error?.status ?? null,
                        mensaje: error?.message ?? null,
                        detalle: error?.debug ?? null,
                        respuesta: error?.body ?? error?.json ?? error?.response ?? null,
                        errores: errors,
                        error,
                    });

                    if (error?.debug?.mensaje) {
                        console.error('[Track-it] ' + error.debug.clase + ': ' + error.debug.mensaje + '\n' + error.debug.archivo + ':' + error.debug.linea + (error.debug.sql ? '\nSQL: ' + error.debug.sql : ''));
                    }

                    // Recuperar lo capturado si el usuario no empezó el siguiente.
                    const sinEmpezar = this.conservarDatos
                        ? ! this.values.nombreEquipo && ! this.values.numeroSerie
                        : this.formIsEmpty();

                    if (sinEmpezar) {
                        this.restoreSnapshot(payload);
                        this.errors = errors;
                    }

                    const first = Object.values(errors)[0];

                    this.failed = true;

                    this.message = (
                        Array.isArray(first)
                            ? first[0]
                            : first
                    ) || (error?.debug ? error.message : '') || window.trackitMensajeError(error, 'guardar el equipo');
                }
            },
        }));
    </script>
    @endscript
</div>