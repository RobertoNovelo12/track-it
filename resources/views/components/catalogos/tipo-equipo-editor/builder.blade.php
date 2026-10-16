{{-- ============================================================
    CONSTRUCTOR DEL FORMULARIO
============================================================ --}}
<section
    x-data="{
        fieldSectionId(field) {
            const requestedId = Number(field.id_seccion);

            const existingSection = this.sections.find(
                section => section.id_seccion === requestedId
            );

            if (existingSection) {
                return existingSection.id_seccion;
            }

            const legacyKeys = {
                general: 'general',
                compra: 'general',
                especifico: 'informacion_tipo',
                ubicacion: 'adicional',
                adicional: 'adicional',
            };

            const legacyKey = String(field.seccion ?? 'adicional')
                .trim()
                .toLocaleLowerCase('es');

            const sectionKey = legacyKeys[legacyKey] ?? 'adicional';

            const fallbackSection = this.sections.find(
                section => section.clave === sectionKey
            ) ?? this.sections.find(
                section => section.clave === 'adicional'
            );

            return fallbackSection?.id_seccion ?? null;
        },

        get builderSections() {
            const selected = this.selectedFields;

            return this.sectionsWithFields.map(section => ({
                ...section,

                fields: selected.filter(
                    field => this.fieldSectionId(field) === section.id_seccion
                ),
            }));
        },

        guardSectionDrag(sectionId) {
            if (! this.draggingUid) {
                return;
            }

            const moving = this.findFieldByUid(this.draggingUid);

            if (! moving) {
                return;
            }

            if (this.fieldSectionId(moving) !== sectionId) {
                this.dragInsertIndex = this.selectedFields.findIndex(
                    field => field.uid === this.draggingUid
                );
            }
        },

        updateSectionDragTarget(sectionId, uid, event) {
            if (! this.draggingUid) {
                return;
            }

            const moving = this.findFieldByUid(this.draggingUid);

            if (! moving) {
                return;
            }

            if (this.fieldSectionId(moving) !== sectionId) {
                this.guardSectionDrag(sectionId);

                return;
            }

            this.updateDragTarget(uid, event);
        },

        sectionDragStyle(field) {
            if (! this.draggingUid) {
                return '';
            }

            const moving = this.findFieldByUid(this.draggingUid);

            if (
                ! moving
                || this.fieldSectionId(moving) !== this.fieldSectionId(field)
            ) {
                return '';
            }

            return this.dragVisualStyle(field.uid);
        },
    }"

    class="
        overflow-hidden
        rounded-xl
        border
        border-[var(--theme-border)]
        bg-[var(--theme-surface)]
    "
>
    {{-- CABECERA --}}
    <div
        class="
            px-5 py-4
            border-b border-[var(--theme-border)]
            flex flex-col
            sm:flex-row sm:items-center sm:justify-between
            gap-3
        "
    >
        <div>
            <h3
                class="
                    text-sm font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                Formulario de
                <span x-text="tipo.nombre ?? ''"></span>
            </h3>

            <p
                class="
                    mt-1
                    text-[10px]
                    text-[var(--theme-text-muted)]
                "
            >
                <span x-text="selectedFields.length"></span>

                <span
                    x-text="
                        selectedFields.length === 1
                            ? 'campo configurado'
                            : 'campos configurados'
                    "
                ></span>

                <span aria-hidden="true"> · </span>

                <span x-text="sections.length"></span>

                <span
                    x-text="
                        sections.length === 1
                            ? 'sección'
                            : 'secciones'
                    "
                ></span>
            </p>
        </div>

        <div
            class="
                flex items-center gap-2
                text-[10px]
                text-[var(--theme-text-muted)]
            "
        >
            <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
            >
                <circle cx="8" cy="5" r="1.3" />
                <circle cx="16" cy="5" r="1.3" />
                <circle cx="8" cy="12" r="1.3" />
                <circle cx="16" cy="12" r="1.3" />
                <circle cx="8" cy="19" r="1.3" />
                <circle cx="16" cy="19" r="1.3" />
            </svg>

            <span class="hidden sm:inline">
                Arrastra dentro de cada sección · doble clic para cambiar el nombre
            </span>

            <span class="sm:hidden">
                Arrastra dentro de cada sección
            </span>
        </div>
    </div>

    {{-- SECCIONES --}}
    <div
        @dragenter.prevent
        @dragover.prevent
        @drop.prevent="finishFieldDrop()"

        class="p-3 min-h-[180px] space-y-4"
    >
        <template
            x-for="section in builderSections"
            :key="section.id_seccion"
        >
            <section
                :data-builder-section-id="section.id_seccion"

                @dragenter.prevent

                @dragover.prevent.stop="
                    guardSectionDrag(section.id_seccion)
                "

                @drop.prevent.stop="
                    guardSectionDrag(section.id_seccion);
                    finishFieldDrop()
                "

                class="
                    rounded-xl
                    border border-[var(--theme-border)]
                    bg-[var(--theme-surface)]
                "
            >
                {{-- CABECERA DE SECCIÓN --}}
                <div
                    class="
                        flex items-center justify-between gap-3
                        px-4 py-3
                        rounded-t-xl
                        border-b border-[var(--theme-border)]
                        bg-[var(--theme-surface-soft)]
                    "
                >
                    <h4
                        x-text="section.nombre"

                        class="
                            min-w-0
                            text-sm font-semibold
                            text-[var(--theme-text-strong)]
                            break-words
                        "
                    ></h4>

                    <span
                        x-text="
                            section.fields.length
                            + (
                                section.fields.length === 1
                                    ? ' campo'
                                    : ' campos'
                            )
                        "

                        class="
                            shrink-0
                            text-[10px]
                            text-[var(--theme-text-muted)]
                        "
                    ></span>
                </div>

                {{-- CAMPOS DE LA SECCIÓN --}}
                <div class="p-2 space-y-2">
                    <template
                        x-for="field in section.fields"
                        :key="field.uid"
                    >
                        <article
                            x-data="{
                                editingName: false,

                                originalName: '',

                                beginNameEdit() {
                                    this.originalName = field.etiqueta ?? '';

                                    this.editingName = true;

                                    this.$nextTick(() => {
                                        this.$refs.nameInput.focus();
                                        this.$refs.nameInput.select();
                                    });
                                },

                                finishNameEdit() {
                                    const value = String(
                                        field.etiqueta ?? ''
                                    ).trim();

                                    if (value === '') {
                                        field.etiqueta = field.nombre_base || 'Campo';
                                    } else {
                                        field.etiqueta = value;
                                    }

                                    this.editingName = false;

                                    markChanged();
                                },

                                cancelNameEdit() {
                                    field.etiqueta = this.originalName;

                                    this.editingName = false;
                                },
                            }"

                            :data-builder-field-uid="field.uid"

                            @dragenter.prevent

                            @dragover.prevent.stop="
                                updateSectionDragTarget(
                                    section.id_seccion,
                                    field.uid,
                                    $event
                                )
                            "

                            @drop.prevent.stop="
                                guardSectionDrag(section.id_seccion);
                                finishFieldDrop(field.uid, $event)
                            "

                            :style="sectionDragStyle(field)"

                            :class="{
                                'opacity-60 scale-[0.985] border-[var(--theme-primary)] ring-2 ring-[var(--theme-primary-soft)]':
                                    draggingUid === field.uid
                            }"

                            class="
                                group
                                flex flex-col
                                lg:flex-row lg:items-center
                                gap-3
                                p-4
                                rounded-xl
                                border border-[var(--theme-border)]
                                bg-[var(--theme-surface)]
                                hover:border-[var(--theme-border-strong)]
                                transition-[transform,border-color,box-shadow,opacity]
                                duration-150 ease-out
                                will-change-transform
                            "
                        >
                            {{-- ASA DE ARRASTRE --}}
                            <div
                                draggable="true"

                                @dragstart="startDrag(field.uid, $event)"

                                @dragend="finishFieldDrop()"

                                class="
                                    w-9 h-9 shrink-0
                                    flex items-center justify-center
                                    rounded-md
                                    text-[var(--theme-text-muted)]
                                    hover:bg-[var(--theme-surface-soft)]
                                    hover:text-[var(--theme-text-strong)]
                                    cursor-grab active:cursor-grabbing
                                    select-none
                                    transition-colors
                                "

                                title="Arrastrar para ordenar dentro de esta sección"
                            >
                                <svg
                                    class="w-5 h-5"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <circle cx="8" cy="5" r="1.35" />
                                    <circle cx="16" cy="5" r="1.35" />
                                    <circle cx="8" cy="12" r="1.35" />
                                    <circle cx="16" cy="12" r="1.35" />
                                    <circle cx="8" cy="19" r="1.35" />
                                    <circle cx="16" cy="19" r="1.35" />
                                </svg>
                            </div>

                            {{-- INFORMACIÓN --}}
                            <div class="min-w-0 flex-1">
                                {{-- NOMBRE NORMAL --}}
                                <div
                                    x-show="! editingName"

                                    @dblclick="beginNameEdit()"

                                    class="
                                        inline-flex flex-wrap items-center
                                        gap-2
                                        cursor-text
                                    "

                                    title="Doble clic para cambiar el nombre"
                                >
                                    <span
                                        x-text="
                                            field.etiqueta
                                            || field.nombre_base
                                            || 'Campo'
                                        "

                                        class="
                                            text-sm font-semibold
                                            text-[var(--theme-text-strong)]
                                        "
                                    ></span>

                                    {{-- TIPO --}}
                                    <span
                                        x-text="controlLabel(field.tipo_control)"

                                        class="
                                            px-2 py-0.5
                                            rounded-md
                                            bg-[var(--theme-surface-soft)]
                                            text-[9px] font-medium
                                            text-[var(--theme-text-muted)]
                                        "
                                    ></span>

                                    {{-- ORIGEN --}}
                                    <span
                                        x-text="storageLabel(field.origen_dato)"

                                        class="
                                            px-2 py-0.5
                                            rounded-md
                                            bg-[var(--theme-primary-soft)]
                                            text-[var(--theme-primary)]
                                            text-[9px] font-medium
                                        "
                                    ></span>

                                    {{-- PROTEGIDO --}}
                                    <span
                                        x-show="field.protected"
                                        x-cloak

                                        class="
                                            px-2 py-0.5
                                            rounded-md
                                            border border-[var(--theme-border)]
                                            text-[9px] font-medium
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        Protegido
                                    </span>
                                </div>

                                {{-- NOMBRE EDITABLE --}}
                                <div
                                    x-show="editingName"
                                    x-cloak
                                    class="max-w-md"
                                >
                                    <input
                                        x-ref="nameInput"
                                        type="text"

                                        x-model="field.etiqueta"

                                        @input="markChanged()"

                                        @keydown.enter.prevent="finishNameEdit()"

                                        @keydown.escape.prevent="cancelNameEdit()"

                                        @blur="finishNameEdit()"

                                        maxlength="150"

                                        class="
                                            w-full h-9
                                            px-3
                                            rounded-md
                                            border border-[var(--theme-primary)]
                                            bg-[var(--theme-surface-soft)]
                                            text-sm font-medium
                                            text-[var(--theme-text-strong)]
                                            focus:outline-none
                                            focus:ring-2
                                            focus:ring-[var(--theme-primary-soft)]
                                        "
                                    >

                                    <p
                                        class="
                                            mt-1
                                            text-[9px]
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        Enter para aceptar · Esc para cancelar
                                    </p>
                                </div>

                                {{-- CLAVE --}}
                                <p
                                    x-show="field.clave"
                                    x-text="field.clave"

                                    class="
                                        mt-1
                                        font-mono text-[9px]
                                        text-[var(--theme-text-muted)]
                                    "
                                ></p>

                                {{-- CAMPO NUEVO --}}
                                <p
                                    x-show="field.is_new_custom"
                                    x-cloak

                                    class="
                                        mt-1
                                        text-[10px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Campo personalizado nuevo.
                                    Haz doble clic en el nombre para editarlo.
                                </p>

                                {{-- DESCRIPCIÓN --}}
                                <p
                                    x-show="
                                        ! field.is_new_custom
                                        && field.descripcion
                                    "

                                    x-text="field.descripcion"

                                    class="
                                        mt-1
                                        text-[10px] leading-relaxed
                                        text-[var(--theme-text-muted)]
                                    "
                                ></p>

                                {{-- LISTA --}}
                                <div
                                    x-show="field.tipo_control === 'select'"
                                    x-cloak
                                    class="mt-2"
                                >
                                    <button
                                        type="button"

                                        @click="openOptions(field.uid)"

                                        class="
                                            inline-flex items-center gap-1.5
                                            text-[10px] font-medium
                                            text-[var(--theme-primary)]
                                            hover:underline
                                        "
                                    >
                                        <span
                                            x-show="
                                                field.fuente_opciones
                                                    === 'personalizado'
                                            "
                                            x-cloak
                                        >
                                            Editar opciones
                                        </span>

                                        <span
                                            x-show="
                                                field.fuente_opciones
                                                    !== 'personalizado'
                                            "
                                            x-cloak
                                        >
                                            Ver opciones de la lista
                                        </span>

                                        <span
                                            x-show="
                                                field.fuente_opciones
                                                    === 'personalizado'
                                            "

                                            x-text="
                                                '('
                                                + (
                                                    Array.isArray(field.custom_options)
                                                        ? field.custom_options.length
                                                        : 0
                                                )
                                                + ')'
                                            "

                                            class="text-[var(--theme-text-muted)]"
                                        ></span>
                                    </button>

                                    <span
                                        x-show="
                                            field.fuente_opciones
                                                === 'personalizado'
                                        "
                                        x-cloak

                                        class="
                                            ml-2
                                            text-[9px]
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        Lista exclusiva de este formulario
                                    </span>

                                    <span
                                        x-show="
                                            field.fuente_opciones
                                                !== 'personalizado'
                                        "
                                        x-cloak

                                        class="
                                            ml-2
                                            text-[9px]
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        Lista compartida del sistema
                                    </span>
                                </div>
                            </div>

                            {{-- ACCIONES --}}
                            <div
                                class="
                                    flex items-center justify-between
                                    lg:justify-end
                                    gap-4
                                    lg:pl-3
                                "
                            >
                                {{-- OBLIGATORIO --}}
                                <button
                                    type="button"

                                    @click="toggleRequired(field.uid)"

                                    :disabled="field.protected"

                                    role="switch"

                                    :aria-checked="
                                        field.obligatorio ? 'true' : 'false'
                                    "

                                    class="
                                        inline-flex items-center gap-2.5
                                        disabled:cursor-not-allowed
                                        disabled:opacity-65
                                    "
                                >
                                    <span
                                        class="
                                            text-[11px] font-medium
                                            text-[var(--theme-text)]
                                        "
                                    >
                                        Obligatorio
                                    </span>

                                    <span
                                        :class="
                                            field.obligatorio
                                                ? 'bg-[var(--theme-primary)]'
                                                : 'bg-[var(--theme-border-strong)]'
                                        "

                                        class="
                                            relative inline-flex
                                            w-11 h-6 shrink-0
                                            rounded-full
                                            transition-colors duration-150
                                        "
                                    >
                                        <span
                                            :class="
                                                field.obligatorio
                                                    ? 'translate-x-5'
                                                    : 'translate-x-0'
                                            "

                                            class="
                                                absolute top-0.5 left-0.5
                                                w-5 h-5
                                                rounded-full
                                                bg-white shadow-sm
                                                transition-transform duration-150
                                            "
                                        ></span>
                                    </span>
                                </button>

                                {{-- QUITAR --}}
                                <button
                                    type="button"

                                    @click="removeField(field.uid)"

                                    :disabled="field.protected"

                                    :title="
                                        field.protected
                                            ? 'Este campo debe permanecer en el formulario'
                                            : 'Quitar del formulario'
                                    "

                                    :aria-label="
                                        field.protected
                                            ? 'Este campo debe permanecer en el formulario'
                                            : 'Quitar del formulario'
                                    "

                                    class="
                                        w-9 h-9 shrink-0
                                        rounded-md
                                        flex items-center justify-center
                                        text-[var(--theme-text-muted)]
                                        hover:bg-[var(--theme-surface-soft)]
                                        hover:text-[var(--theme-danger)]
                                        disabled:opacity-30
                                        disabled:cursor-not-allowed
                                        disabled:hover:bg-transparent
                                        disabled:hover:text-[var(--theme-text-muted)]
                                        transition-colors
                                    "
                                >
                                    <svg
                                        class="w-5 h-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12h14" />
                                    </svg>
                                </button>
                            </div>
                        </article>
                    </template>

                    {{-- SECCIÓN VACÍA --}}
                    <div
                        x-show="section.fields.length === 0"
                        x-cloak

                        class="
                            px-4 py-8
                            rounded-lg
                            border border-dashed
                            border-[var(--theme-border)]
                            text-center
                        "
                    >
                        <p
                            class="
                                text-xs font-medium
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Esta sección todavía no tiene campos.
                        </p>
                    </div>
                </div>
            </section>
        </template>

        {{-- SIN SECCIONES RECIBIDAS --}}
        <div
            x-show="sections.length === 0"
            x-cloak
            class="px-5 py-12 text-center"
        >
            <p
                class="
                    text-sm font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                No se cargaron las secciones del formulario.
            </p>

            <p
                class="
                    mt-1 text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Vuelve a abrir el constructor para cargar su configuración.
            </p>
        </div>

        {{-- FORMULARIO SIN CAMPOS --}}
        <div
            x-show="
                sections.length > 0
                && selectedFields.length === 0
            "
            x-cloak
            class="px-5 py-6 text-center"
        >
            <p
                class="
                    text-sm font-semibold
                    text-[var(--theme-text-strong)]
                "
            >
                El formulario está vacío
            </p>

            <p
                class="
                    mt-1 text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Agrega un tipo de campo desde el panel izquierdo.
            </p>
        </div>
    </div>
</section>