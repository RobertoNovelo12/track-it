@once
<script>
    window.tipoEquipoFormBuilder = function (initialData, wire) {
        return {
            /*
            |--------------------------------------------------------------------------
            | Datos principales
            |--------------------------------------------------------------------------
            */

            wire: wire,

            tipo: {},

            sections: [],

            fields: [],

            initialFields: [],

            /*
             * Programas del tipo (partials/programas): casilla «Incluye
             * programas» y predeterminados. Se guardan con «Guardar
             * configuración».
             */
            programas: { disponible: false, incluye: false, seleccion: [], opciones: [] },
            initialProgramas: { disponible: false, incluye: false, seleccion: [], opciones: [] },
            programasBuscar: '',

            /*
            |--------------------------------------------------------------------------
            | Estado general
            |--------------------------------------------------------------------------
            */

            hasChanges: false,

            saving: false,

            newFieldCounter: 0,

            /*
            |--------------------------------------------------------------------------
            | Drag & Drop
            |--------------------------------------------------------------------------
            */

            draggingUid: null,

            dragInsertIndex: null,

            dragShiftDistance: 0,

            dragGap: 8,

            dragSourceHidden: false,

            dragHideFrame: null,

            /*
            |--------------------------------------------------------------------------
            | Buscador
            |--------------------------------------------------------------------------
            */

            availableSearch: '',

            /*
            |--------------------------------------------------------------------------
            | Modal de opciones
            |--------------------------------------------------------------------------
            */

            optionsModalOpen: false,

            optionsModalEditable: false,

            optionsModalLoading: false,

            optionsModalFieldUid: null,

            optionsModalTitle: '',

            optionsModalSource: '',

            optionsModalItems: [],

            newOptionName: '',

            /*
            |--------------------------------------------------------------------------
            | Toast
            |--------------------------------------------------------------------------
            */

            toastVisible: false,

            toastMessage: '',

            toastType: 'success',

            toastTimer: null,

            /*
            |--------------------------------------------------------------------------
            | Inicializar
            |--------------------------------------------------------------------------
            */

            init() {
                this.replaceData(initialData);
            },

            /*
            |--------------------------------------------------------------------------
            | Clonar
            |--------------------------------------------------------------------------
            */

            clone(value) {
                return JSON.parse(JSON.stringify(value));
            },

            /*
            |--------------------------------------------------------------------------
            | Reemplazar datos
            |--------------------------------------------------------------------------
            */

            replaceData(data) {
                this.tipo = this.clone(data?.tipo ?? {});

                if (data?.programas) {
                    this.programas = this.clone({
                        disponible: Boolean(data.programas.disponible),
                        incluye: Boolean(data.programas.incluye),
                        seleccion: (data.programas.seleccion ?? []).map(String),
                        opciones: data.programas.opciones ?? [],
                        url_catalogo: data.programas.url_catalogo ?? '#',
                        url_nuevo: data.programas.url_nuevo ?? '#',
                    });

                    this.initialProgramas = this.clone(this.programas);
                }

                /*
                |--------------------------------------------------------------------------
                | Secciones reales
                |--------------------------------------------------------------------------
                |
                | Guardamos únicamente sus metadatos.
                | Los campos mantienen una sola fuente de verdad: this.fields.
                |
                */

                this.sections = this.clone(
                    Array.isArray(data?.sections)
                        ? data.sections
                        : []
                ).map(section => ({
                    id_seccion: Number(section.id_seccion),
                    clave: String(section.clave ?? ''),
                    nombre: String(section.nombre ?? ''),
                    orden: Number(section.orden ?? 0),
                    es_base: Boolean(section.es_base),
                }));

                /*
                |--------------------------------------------------------------------------
                | Campos
                |--------------------------------------------------------------------------
                |
                | No ordenamos aquí.
                |
                | PHP devuelve primero los campos visibles en su orden real
                | y después los campos disponibles.
                |
                */

                this.fields = this.clone(data?.fields ?? []).map(field => ({
                    ...field,

                    visible: Boolean(field.visible),

                    obligatorio: Boolean(field.obligatorio),

                    protected: Boolean(field.protected),

                    es_sistema: Boolean(field.es_sistema),

                    is_new_custom: Boolean(field.is_new_custom),

                    custom_options: Array.isArray(field.custom_options)
                        ? field.custom_options
                        : [],
                }));

                this.normalizeVisibleOrder();

                this.initialFields = this.clone(this.fields);

                this.hasChanges = false;

                this.saving = false;

                this.cancelFieldDrag();

                this.closeOptionsModal();
            },

            /*
            |--------------------------------------------------------------------------
            | Campos activos
            |--------------------------------------------------------------------------
            |
            | El orden físico del array representa el orden del formulario.
            |
            */

            get selectedFields() {
                return this.fields.filter(field => field.visible);
            },

            /*
            |--------------------------------------------------------------------------
            | Secciones con sus campos activos
            |--------------------------------------------------------------------------
            |
            | Conserva las secciones vacías.
            | Respeta el orden actual de this.fields.
            | No modifica ni ordena el array del constructor.
            |
            */

            get sectionsWithFields() {
                const selected = this.selectedFields;

                return this.sections.map(section => ({
                    ...section,

                    fields: selected.filter(
                        field =>
                            field.id_seccion !== null
                            && field.id_seccion !== undefined
                            && Number(field.id_seccion) === section.id_seccion
                    ),
                }));
            },

            /*
            |--------------------------------------------------------------------------
            | Campos disponibles
            |--------------------------------------------------------------------------
            |
            | Esta lista puede ordenarse alfabéticamente sin modificar
            | el orden del formulario.
            |
            */

            get availableFields() {
                const search = this.availableSearch
                    .trim()
                    .toLocaleLowerCase('es');

                return this.fields
                    .filter(field => ! field.visible)
                    .filter(field => {
                        if (search === '') {
                            return true;
                        }

                        const text = [
                            field.etiqueta,
                            field.nombre_base,
                            field.clave,
                            this.controlLabel(field.tipo_control),
                        ]
                            .filter(Boolean)
                            .join(' ')
                            .toLocaleLowerCase('es');

                        return text.includes(search);
                    })
                    .slice()
                    .sort((a, b) =>
                        String(a.etiqueta ?? a.nombre_base ?? '')
                            .localeCompare(
                                String(b.etiqueta ?? b.nombre_base ?? ''),
                                'es',
                                {
                                    sensitivity: 'base',
                                }
                            )
                    );
            },

            /*
            |--------------------------------------------------------------------------
            | Crear UID local
            |--------------------------------------------------------------------------
            */

            nextUid() {
                this.newFieldCounter++;

                return [
                    'new',
                    Date.now(),
                    this.newFieldCounter,
                ].join('-');
            },

            /*
            |--------------------------------------------------------------------------
            | Agregar control personalizado
            |--------------------------------------------------------------------------
            */

            addControl(type) {
                const allowed = [
                    'text',
                    'textarea',
                    'number',
                    'date',
                    'boolean',
                    'select',
                ];

                if (! allowed.includes(type)) {
                    return;
                }

                const labels = {
                    text: 'Nuevo campo',
                    textarea: 'Texto largo',
                    number: 'Número',
                    date: 'Fecha',
                    boolean: 'Sí / No',
                    select: 'Nueva lista',
                };

                const field = {
                    uid: this.nextUid(),

                    id_campo: null,

                    id_tipo_campo: null,

                    clave: null,

                    nombre_base: labels[type],

                    etiqueta: labels[type],

                    tipo_control: type,

                    descripcion: '',

                    placeholder: '',

                    es_sistema: false,

                    origen_dato: 'atributo',

                    tabla_destino: null,

                    columna_destino: null,

                    fuente_opciones: type === 'select'
                        ? 'personalizado'
                        : null,

                    id_catalogo: null,

                    seccion: 'adicional',

                    visible: true,

                    obligatorio: false,

                    orden: 0,

                    protected: false,

                    is_new_custom: true,

                    custom_options: [],
                };

                /*
                |--------------------------------------------------------------------------
                | Insertar al final de los campos activos
                |--------------------------------------------------------------------------
                */

                const firstAvailableIndex = this.fields.findIndex(
                    item => ! item.visible
                );

                if (firstAvailableIndex === -1) {
                    this.fields.push(field);
                } else {
                    this.fields.splice(firstAvailableIndex, 0, field);
                }

                this.normalizeVisibleOrder();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Agregar campo existente
            |--------------------------------------------------------------------------
            */

            addExistingField(uid) {
                const index = this.findIndexByUid(uid);

                if (index === -1) {
                    return;
                }

                const [field] = this.fields.splice(index, 1);

                field.visible = true;

                if (field.protected) {
                    field.obligatorio = true;
                }

                const firstAvailableIndex = this.fields.findIndex(
                    item => ! item.visible
                );

                if (firstAvailableIndex === -1) {
                    this.fields.push(field);
                } else {
                    this.fields.splice(firstAvailableIndex, 0, field);
                }

                this.normalizeVisibleOrder();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Quitar campo
            |--------------------------------------------------------------------------
            */

            removeField(uid) {
                const index = this.findIndexByUid(uid);

                if (index === -1) {
                    return;
                }

                const field = this.fields[index];

                if (field.protected) {
                    this.showToast(
                        'Este campo debe permanecer en el formulario.',
                        'warning'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Campo nuevo todavía no guardado
                |--------------------------------------------------------------------------
                */

                if (field.is_new_custom) {
                    this.fields.splice(index, 1);

                    this.normalizeVisibleOrder();

                    this.markChanged();

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Campo existente
                |--------------------------------------------------------------------------
                |
                | No se elimina: se convierte en disponible.
                |
                */

                const [removed] = this.fields.splice(index, 1);

                removed.visible = false;

                this.fields.push(removed);

                this.normalizeVisibleOrder();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Obligatorio / opcional
            |--------------------------------------------------------------------------
            */

            toggleRequired(uid) {
                const field = this.findFieldByUid(uid);

                if (! field) {
                    return;
                }

                if (field.protected) {
                    field.obligatorio = true;

                    return;
                }

                field.obligatorio = ! field.obligatorio;

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Actualizar etiqueta
            |--------------------------------------------------------------------------
            */

            updateLabel(uid, value) {
                const field = this.findFieldByUid(uid);

                if (! field) {
                    return;
                }

                field.etiqueta = value;

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Iniciar arrastre
            |--------------------------------------------------------------------------
            */

            startDrag(uid, event) {
                const field = this.findFieldByUid(uid);

                if (! field || ! field.visible) {
                    return;
                }

                if (this.dragHideFrame) {
                    cancelAnimationFrame(this.dragHideFrame);

                    this.dragHideFrame = null;
                }

                this.draggingUid = uid;

                this.dragSourceHidden = false;

                const selected = this.selectedFields;

                this.dragInsertIndex = selected.findIndex(
                    item => item.uid === uid
                );

                const card = event?.currentTarget?.closest(
                    '[data-builder-field-uid]'
                );

                if (card) {
                    const rect = card.getBoundingClientRect();

                    this.dragShiftDistance = rect.height + this.dragGap;

                    if (event?.dataTransfer) {
                        const offsetX = Math.max(
                            0,
                            Math.min(
                                event.clientX - rect.left,
                                rect.width
                            )
                        );

                        const offsetY = Math.max(
                            0,
                            Math.min(
                                event.clientY - rect.top,
                                rect.height
                            )
                        );

                        event.dataTransfer.setDragImage(
                            card,
                            offsetX,
                            offsetY
                        );
                    }
                } else {
                    this.dragShiftDistance = 80;
                }

                if (event?.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';

                    event.dataTransfer.setData(
                        'text/plain',
                        String(uid)
                    );
                }

                this.dragHideFrame = requestAnimationFrame(() => {
                    this.dragHideFrame = requestAnimationFrame(() => {
                        if (this.draggingUid === uid) {
                            this.dragSourceHidden = true;
                        }

                        this.dragHideFrame = null;
                    });
                });
            },

            /*
            |--------------------------------------------------------------------------
            | Actualizar destino del arrastre
            |--------------------------------------------------------------------------
            */

            updateDragTarget(targetUid, event) {
                if (! this.draggingUid) {
                    return;
                }

                if (event?.dataTransfer) {
                    event.dataTransfer.dropEffect = 'move';
                }

                const selected = this.selectedFields;

                const draggedIndex = selected.findIndex(
                    field => field.uid === this.draggingUid
                );

                const targetIndex = selected.findIndex(
                    field => field.uid === targetUid
                );

                if (draggedIndex === -1 || targetIndex === -1) {
                    return;
                }

                if (targetUid === this.draggingUid) {
                    this.dragInsertIndex = draggedIndex;

                    return;
                }

                const targetElement = event?.currentTarget;

                if (! targetElement) {
                    return;
                }

                const rect = targetElement.getBoundingClientRect();

                const after = event.clientY > (
                    rect.top + rect.height / 2
                );

                /*
                |--------------------------------------------------------------------------
                | Índice final después de retirar el elemento original
                |--------------------------------------------------------------------------
                */

                let insertIndex = targetIndex + (after ? 1 : 0);

                if (insertIndex > draggedIndex) {
                    insertIndex--;
                }

                insertIndex = Math.max(
                    0,
                    Math.min(
                        insertIndex,
                        selected.length - 1
                    )
                );

                this.dragInsertIndex = insertIndex;
            },

            /*
            |--------------------------------------------------------------------------
            | Estilo visual durante el arrastre
            |--------------------------------------------------------------------------
            */

            dragVisualStyle(uid) {
                if (
                    ! this.draggingUid
                    || this.dragInsertIndex === null
                ) {
                    return '';
                }

                const selected = this.selectedFields;

                const draggedIndex = selected.findIndex(
                    field => field.uid === this.draggingUid
                );

                const currentIndex = selected.findIndex(
                    field => field.uid === uid
                );

                if (draggedIndex === -1 || currentIndex === -1) {
                    return '';
                }

                /*
                |--------------------------------------------------------------------------
                | Ocultar la tarjeta después de crear la imagen de arrastre
                |--------------------------------------------------------------------------
                |
                | Conserva su posición física para calcular el desplazamiento.
                |
                */

                if (uid === this.draggingUid) {
                    if (this.dragSourceHidden) {
                        return `
                            visibility: hidden;
                            pointer-events: none;
                            transition: none;
                        `;
                    }

                    return `
                        position: relative;
                        z-index: 20;
                    `;
                }

                const targetIndex = this.dragInsertIndex;

                /*
                |--------------------------------------------------------------------------
                | Movimiento hacia abajo
                |--------------------------------------------------------------------------
                */

                if (
                    targetIndex > draggedIndex
                    && currentIndex > draggedIndex
                    && currentIndex <= targetIndex
                ) {
                    return `
                        transform:
                            translateY(
                                -${this.dragShiftDistance}px
                            );
                    `;
                }

                /*
                |--------------------------------------------------------------------------
                | Movimiento hacia arriba
                |--------------------------------------------------------------------------
                */

                if (
                    targetIndex < draggedIndex
                    && currentIndex >= targetIndex
                    && currentIndex < draggedIndex
                ) {
                    return `
                        transform:
                            translateY(
                                ${this.dragShiftDistance}px
                            );
                    `;
                }

                return `
                    transform:
                        translateY(0px);
                `;
            },

            /*
            |--------------------------------------------------------------------------
            | Soltar campo
            |--------------------------------------------------------------------------
            |
            | Utiliza el destino calculado durante dragover.
            | También debe invocarse desde dragend en el asa del builder.
            |
            */

            finishFieldDrop(targetUid = null, event = null) {
                if (! this.draggingUid) {
                    return;
                }

                const draggedUid = this.draggingUid;

                const insertIndex = this.dragInsertIndex;

                const selected = this.selectedFields.slice();

                const available = this.fields.filter(
                    field => ! field.visible
                );

                const draggedIndex = selected.findIndex(
                    field => field.uid === draggedUid
                );

                if (draggedIndex === -1) {
                    this.cancelFieldDrag();

                    return;
                }

                if (
                    insertIndex === null
                    || insertIndex === undefined
                ) {
                    this.cancelFieldDrag();

                    return;
                }

                if (insertIndex === draggedIndex) {
                    this.cancelFieldDrag();

                    return;
                }

                const [moving] = selected.splice(draggedIndex, 1);

                const finalIndex = Math.max(
                    0,
                    Math.min(
                        insertIndex,
                        selected.length
                    )
                );

                selected.splice(finalIndex, 0, moving);

                const nextFields = [
                    ...selected,
                    ...available,
                ];

                /*
                |--------------------------------------------------------------------------
                | Limpiar el estado visual antes de actualizar el array
                |--------------------------------------------------------------------------
                */

                this.cancelFieldDrag();

                this.fields = nextFields;

                this.normalizeVisibleOrder();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Limpiar estado del arrastre
            |--------------------------------------------------------------------------
            */

            cancelFieldDrag() {
                if (this.dragHideFrame) {
                    cancelAnimationFrame(this.dragHideFrame);

                    this.dragHideFrame = null;
                }

                this.dragSourceHidden = false;

                this.dragInsertIndex = null;

                this.dragShiftDistance = 0;

                this.draggingUid = null;
            },

            /*
            |--------------------------------------------------------------------------
            | Movimiento directo
            |--------------------------------------------------------------------------
            */

            moveField(draggedUid, targetUid) {
                if (
                    ! draggedUid
                    || ! targetUid
                    || draggedUid === targetUid
                ) {
                    return;
                }

                const selected = this.selectedFields.slice();

                const available = this.fields.filter(
                    field => ! field.visible
                );

                const draggedIndex = selected.findIndex(
                    field => field.uid === draggedUid
                );

                const targetIndex = selected.findIndex(
                    field => field.uid === targetUid
                );

                if (draggedIndex === -1 || targetIndex === -1) {
                    return;
                }

                const [moving] = selected.splice(draggedIndex, 1);

                let insertIndex = targetIndex;

                if (draggedIndex < targetIndex) {
                    insertIndex--;
                }

                insertIndex = Math.max(
                    0,
                    Math.min(
                        insertIndex,
                        selected.length
                    )
                );

                selected.splice(insertIndex, 0, moving);

                this.fields = [
                    ...selected,
                    ...available,
                ];

                this.normalizeVisibleOrder();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Normalizar orden
            |--------------------------------------------------------------------------
            |
            | No ordena el array.
            | Asigna el valor siguiendo la posición actual.
            |
            */

            normalizeVisibleOrder() {
                let order = 10;

                this.fields.forEach(field => {
                    if (! field.visible) {
                        return;
                    }

                    field.orden = order;

                    order += 10;
                });
            },

            /*
            |--------------------------------------------------------------------------
            | Abrir opciones
            |--------------------------------------------------------------------------
            */

            async openOptions(uid) {
                const field = this.findFieldByUid(uid);

                if (! field || field.tipo_control !== 'select') {
                    return;
                }

                this.optionsModalFieldUid = uid;

                this.optionsModalTitle = field.etiqueta
                    || field.nombre_base
                    || 'Lista';

                this.newOptionName = '';

                /*
                |--------------------------------------------------------------------------
                | Lista personalizada
                |--------------------------------------------------------------------------
                */

                if (field.fuente_opciones === 'personalizado') {
                    this.optionsModalEditable = true;

                    this.optionsModalLoading = false;

                    this.optionsModalSource = 'Lista personalizada de '
                        + (this.tipo.nombre ?? '');

                    this.optionsModalItems = this.clone(
                        field.custom_options ?? []
                    );

                    this.optionsModalOpen = true;

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Lista compartida del sistema
                |--------------------------------------------------------------------------
                */

                this.optionsModalEditable = false;

                this.optionsModalLoading = true;

                this.optionsModalSource = 'Cargando opciones...';

                this.optionsModalItems = [];

                this.optionsModalOpen = true;

                try {
                    const result = await this.wire.loadFieldOptions({
                        id_campo: field.id_campo,
                        fuente_opciones: field.fuente_opciones,
                        id_catalogo: field.id_catalogo,
                    });

                    this.optionsModalSource = result?.source
                        ?? 'Lista del sistema';

                    this.optionsModalItems = Array.isArray(result?.items)
                        ? result.items
                        : [];
                } catch (error) {
                    console.error(error);

                    this.optionsModalSource =
                        'No fue posible cargar las opciones.';

                    this.optionsModalItems = [];
                } finally {
                    this.optionsModalLoading = false;
                }
            },

            /*
            |--------------------------------------------------------------------------
            | Cerrar modal de opciones
            |--------------------------------------------------------------------------
            */

            closeOptionsModal() {
                this.optionsModalOpen = false;

                this.optionsModalEditable = false;

                this.optionsModalLoading = false;

                this.optionsModalFieldUid = null;

                this.optionsModalTitle = '';

                this.optionsModalSource = '';

                this.optionsModalItems = [];

                this.newOptionName = '';
            },

            /*
            |--------------------------------------------------------------------------
            | Agregar opción
            |--------------------------------------------------------------------------
            */

            addOption() {
                if (! this.optionsModalEditable) {
                    return;
                }

                const name = this.newOptionName.trim();

                if (name === '') {
                    return;
                }

                const exists = this.optionsModalItems.some(
                    item =>
                        String(item.nombre ?? '')
                            .trim()
                            .toLocaleLowerCase('es')
                        === name.toLocaleLowerCase('es')
                );

                if (exists) {
                    this.showToast(
                        'Esa opción ya existe en la lista.',
                        'warning'
                    );

                    return;
                }

                this.optionsModalItems.push({
                    id_opcion: null,
                    nombre: name,
                });

                this.newOptionName = '';

                this.syncOptionsToField();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Editar opción
            |--------------------------------------------------------------------------
            */

            updateOption(index, value) {
                if (
                    ! this.optionsModalEditable
                    || ! this.optionsModalItems[index]
                ) {
                    return;
                }

                this.optionsModalItems[index].nombre = value;

                this.syncOptionsToField();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Eliminar opción
            |--------------------------------------------------------------------------
            */

            removeOption(index) {
                if (! this.optionsModalEditable) {
                    return;
                }

                if (! this.optionsModalItems[index]) {
                    return;
                }

                this.optionsModalItems.splice(index, 1);

                this.syncOptionsToField();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Reordenar opción
            |--------------------------------------------------------------------------
            */

            moveOption(index, direction) {
                if (! this.optionsModalEditable) {
                    return;
                }

                const target = index + direction;

                if (
                    target < 0
                    || target >= this.optionsModalItems.length
                ) {
                    return;
                }

                const [moving] = this.optionsModalItems.splice(index, 1);

                this.optionsModalItems.splice(target, 0, moving);

                this.syncOptionsToField();

                this.markChanged();
            },

            /*
            |--------------------------------------------------------------------------
            | Sincronizar opciones con el campo
            |--------------------------------------------------------------------------
            */

            syncOptionsToField() {
                const field = this.findFieldByUid(
                    this.optionsModalFieldUid
                );

                if (! field) {
                    return;
                }

                field.custom_options = this.clone(
                    this.optionsModalItems
                );
            },

            /*
            |--------------------------------------------------------------------------
            | Restablecer
            |--------------------------------------------------------------------------
            */

            resetChanges() {
                this.fields = this.clone(this.initialFields);

                this.programas = this.clone(this.initialProgramas);
                this.programasBuscar = '';

                this.hasChanges = false;

                this.cancelFieldDrag();

                this.closeOptionsModal();

                this.showToast(
                    'Los cambios sin guardar fueron descartados.',
                    'info'
                );
            },

            /*
            |--------------------------------------------------------------------------
            | Guardar
            |--------------------------------------------------------------------------
            */

            async saveConfiguration() {
                if (! this.hasChanges || this.saving) {
                    return;
                }

                this.saving = true;

                try {
                    this.normalizeVisibleOrder();

                    const payload = this.clone(this.fields);

                    const result = await this.$wire.$call(
                        'saveConfiguration',
                        payload,
                        this.programas.disponible
                            ? { incluye: this.programas.incluye, seleccion: this.programas.seleccion }
                            : null
                    );

                    console.log('Diagnóstico del guardado:', JSON.stringify({
                        tipoEsperado: this.tipo.id,
                        respuestaUndefined: result === undefined,
                        respuesta: result ?? null,
                    }, null, 2));

                    if (
                        ! result
                        || ! Array.isArray(result.fields)
                        || ! Array.isArray(result.sections)
                        || Number(result.tipo?.id) !== Number(this.tipo.id)
                    ) {
                        throw new Error(
                            'Livewire no devolvió la configuración guardada del tipo de equipo.'
                        );
                    }

                    this.replaceData(result);
                    this.hasChanges = false;

                    // Volver a la tabla de tipos; el aviso de guardado se
                    // muestra al llegar (lo deja el servidor en la sesión).
                    if (result.volver) {
                        window.trackitDescartarCambios?.();

                        window.Livewire?.navigate
                            ? window.Livewire.navigate(result.volver)
                            : (window.location.href = result.volver);

                        return;
                    }

                    this.showToast(
                        'La configuración del formulario se guardó correctamente.',
                        'success'
                    );
                } catch (error) {
                    console.error('Error al guardar el formulario:', error);

                    this.hasChanges = true;

                    this.showToast(
                        'No se pudo confirmar el guardado. Tus cambios siguen pendientes.',
                        'error'
                    );
                } finally {
                    this.saving = false;
                }
            },

            /*
            |--------------------------------------------------------------------------
            | Snapshot
            |--------------------------------------------------------------------------
            */

            snapshot() {
                this.normalizeVisibleOrder();

                return this.clone(this.fields);
            },

            /*
            |--------------------------------------------------------------------------
            | Marcar cambios
            |--------------------------------------------------------------------------
            */

            markChanged() {
                this.hasChanges = true;
            },

            /*
            |--------------------------------------------------------------------------
            | Programas del tipo
            |--------------------------------------------------------------------------
            */

            alternarIncluyeProgramas() {
                this.programas.incluye = ! this.programas.incluye;
                this.markChanged();
            },

            programaElegido(id) {
                return this.programas.seleccion.includes(String(id));
            },

            alternarPrograma(id) {
                const clave = String(id);

                this.programas.seleccion = this.programaElegido(clave)
                    ? this.programas.seleccion.filter(valor => valor !== clave)
                    : [...this.programas.seleccion, clave];

                this.markChanged();
            },

            programasFiltrados() {
                const normalizar = texto => String(texto ?? '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');

                const buscar = normalizar(this.programasBuscar).trim();

                if (buscar === '') {
                    return this.programas.opciones;
                }

                return this.programas.opciones.filter(opcion =>
                    normalizar(opcion.label + ' ' + opcion.detalle + ' ' + opcion.categoria).includes(buscar)
                );
            },

            /*
            |--------------------------------------------------------------------------
            | Buscar índice
            |--------------------------------------------------------------------------
            */

            findIndexByUid(uid) {
                return this.fields.findIndex(
                    field => field.uid === uid
                );
            },

            /*
            |--------------------------------------------------------------------------
            | Buscar campo
            |--------------------------------------------------------------------------
            */

            findFieldByUid(uid) {
                return this.fields.find(
                    field => field.uid === uid
                ) ?? null;
            },

            /*
            |--------------------------------------------------------------------------
            | Etiqueta del control
            |--------------------------------------------------------------------------
            */

            controlLabel(type) {
                const labels = {
                    text: 'Texto',
                    textarea: 'Texto largo',
                    number: 'Número',
                    date: 'Fecha',
                    boolean: 'Sí / No',
                    select: 'Lista',
                };

                return labels[type] ?? type ?? '';
            },

            /*
            |--------------------------------------------------------------------------
            | Etiqueta de almacenamiento
            |--------------------------------------------------------------------------
            */

            storageLabel(origin) {
                const labels = {
                    equipos: 'Sistema',
                    tabla_tipo: 'Específico',
                    atributo: 'Personalizado',
                    asignacion: 'Asignación',
                };

                return labels[origin] ?? 'Disponible';
            },

            /*
            |--------------------------------------------------------------------------
            | Toast
            |--------------------------------------------------------------------------
            */

            showToast(message, type = 'success') {
                this.toastMessage = message;

                this.toastType = type;

                window.trackitToast?.(this.toastType === 'error' ? 'error' : 'success', this.toastMessage); /* aviso global */

                if (this.toastTimer) {
                    clearTimeout(this.toastTimer);
                }

                this.toastTimer = setTimeout(() => {
                    this.toastVisible = false;
                }, 3200);
            },
        };
    };
</script>
@endonce