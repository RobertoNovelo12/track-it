{{-- ============================================================
    CONSTRUCTOR DEL FORMULARIO
============================================================ --}}
<section
    x-data="{
        hoveredSectionId: null,
        hoveredFieldUid: null,
        hoveredAfter: false,

        fieldSectionId(field) {
            const requestedId = Number(field.id_seccion);

            const existing = this.sections.find(
                section => Number(section.id_seccion) === requestedId
            );

            if (existing) {
                return Number(existing.id_seccion);
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

            const key = legacyKeys[legacyKey] ?? 'adicional';

            const fallback = this.sections.find(
                section => section.clave === key
            ) ?? this.sections.find(
                section => section.clave === 'adicional'
            );

            return fallback ? Number(fallback.id_seccion) : null;
        },

        get builderSections() {
            return this.sectionsWithFields.map(section => ({
                ...section,
                fields: this.selectedFields.filter(
                    field => this.fieldSectionId(field)
                        === Number(section.id_seccion)
                ),
            }));
        },

        beginBuilderDrag(uid, event) {
            const field = this.findFieldByUid(uid);

            if (this.saving || !field || !field.visible) {
                event.preventDefault();
                return;
            }

            this.clearBuilderDrag();
            this.draggingUid = uid;

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', String(uid));

                const card = event.currentTarget.closest(
                    '[data-builder-field-uid]'
                );

                if (card) {
                    const rect = card.getBoundingClientRect();

                    event.dataTransfer.setDragImage(
                        card,
                        Math.max(0, Math.min(event.clientX - rect.left, rect.width)),
                        Math.max(0, Math.min(event.clientY - rect.top, rect.height))
                    );
                }
            }
        },

        hoverBuilderSection(sectionId, event) {
            if (!this.draggingUid || this.saving) return;

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }

            this.hoveredSectionId = Number(sectionId);
            this.hoveredFieldUid = null;
            this.hoveredAfter = false;
        },

        hoverBuilderField(sectionId, uid, event) {
            if (!this.draggingUid || this.saving) return;

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }

            const rect = event.currentTarget.getBoundingClientRect();

            this.hoveredSectionId = Number(sectionId);
            this.hoveredFieldUid = uid;
            this.hoveredAfter = event.clientY > rect.top + rect.height / 2;
        },

        leaveBuilderSection(event) {
            const next = event.relatedTarget;

            if (
                next instanceof Node
                && event.currentTarget.contains(next)
            ) {
                return;
            }

            this.hoveredSectionId = null;
            this.hoveredFieldUid = null;
            this.hoveredAfter = false;
        },

        dropOnBuilderField(sectionId, uid, event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const after = event.clientY > rect.top + rect.height / 2;

            this.commitBuilderDrop(sectionId, uid, after);
        },

        commitBuilderDrop(sectionId, targetUid = null, after = false) {
            const section = this.sections.find(
                item => Number(item.id_seccion) === Number(sectionId)
            );

            const moving = this.findFieldByUid(this.draggingUid);

            if (
                this.saving
                || !section
                || !moving
                || !moving.visible
            ) {
                this.clearBuilderDrag();
                return;
            }

            if (targetUid === moving.uid) {
                this.clearBuilderDrag();
                return;
            }

            const selected = this.selectedFields.filter(
                field => field.uid !== moving.uid
            );

            let insertAt;

            if (targetUid !== null) {
                const targetIndex = selected.findIndex(
                    field => field.uid === targetUid
                        && this.fieldSectionId(field)
                            === Number(section.id_seccion)
                );

                if (targetIndex < 0) {
                    this.clearBuilderDrag();
                    return;
                }

                insertAt = targetIndex + (after ? 1 : 0);
            } else {
                insertAt = selected.length;

                for (let index = selected.length - 1; index >= 0; index--) {
                    if (
                        this.fieldSectionId(selected[index])
                        === Number(section.id_seccion)
                    ) {
                        insertAt = index + 1;
                        break;
                    }
                }
            }

            const updatedField = {
                ...moving,
                id_seccion: Number(section.id_seccion),
                seccion: section.clave === 'general'
                    ? 'general'
                    : section.clave === 'informacion_tipo'
                        ? 'especifico'
                        : 'adicional',
            };

            selected.splice(insertAt, 0, updatedField);

            const available = this.fields.filter(
                field => !field.visible
            );

            this.clearBuilderDrag();
            this.fields = [...selected, ...available];
            this.normalizeVisibleOrder();
            this.markChanged();
        },

        clearBuilderDrag() {
            this.hoveredSectionId = null;
            this.hoveredFieldUid = null;
            this.hoveredAfter = false;
            this.cancelFieldDrag();
        },
    }"
    class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]"
>
    {{-- CABECERA --}}
    <div class="flex flex-col gap-3 border-b border-[var(--theme-border)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Formulario de
                <span x-text="tipo.nombre ?? ''"></span>
            </h3>

            <p class="mt-1 text-[10px] text-[var(--theme-text-muted)]">
                <span x-text="selectedFields.length"></span>

                <span
                    x-text="selectedFields.length === 1
                        ? 'campo configurado'
                        : 'campos configurados'"
                ></span>

                <span aria-hidden="true"> · </span>

                <span x-text="sections.length"></span>

                <span
                    x-text="sections.length === 1
                        ? 'sección'
                        : 'secciones'"
                ></span>
            </p>
        </div>

        <p class="max-w-sm text-[10px] text-[var(--theme-text-muted)]">
            Arrastra los campos para ordenarlos o moverlos a otra sección.
            Doble clic en el nombre para editarlo.
        </p>
    </div>

    {{-- SECCIONES --}}
    <div class="min-h-[180px] space-y-4 p-3">
        <template
            x-for="section in builderSections"
            :key="section.id_seccion"
        >
            <section
                :data-builder-section-id="section.id_seccion"
                @dragenter.prevent.stop="hoverBuilderSection(section.id_seccion, $event)"
                @dragover.prevent.stop="hoverBuilderSection(section.id_seccion, $event)"
                @dragleave.stop="leaveBuilderSection($event)"
                @drop.prevent.stop="commitBuilderDrop(section.id_seccion)"
                :class="{
                    'ring-2 ring-[var(--theme-primary)]':
                        draggingUid
                        && hoveredSectionId === Number(section.id_seccion)
                }"
                class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] transition-shadow"
            >
                {{-- CABECERA DE SECCIÓN --}}
                <div class="flex items-center justify-between gap-3 rounded-t-xl border-b border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3">
                    <h4
                        x-text="section.nombre"
                        class="min-w-0 break-words text-sm font-semibold text-[var(--theme-text-strong)]"
                    ></h4>

                    <span
                        x-text="section.fields.length + (
                            section.fields.length === 1
                                ? ' campo'
                                : ' campos'
                        )"
                        class="shrink-0 text-[10px] text-[var(--theme-text-muted)]"
                    ></span>
                </div>

                {{-- CAMPOS --}}
                <div class="space-y-2 p-2">
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
                                    if (!this.editingName) return;

                                    const value = String(
                                        field.etiqueta ?? ''
                                    ).trim();

                                    field.etiqueta = value === ''
                                        ? (field.nombre_base || 'Campo')
                                        : value;

                                    this.editingName = false;
                                    markChanged();
                                },

                                cancelNameEdit() {
                                    field.etiqueta = this.originalName;
                                    this.editingName = false;
                                },
                            }"
                            :data-builder-field-uid="field.uid"
                            @dragenter.prevent.stop="
                                hoverBuilderField(
                                    section.id_seccion,
                                    field.uid,
                                    $event
                                )
                            "
                            @dragover.prevent.stop="
                                hoverBuilderField(
                                    section.id_seccion,
                                    field.uid,
                                    $event
                                )
                            "
                            @drop.prevent.stop="
                                dropOnBuilderField(
                                    section.id_seccion,
                                    field.uid,
                                    $event
                                )
                            "
                            :class="{
                                'opacity-40': draggingUid === field.uid
                            }"
                            class="relative flex flex-col gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 transition-opacity hover:border-[var(--theme-border-strong)] lg:flex-row lg:items-center"
                        >
                            {{-- INDICADOR: INSERTAR ANTES --}}
                            <div
                                x-show="
                                    draggingUid
                                    && draggingUid !== field.uid
                                    && hoveredFieldUid === field.uid
                                    && !hoveredAfter
                                "
                                x-cloak
                                aria-hidden="true"
                                class="pointer-events-none absolute -top-1.5 left-2 right-2 z-10 h-1 rounded-full bg-[var(--theme-primary)]"
                            ></div>

                            {{-- INDICADOR: INSERTAR DESPUÉS --}}
                            <div
                                x-show="
                                    draggingUid
                                    && draggingUid !== field.uid
                                    && hoveredFieldUid === field.uid
                                    && hoveredAfter
                                "
                                x-cloak
                                aria-hidden="true"
                                class="pointer-events-none absolute -bottom-1.5 left-2 right-2 z-10 h-1 rounded-full bg-[var(--theme-primary)]"
                            ></div>

                            {{-- ASA DE ARRASTRE --}}
                            <div
                                :draggable="!saving"
                                @dragstart.stop="beginBuilderDrag(field.uid, $event)"
                                @dragend.stop="clearBuilderDrag()"
                                title="Arrastrar para ordenar o mover a otra sección"
                                class="flex h-9 w-9 shrink-0 cursor-grab select-none items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text-strong)] active:cursor-grabbing"
                            >
                                <svg
                                    class="pointer-events-none h-5 w-5"
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
                                <div
                                    x-show="!editingName"
                                    @dblclick="beginNameEdit()"
                                    title="Doble clic para cambiar el nombre"
                                    class="inline-flex cursor-text flex-wrap items-center gap-2"
                                >
                                    <span
                                        x-text="field.etiqueta || field.nombre_base || 'Campo'"
                                        class="text-sm font-semibold text-[var(--theme-text-strong)]"
                                    ></span>

                                    <span
                                        x-text="controlLabel(field.tipo_control)"
                                        class="rounded-md bg-[var(--theme-surface-soft)] px-2 py-0.5 text-[9px] font-medium text-[var(--theme-text-muted)]"
                                    ></span>

                                    <span
                                        x-text="storageLabel(field.origen_dato)"
                                        class="rounded-md bg-[var(--theme-primary-soft)] px-2 py-0.5 text-[9px] font-medium text-[var(--theme-primary)]"
                                    ></span>

                                    <span
                                        x-show="field.protected"
                                        x-cloak
                                        class="rounded-md border border-[var(--theme-border)] px-2 py-0.5 text-[9px] font-medium text-[var(--theme-text-muted)]"
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
                                        class="h-9 w-full rounded-md border border-[var(--theme-primary)] bg-[var(--theme-surface-soft)] px-3 text-sm font-medium text-[var(--theme-text-strong)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)]"
                                    >

                                    <p class="mt-1 text-[9px] text-[var(--theme-text-muted)]">
                                        Enter para aceptar · Esc para cancelar
                                    </p>
                                </div>

                                {{-- CLAVE --}}
                                <p
                                    x-show="field.clave"
                                    x-text="field.clave"
                                    class="mt-1 font-mono text-[9px] text-[var(--theme-text-muted)]"
                                ></p>

                                {{-- CAMPO NUEVO --}}
                                <p
                                    x-show="field.is_new_custom"
                                    x-cloak
                                    class="mt-1 text-[10px] text-[var(--theme-text-muted)]"
                                >
                                    Campo personalizado nuevo.
                                    Haz doble clic en el nombre para editarlo.
                                </p>

                                {{-- DESCRIPCIÓN --}}
                                <p
                                    x-show="!field.is_new_custom && field.descripcion"
                                    x-text="field.descripcion"
                                    class="mt-1 text-[10px] leading-relaxed text-[var(--theme-text-muted)]"
                                ></p>

                                {{-- OPCIONES DE LISTA --}}
                                <div
                                    x-show="field.tipo_control === 'select'"
                                    x-cloak
                                    class="mt-2"
                                >
                                    <button
                                        type="button"
                                        @click="openOptions(field.uid)"
                                        class="inline-flex items-center gap-1.5 text-[10px] font-medium text-[var(--theme-primary)] hover:underline"
                                    >
                                        <span
                                            x-text="field.fuente_opciones === 'personalizado'
                                                ? 'Editar opciones'
                                                : 'Ver opciones de la lista'"
                                        ></span>

                                        <span
                                            x-show="field.fuente_opciones === 'personalizado'"
                                            x-text="'(' + (
                                                Array.isArray(field.custom_options)
                                                    ? field.custom_options.length
                                                    : 0
                                            ) + ')'"
                                            class="text-[var(--theme-text-muted)]"
                                        ></span>
                                    </button>

                                    <span
                                        x-text="field.fuente_opciones === 'personalizado'
                                            ? 'Lista exclusiva de este formulario'
                                            : 'Lista compartida del sistema'"
                                        class="ml-2 text-[9px] text-[var(--theme-text-muted)]"
                                    ></span>
                                </div>
                            </div>

                            {{-- ACCIONES --}}
                            <div class="flex items-center justify-between gap-4 lg:justify-end lg:pl-3">
                                <button
                                    type="button"
                                    @click="toggleRequired(field.uid)"
                                    :disabled="field.protected || saving"
                                    role="switch"
                                    :aria-checked="field.obligatorio ? 'true' : 'false'"
                                    class="inline-flex items-center gap-2.5 disabled:cursor-not-allowed disabled:opacity-65"
                                >
                                    <span class="text-[11px] font-medium text-[var(--theme-text)]">
                                        Obligatorio
                                    </span>

                                    <span
                                        :class="field.obligatorio
                                            ? 'bg-[var(--theme-primary)]'
                                            : 'bg-[var(--theme-border-strong)]'"
                                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-150"
                                    >
                                        <span
                                            :class="field.obligatorio
                                                ? 'translate-x-5'
                                                : 'translate-x-0'"
                                            class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-150"
                                        ></span>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    @click="removeField(field.uid)"
                                    :disabled="field.protected || saving"
                                    :title="field.protected
                                        ? 'Este campo debe permanecer en el formulario'
                                        : 'Quitar del formulario'"
                                    :aria-label="field.protected
                                        ? 'Este campo debe permanecer en el formulario'
                                        : 'Quitar del formulario'"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-danger)] disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-[var(--theme-text-muted)]"
                                >
                                    <svg
                                        class="h-5 w-5"
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
                        class="rounded-lg border border-dashed border-[var(--theme-border)] px-4 py-10 text-center"
                    >
                        <p
                            x-text="draggingUid
                                ? 'Suelta el campo aquí'
                                : 'Esta sección todavía no tiene campos.'"
                            class="text-xs font-medium text-[var(--theme-text-muted)]"
                        ></p>
                    </div>

                    {{-- ZONA PARA COLOCAR AL FINAL --}}
                    <div
                        x-show="draggingUid && section.fields.length > 0"
                        x-cloak
                        class="rounded-lg border border-dashed border-[var(--theme-border)] px-4 py-4 text-center text-[10px] text-[var(--theme-text-muted)]"
                    >
                        Suelta aquí para colocar al final de esta sección
                    </div>
                </div>
            </section>
        </template>

        {{-- SIN SECCIONES --}}
        <div
            x-show="sections.length === 0"
            x-cloak
            class="px-5 py-12 text-center"
        >
            <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                No se cargaron las secciones del formulario.
            </p>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Vuelve a abrir el constructor para cargar su configuración.
            </p>
        </div>

        {{-- SIN CAMPOS --}}
        <div
            x-show="sections.length > 0 && selectedFields.length === 0"
            x-cloak
            class="px-5 py-6 text-center"
        >
            <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                El formulario está vacío
            </p>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Agrega un tipo de campo desde el panel izquierdo.
            </p>
        </div>
    </div>
</section>