<div>
    <div x-data="catalogCreate" wire:key="catalog-create-ui">

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
            class="fixed z-[150] top-20 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 flex items-start gap-3 bg-[var(--theme-surface)] border border-emerald-500/30 rounded-xl shadow-lg px-4 py-3"
        >
            <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-500/15 text-emerald-500 flex items-center justify-center">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="{{ \App\Support\Icono::url('i-e7e7dec') }}"></use></svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    Registro creado
                </p>

                <p class="mt-0.5 text-xs text-[var(--theme-text)]" x-text="successMessage"></p>
            </div>

            <button
                type="button"
                @click="successVisible = false"
                class="shrink-0 text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] transition-colors"
                aria-label="Cerrar mensaje"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>
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
            class="fixed z-[150] top-20 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 flex items-start gap-3 bg-[var(--theme-surface)] border border-[var(--theme-danger)] rounded-xl shadow-lg px-4 py-3"
        >
            <div class="shrink-0 w-8 h-8 rounded-full bg-[var(--theme-danger-soft)] text-[var(--theme-danger)] flex items-center justify-center">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="{{ \App\Support\Icono::url('i-c96af83') }}"></use></svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    No se pudo guardar
                </p>

                <p class="mt-0.5 text-xs text-[var(--theme-text)]" x-text="errorMessage"></p>
            </div>

            <button
                type="button"
                @click="errorVisible = false"
                class="shrink-0 text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] transition-colors"
                aria-label="Cerrar mensaje"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="{{ \App\Support\Icono::url('i-e0c3579') }}"></use></svg>
            </button>
        </div>

        {{-- ============================================================
            CABECERA
        ============================================================ --}}
        <x-form.page-header
            :volver="false"
            :title="$tipoRegistro === 'modelo' ? 'Nuevo modelo' : 'Nueva marca'"
            :crumbs="$tipoRegistro === 'modelo'
                ? [
                    ['label' => 'Administración', 'href' => route('catalogos.index')],
                    ['label' => 'Modelos', 'href' => route('catalogos.index', ['section' => 'modelos'])],
                    ['label' => 'Nuevo modelo'],
                ]
                : [
                    ['label' => 'Administración', 'href' => route('catalogos.index')],
                    ['label' => 'Marcas', 'href' => route('catalogos.index', ['section' => 'marcas'])],
                    ['label' => 'Nueva marca'],
                ]"
        />

        {{-- ============================================================
            FORMULARIO
        ============================================================ --}}
        <form
            data-avisar-cambios @submit.prevent="saveRecord()" class="relative">
            <fieldset class="space-y-5">

                {{-- ====================================================
                    INFORMACIÓN GENERAL
                ==================================================== --}}
                <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                    <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                        Información general
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                        {{-- Tipo (marca o modelo): lo decide el botón «Añadir marca» /
                             «Añadir modelo» (?tipo=); ya no se elige aquí. --}}

                        {{-- CÓDIGO INTERNO --}}
                        <div class="min-w-0">
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Código interno
                            </label>

                            <div class="relative">
                                <input
                                    type="text"
                                    value="Se genera automáticamente"
                                    readonly
                                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] pr-9 cursor-not-allowed"
                                >

                                <svg
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--theme-text-muted)]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                ><use href="{{ \App\Support\Icono::url('i-6e570dc') }}"></use></svg>
                            </div>
                        </div>

                        {{-- ESTADO INICIAL --}}
                        <div class="min-w-0">
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Estado inicial
                            </label>

                            <div class="min-h-[42px] flex items-center px-3 py-2.5 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)]">
                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-[var(--theme-success-soft)] text-[var(--theme-success)] text-[10px] font-medium">
                                    Activo
                                </span>
                            </div>
                        </div>

                        {{-- NOMBRE --}}
                        <div class="min-w-0">
                            <label
                                for="catalog-name"
                                class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                            >
                                <span
                                    x-text="
                                        recordType === 'modelo'
                                            ? 'Nombre del modelo'
                                            : 'Nombre de la marca'
                                    "
                                ></span>
                                *
                            </label>

                            <input
                                id="catalog-name"
                                type="text"
                                wire:model="nombre"
                                :placeholder="
                                    recordType === 'modelo'
                                        ? 'Ej. Latitude 5440'
                                        : 'Ej. Apple'
                                "
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                            >

                            <p
                                x-show="fieldError('nombre')"
                                x-cloak
                                x-text="fieldError('nombre')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- ====================================================
                            CAMPOS DE MODELO
                        ==================================================== --}}
                        <div
                            x-show="recordType === 'modelo'"
                            x-cloak
                            class="min-w-0"
                        >
                            <x-searchable-select
                                wire-model="idMarca"
                                :options="$marcas
                                    ->map(fn ($marca) => [
                                        'value' => $marca->id_marca,
                                        'label' => $marca->nombre,
                                    ])
                                    ->values()
                                    ->all()"
                                label="Marca *"
                                placeholder="Buscar marca..."
                            />

                            <p
                                x-show="fieldError('idMarca')"
                                x-cloak
                                x-text="fieldError('idMarca')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        <div
                            x-show="recordType === 'modelo'"
                            x-cloak
                            class="min-w-0"
                        >
                            <x-searchable-select
                                wire-model="idTipoEquipo"
                                :options="$tiposEquipo
                                    ->map(fn ($tipoEquipo) => [
                                        'value' => $tipoEquipo->id_tipo_equipo,
                                        'label' => $tipoEquipo->nombre,
                                    ])
                                    ->values()
                                    ->all()"
                                label="Tipo de dispositivo *"
                                placeholder="Buscar tipo de dispositivo..."
                            />

                            <p
                                x-show="fieldError('idTipoEquipo')"
                                x-cloak
                                x-text="fieldError('idTipoEquipo')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- ====================================================
                            CAMPOS DE MARCA
                        ==================================================== --}}
                        <div
                            x-show="recordType === 'marca'"
                            x-cloak
                            class="min-w-0"
                        >
                            <x-searchable-select
                                wire-model="idPaisOrigen"
                                :options="$paisesOrigen
                                    ->map(fn ($pais) => [
                                        'value' => $pais->id_valor,
                                        'label' => $pais->nombre,
                                    ])
                                    ->values()
                                    ->all()"
                                label="País de origen"
                                placeholder="Seleccionar un país..."
                            />

                            <p
                                x-show="fieldError('idPaisOrigen')"
                                x-cloak
                                x-text="fieldError('idPaisOrigen')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>


                        {{-- PAÍS HEREDADO DEL MODELO --}}
                        <div
                            x-show="recordType === 'modelo'"
                            x-cloak
                            class="min-w-0"
                        >
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                País de origen
                            </label>

                            <input
                                id="catalog-model-country"
                                type="text"
                                value="{{ $paisOrigenModelo ?: 'Se toma de la marca seleccionada' }}"
                                readonly
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] cursor-not-allowed"
                            >
                        </div>

                        {{-- SITIO WEB: completa la fila (2 columnas en marca, 3 en modelo) --}}
                        <div
                            class="min-w-0"
                            x-bind:class="recordType === 'modelo' ? 'md:col-span-2 xl:col-span-3' : 'md:col-span-2'"
                        >
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

                            <p
                                x-show="fieldError('sitioWeb')"
                                x-cloak
                                x-text="fieldError('sitioWeb')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- DESCRIPCIÓN --}}
                        <div class="min-w-0 xl:col-span-3">
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
                                x-bind:placeholder="recordType === 'modelo' ? 'Descripción de este modelo' : 'Descripción de esta marca'"
                                placeholder="{{ $tipoRegistro === 'modelo' ? 'Descripción de este modelo' : 'Descripción de esta marca' }}"
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-y"
                            ></textarea>

                            <p
                                x-show="fieldError('descripcion')"
                                x-cloak
                                x-text="fieldError('descripcion')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>
                    </div>
                </section>

                {{-- ====================================================
                    INFORMACIÓN ADICIONAL
                ==================================================== --}}
                <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
                    <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                        Información adicional
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                        {{-- PROVEEDOR --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idProveedorSugerido"
                                :options="$proveedores
                                    ->map(fn ($proveedor) => [
                                        'value' => $proveedor->id_proveedor,
                                        'label' => $proveedor->nombre,
                                    ])
                                    ->values()
                                    ->all()"
                                label="Proveedor sugerido"
                                placeholder="Buscar proveedor..."
                            />

                            <p
                                x-show="fieldError('idProveedorSugerido')"
                                x-cloak
                                x-text="fieldError('idProveedorSugerido')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
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

                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[var(--theme-text-muted)]">
                                    meses
                                </span>
                            </div>

                            <p
                                x-show="fieldError('garantiaEstandarMeses')"
                                x-cloak
                                x-text="fieldError('garantiaEstandarMeses')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- CONTACTO --}}
                        <div class="min-w-0">
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Contacto de proveedor
                            </label>

                            <input
                                id="catalog-provider-contact"
                                type="text"
                                value="{{ $contactoProveedor ?: 'Selecciona un proveedor' }}"
                                readonly
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] cursor-not-allowed"
                            >
                        </div>

                        {{-- ÁREA DE USO COMÚN --}}
                        <div
                            x-show="recordType === 'modelo'"
                            x-cloak
                            class="min-w-0"
                        >
                            <x-searchable-select
                                wire-model="idUnidadUsoComun"
                                :options="$unidades
                                    ->map(fn ($hotel) => [
                                        'value' => $hotel->id_unidad,
                                        'label' => $hotel->nombre,
                                    ])
                                    ->values()
                                    ->all()"
                                label="Hotel de uso común"
                                placeholder="Buscar hotel..."
                            />

                            <p
                                x-show="fieldError('idUnidadUsoComun')"
                                x-cloak
                                x-text="fieldError('idUnidadUsoComun')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- COMENTARIOS --}}
                        <div
                            class="min-w-0 md:col-span-2"
                            :class="
                                recordType === 'modelo'
                                    ? 'xl:col-span-2'
                                    : 'xl:col-span-3'
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

                            <p
                                x-show="fieldError('comentarios')"
                                x-cloak
                                x-text="fieldError('comentarios')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>
                    </div>
                </section>

                {{-- ====================================================
                    ACCIONES
                ==================================================== --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-1 pb-2">
                    <a
                        href="{{ route('catalogos.index') }}"
                        wire:navigate.hover
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
                        Guardar registro
                    </x-ui.button>
                </div>
            </fieldset>
        </form>
    </div>

    {{-- ============================================================
        ALPINE
    ============================================================ --}}
    @script
        <script>
            Alpine.data('catalogCreate', () => ({
                recordType: 'marca',

                successVisible: false,
                successMessage: '',
                successTimer: null,

                errorVisible: false,
                errorMessage: '',
                errorTimer: null,

                savingVisual: false,
                spinnerTimer: null,

                validationErrors: {},

                /*
                |--------------------------------------------------------------------------
                | Inicialización
                |--------------------------------------------------------------------------
                */
                init() {
                    const currentType =
                        String(
                            $wire.tipoRegistro
                            || 'marca'
                        );

                    this.recordType =
                        currentType === 'modelo'
                            ? 'modelo'
                            : 'marca';
                },

                /*
                |--------------------------------------------------------------------------
                | Cambio Marca / Modelo
                |--------------------------------------------------------------------------
                |
                | Importante:
                | - No hacemos request.
                | - Alpine cambia la interfaz inmediatamente.
                | - Livewire conserva el valor localmente para la próxima
                |   petición que realmente sea necesaria.
                |
                */
                changeRecordType(value) {
                    const nextType =
                        value === 'modelo'
                            ? 'modelo'
                            : 'marca';

                    this.recordType =
                        nextType;

                    this.validationErrors = {};
                    this.errorVisible = false;

                    $wire.$set(
                        'tipoRegistro',
                        nextType,
                        false
                    );

                    if (nextType === 'marca') {
                        $wire.$set(
                            'idMarca',
                            null,
                            false
                        );

                        $wire.$set(
                            'idTipoEquipo',
                            null,
                            false
                        );

                        $wire.$set(
                            'idUnidadUsoComun',
                            null,
                            false
                        );

                        this.setInputValue(
                            'catalog-model-country',
                            'Se toma de la marca seleccionada'
                        );
                    } else {
                        $wire.$set(
                            'idPaisOrigen',
                            null,
                            false
                        );
                    }

                    this.$nextTick(() => {
                        const input =
                            document.getElementById(
                                'catalog-name'
                            );

                        if (input) {
                            input.focus();
                        }
                    });
                },

                /*
                |--------------------------------------------------------------------------
                | Toast éxito
                |--------------------------------------------------------------------------
                */
                showSuccess(message) {
                    this.successMessage =
                        message
                        || 'El registro se creó correctamente.';

                    window.trackitToast?.('success', this.successMessage); /* aviso global */

                    if (this.successTimer) {
                        clearTimeout(
                            this.successTimer
                        );
                    }

                    this.successTimer =
                        setTimeout(() => {
                            this.successVisible = false;
                        }, 3200);
                },

                /*
                |--------------------------------------------------------------------------
                | Toast error
                |--------------------------------------------------------------------------
                */
                showError(message) {
                    this.errorMessage =
                        message
                        || 'No fue posible guardar el registro.';

                    window.trackitToast?.('error', this.errorMessage); /* aviso global */

                    if (this.errorTimer) {
                        clearTimeout(
                            this.errorTimer
                        );
                    }

                    this.errorTimer =
                        setTimeout(() => {
                            this.errorVisible = false;
                        }, 5000);
                },

                /*
                |--------------------------------------------------------------------------
                | Primer error
                |--------------------------------------------------------------------------
                */
                firstError(error) {
                    const errors =
                        error && error.errors
                            ? error.errors
                            : null;

                    if (
                        !errors
                        || typeof errors !== 'object'
                    ) {
                        return null;
                    }

                    const keys =
                        Object.keys(errors);

                    if (keys.length === 0) {
                        return null;
                    }

                    const value =
                        errors[keys[0]];

                    if (Array.isArray(value)) {
                        return value.length > 0
                            ? value[0]
                            : null;
                    }

                    return typeof value === 'string'
                        ? value
                        : null;
                },

                /*
                |--------------------------------------------------------------------------
                | Error por campo
                |--------------------------------------------------------------------------
                */
                fieldError(field) {
                    if (
                        !this.validationErrors
                        || !this.validationErrors[field]
                    ) {
                        return null;
                    }

                    const value =
                        this.validationErrors[field];

                    if (Array.isArray(value)) {
                        return value.length > 0
                            ? value[0]
                            : null;
                    }

                    return typeof value === 'string'
                        ? value
                        : null;
                },

                /*
                |--------------------------------------------------------------------------
                | Obtener valor del DOM
                |--------------------------------------------------------------------------
                */
                inputValue(id) {
                    const element =
                        document.getElementById(id);

                    if (!element) {
                        return '';
                    }

                    return element.value ?? '';
                },

                /*
                |--------------------------------------------------------------------------
                | Asignar valor al DOM
                |--------------------------------------------------------------------------
                */
                setInputValue(id, value) {
                    const element =
                        document.getElementById(id);

                    if (!element) {
                        return;
                    }

                    element.value =
                        value ?? '';
                },

                /*
                |--------------------------------------------------------------------------
                | Entero nullable
                |--------------------------------------------------------------------------
                */
                nullableInteger(value) {
                    if (
                        value === null
                        || value === undefined
                        || value === ''
                    ) {
                        return null;
                    }

                    const parsed =
                        Number.parseInt(
                            String(value),
                            10
                        );

                    return Number.isNaN(parsed)
                        ? null
                        : parsed;
                },

                /*
                |--------------------------------------------------------------------------
                | Capturar snapshot
                |--------------------------------------------------------------------------
                */
                captureSnapshot() {
                    return {
                        payload: {
                            tipoRegistro:
                                this.recordType,

                            nombre:
                                String(
                                    this.inputValue(
                                        'catalog-name'
                                    )
                                ),

                            descripcion:
                                String(
                                    this.inputValue(
                                        'catalog-description'
                                    )
                                ),

                            sitioWeb:
                                String(
                                    this.inputValue(
                                        'catalog-web'
                                    )
                                ),

                            idPaisOrigen:
                                this.nullableInteger(
                                    $wire.idPaisOrigen
                                ),

                            idMarca:
                                this.nullableInteger(
                                    $wire.idMarca
                                ),

                            idTipoEquipo:
                                this.nullableInteger(
                                    $wire.idTipoEquipo
                                ),

                            idProveedorSugerido:
                                this.nullableInteger(
                                    $wire.idProveedorSugerido
                                ),

                            garantiaEstandarMeses:
                                this.nullableInteger(
                                    this.inputValue(
                                        'catalog-warranty'
                                    )
                                ),

                            idUnidadUsoComun:
                                this.nullableInteger(
                                    $wire.idUnidadUsoComun
                                ),

                            comentarios:
                                String(
                                    this.inputValue(
                                        'catalog-comments'
                                    )
                                ),
                        },

                        ui: {
                            providerContact:
                                String(
                                    this.inputValue(
                                        'catalog-provider-contact'
                                    )
                                ),

                            modelCountry:
                                String(
                                    this.inputValue(
                                        'catalog-model-country'
                                    )
                                ),
                        },
                    };
                },

                /*
                |--------------------------------------------------------------------------
                | Validación inmediata
                |--------------------------------------------------------------------------
                */
                validateSnapshot(payload) {
                    const errors = {};

                    if (
                        String(
                            payload.nombre ?? ''
                        ).trim() === ''
                    ) {
                        errors.nombre = [
                            payload.tipoRegistro === 'modelo'
                                ? 'Ingresa el nombre del modelo.'
                                : 'Ingresa el nombre de la marca.'
                        ];
                    }

                    if (
                        payload.tipoRegistro === 'modelo'
                        && payload.idMarca === null
                    ) {
                        errors.idMarca = [
                            'Selecciona una marca.'
                        ];
                    }

                    if (
                        payload.tipoRegistro === 'modelo'
                        && payload.idTipoEquipo === null
                    ) {
                        errors.idTipoEquipo = [
                            'Selecciona un tipo de dispositivo.'
                        ];
                    }

                    this.validationErrors =
                        errors;

                    const keys =
                        Object.keys(errors);

                    if (keys.length === 0) {
                        return true;
                    }

                    const first =
                        errors[keys[0]];

                    this.showError(
                        Array.isArray(first)
                            ? first[0]
                            : 'Revisa los campos requeridos.'
                    );

                    return false;
                },

                /*
                |--------------------------------------------------------------------------
                | Spinner breve
                |--------------------------------------------------------------------------
                */
                pulseSpinner() {
                    this.savingVisual = true;

                    if (this.spinnerTimer) {
                        clearTimeout(
                            this.spinnerTimer
                        );
                    }

                    this.spinnerTimer =
                        setTimeout(() => {
                            this.savingVisual = false;
                        }, 350);
                },

                /*
                |--------------------------------------------------------------------------
                | Limpieza optimista
                |--------------------------------------------------------------------------
                */
                clearFormInstantly(type) {
                    this.validationErrors = {};

                    this.setInputValue(
                        'catalog-name',
                        ''
                    );

                    this.setInputValue(
                        'catalog-web',
                        ''
                    );

                    this.setInputValue(
                        'catalog-description',
                        ''
                    );

                    this.setInputValue(
                        'catalog-warranty',
                        ''
                    );

                    this.setInputValue(
                        'catalog-comments',
                        ''
                    );

                    $wire.$set(
                        'nombre',
                        '',
                        false
                    );

                    $wire.$set(
                        'descripcion',
                        '',
                        false
                    );

                    $wire.$set(
                        'sitioWeb',
                        '',
                        false
                    );

                    $wire.$set(
                        'idProveedorSugerido',
                        null,
                        false
                    );

                    $wire.$set(
                        'garantiaEstandarMeses',
                        null,
                        false
                    );

                    $wire.$set(
                        'comentarios',
                        '',
                        false
                    );

                    $wire.$set(
                        'idPaisOrigen',
                        null,
                        false
                    );

                    $wire.$set(
                        'idMarca',
                        null,
                        false
                    );

                    $wire.$set(
                        'idTipoEquipo',
                        null,
                        false
                    );

                    $wire.$set(
                        'idUnidadUsoComun',
                        null,
                        false
                    );

                    this.setInputValue(
                        'catalog-provider-contact',
                        'Selecciona un proveedor'
                    );

                    if (type === 'modelo') {
                        this.setInputValue(
                            'catalog-model-country',
                            'Se toma de la marca seleccionada'
                        );
                    }

                    this.$nextTick(() => {
                        const input =
                            document.getElementById(
                                'catalog-name'
                            );

                        if (input) {
                            input.focus();
                        }
                    });
                },

                /*
                |--------------------------------------------------------------------------
                | Comprobar si el usuario ya empezó otro registro
                |--------------------------------------------------------------------------
                */
                formIsStillEmpty(type) {
                    /*
                     * Si el usuario ya cambió Marca / Modelo mientras la
                     * petición anterior estaba trabajando, no restauramos
                     * información vieja.
                     */
                    if (
                        this.recordType !== type
                    ) {
                        return false;
                    }

                    const textFields = [
                        'catalog-name',
                        'catalog-web',
                        'catalog-description',
                        'catalog-warranty',
                        'catalog-comments',
                    ];

                    for (
                        const field
                        of textFields
                    ) {
                        if (
                            String(
                                this.inputValue(field)
                            ).trim() !== ''
                        ) {
                            return false;
                        }
                    }

                    if (
                        $wire.idProveedorSugerido !== null
                        && $wire.idProveedorSugerido !== ''
                    ) {
                        return false;
                    }

                    if (type === 'marca') {
                        return (
                            $wire.idPaisOrigen === null
                            || $wire.idPaisOrigen === ''
                        );
                    }

                    return (
                        (
                            $wire.idMarca === null
                            || $wire.idMarca === ''
                        )
                        &&
                        (
                            $wire.idTipoEquipo === null
                            || $wire.idTipoEquipo === ''
                        )
                        &&
                        (
                            $wire.idUnidadUsoComun === null
                            || $wire.idUnidadUsoComun === ''
                        )
                    );
                },

                /*
                |--------------------------------------------------------------------------
                | Restaurar snapshot si falla
                |--------------------------------------------------------------------------
                */
                restoreSnapshot(snapshot) {
                    const payload =
                        snapshot.payload;

                    this.recordType =
                        payload.tipoRegistro;

                    $wire.$set(
                        'tipoRegistro',
                        payload.tipoRegistro,
                        false
                    );

                    this.setInputValue(
                        'catalog-name',
                        payload.nombre
                    );

                    this.setInputValue(
                        'catalog-web',
                        payload.sitioWeb
                    );

                    this.setInputValue(
                        'catalog-description',
                        payload.descripcion
                    );

                    this.setInputValue(
                        'catalog-warranty',
                        payload.garantiaEstandarMeses
                    );

                    this.setInputValue(
                        'catalog-comments',
                        payload.comentarios
                    );

                    $wire.$set(
                        'nombre',
                        payload.nombre,
                        false
                    );

                    $wire.$set(
                        'descripcion',
                        payload.descripcion,
                        false
                    );

                    $wire.$set(
                        'sitioWeb',
                        payload.sitioWeb,
                        false
                    );

                    $wire.$set(
                        'idProveedorSugerido',
                        payload.idProveedorSugerido,
                        false
                    );

                    $wire.$set(
                        'garantiaEstandarMeses',
                        payload.garantiaEstandarMeses,
                        false
                    );

                    $wire.$set(
                        'comentarios',
                        payload.comentarios,
                        false
                    );

                    $wire.$set(
                        'idPaisOrigen',
                        payload.idPaisOrigen,
                        false
                    );

                    $wire.$set(
                        'idMarca',
                        payload.idMarca,
                        false
                    );

                    $wire.$set(
                        'idTipoEquipo',
                        payload.idTipoEquipo,
                        false
                    );

                    $wire.$set(
                        'idUnidadUsoComun',
                        payload.idUnidadUsoComun,
                        false
                    );

                    if (
                        snapshot.ui
                        && snapshot.ui.providerContact
                    ) {
                        this.setInputValue(
                            'catalog-provider-contact',
                            snapshot.ui.providerContact
                        );
                    }

                    if (
                        payload.tipoRegistro === 'modelo'
                        && snapshot.ui
                        && snapshot.ui.modelCountry
                    ) {
                        this.setInputValue(
                            'catalog-model-country',
                            snapshot.ui.modelCountry
                        );
                    }

                    this.$nextTick(() => {
                        const input =
                            document.getElementById(
                                'catalog-name'
                            );

                        if (input) {
                            input.focus();
                        }
                    });
                },

                /*
                |--------------------------------------------------------------------------
                | Guardado optimista
                |--------------------------------------------------------------------------
                */
                async saveRecord() {
                    const snapshot =
                        this.captureSnapshot();

                    const payload =
                        snapshot.payload;

                    if (
                        !this.validateSnapshot(
                            payload
                        )
                    ) {
                        return;
                    }

                    this.errorVisible = false;

                    this.pulseSpinner();

                    let request;

                    try {
                        request =
                            $wire.save(
                                payload
                            );
                    } catch (error) {
                        this.savingVisual = false;

                        this.showError(
                            'No fue posible iniciar el guardado.'
                        );

                        return;
                    }

                    /*
                     * Aquí se limpia antes de esperar al servidor.
                     */
                    this.clearFormInstantly(
                        payload.tipoRegistro
                    );

                    try {
                        const result =
                            await request;

                        if (
                            !result
                            || result.ok !== true
                        ) {
                            throw new Error(
                                'Respuesta de guardado no válida.'
                            );
                        }

                        this.showSuccess(
                            result.message
                                || 'El registro se creó correctamente.'
                        );
                    } catch (error) {
                        const serverErrors =
                            error
                            && error.errors
                                ? error.errors
                                : {};

                        const validationMessage =
                            this.firstError(
                                error
                            );

                        if (
                            this.formIsStillEmpty(
                                payload.tipoRegistro
                            )
                        ) {
                            this.restoreSnapshot(
                                snapshot
                            );

                            this.validationErrors =
                                serverErrors;
                        } else {
                            this.validationErrors = {};
                        }

                        this.showError(
                            validationMessage
                                || window.trackitMensajeError(error, 'guardar el registro')
                        );
                    }
                },
            }));
        </script>
    @endscript
</div>