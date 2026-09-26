<div>
    <div x-data="equipmentCreate" wire:key="equipment-create-ui">
        @php
            $toOptions = fn ($items, string $valueKey, string $labelKey = 'nombre') =>
                collect($items)
                    ->map(fn ($i) => [
                        'value' => $i[$valueKey],
                        'label' => $i[$labelKey],
                    ])
                    ->values()
                    ->all();
        @endphp

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
            class="fixed z-[150] top-20 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 flex items-start gap-3 bg-[var(--theme-surface)] border border-emerald-500/30 rounded-xl shadow-lg px-4 py-3"
        >
            <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-500/15 flex items-center justify-center text-emerald-500">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12l4 4L19 7"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-emerald-500">
                    Registro completado
                </p>

                <p class="mt-0.5 text-xs text-[var(--theme-text)]" x-text="successMessage"></p>
            </div>

            <button
                type="button"
                @click="successVisible = false"
                class="shrink-0 text-emerald-500/60 hover:text-emerald-500 transition-colors"
                aria-label="Cerrar mensaje"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            class="fixed z-[150] top-20 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 flex items-start gap-3 bg-[var(--theme-surface)] border border-[var(--theme-danger)] rounded-xl shadow-lg px-4 py-3"
        >
            <div class="shrink-0 w-8 h-8 rounded-full bg-[var(--theme-danger-soft)] flex items-center justify-center text-[var(--theme-danger)]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v5"/>
                    <path d="M12 17h.01"/>
                    <circle cx="12" cy="12" r="9"/>
                </svg>
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
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            wire:key="equipo-form-{{ $formKey }}"
            class="relative"
        >
            <fieldset class="space-y-4 sm:space-y-6">

                {{-- ====================================================
                    INFORMACIÓN GENERAL
                ==================================================== --}}
                <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-xl p-4 sm:p-6">
                    <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between mb-5">
                        <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                            Información general
                        </h2>

                        <div class="flex items-center gap-2 text-xs text-[var(--theme-text-muted)]">
                            <span>Código de inventario:</span>

                            <span
                                id="equipment-code-preview"
                                class="inline-flex items-center rounded-md bg-[var(--theme-primary-soft)] px-2.5 py-1 font-semibold text-[var(--theme-primary)]"
                            >
                                {{ $this->codigoInventarioPreview }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        {{-- Nombre --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Nombre del equipo *
                            </label>

                            <input
                                type="text"
                                wire:model="nombreEquipo"
                                placeholder="Ej. Laptop del área de RRHH"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >

                            <p
                                x-show="fieldError('nombreEquipo')"
                                x-cloak
                                x-text="fieldError('nombreEquipo')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- Host --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Host
                            </label>

                            <input
                                type="text"
                                wire:model="host"
                                placeholder="Ej. 030-123-456"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >
                        </div>

                        {{-- Estado --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idEstadoActivo"
                                :options="$toOptions($this->estados, 'id_estado_equipo')"
                                label="Estado *"
                                placeholder="Buscar estado..."
                            />

                            <p
                                x-show="fieldError('idEstadoActivo')"
                                x-cloak
                                x-text="fieldError('idEstadoActivo')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- Tipo --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idTipoEquipo"
                                :options="$toOptions($this->tiposEquipo, 'id_tipo_equipo')"
                                label="Tipo de equipo *"
                                placeholder="Buscar tipo de equipo..."
                            />

                            <p
                                x-show="fieldError('idTipoEquipo')"
                                x-cloak
                                x-text="fieldError('idTipoEquipo')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- Marca --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idMarca"
                                :options="$toOptions($this->marcas, 'id_marca')"
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

                        {{-- Modelo --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idModelo"
                                :options="$toOptions($this->modelos, 'id_modelo')"
                                label="Modelo"
                                :placeholder="
                                    ($idMarca === '' || $idTipoEquipo === '')
                                        ? 'Elige marca y tipo primero'
                                        : 'Buscar modelo...'
                                "
                                :disabled="
                                    $idMarca === '' ||
                                    $idTipoEquipo === ''
                                "
                            />
                        </div>

                        {{-- MAC --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Dirección MAC
                            </label>

                            <input
                                type="text"
                                wire:model="direccionMac"
                                placeholder="Ej. 4C:CC:6A:18:28:7D"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >
                        </div>

                        {{-- Fecha compra --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Fecha de compra
                            </label>

                            <input
                                type="date"
                                wire:model="fechaCompra"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >
                        </div>

                        {{-- Factura --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Número de factura *
                            </label>

                            <input
                                type="text"
                                wire:model="numeroFactura"
                                placeholder="Ingresar número de factura"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >

                            <p
                                x-show="fieldError('numeroFactura')"
                                x-cloak
                                x-text="fieldError('numeroFactura')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- Serie --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Número de serie *
                            </label>

                            <input
                                type="text"
                                wire:model="numeroSerie"
                                placeholder="Ingresar número de serie"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >

                            <p
                                x-show="fieldError('numeroSerie')"
                                x-cloak
                                x-text="fieldError('numeroSerie')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- Proveedor --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idProveedor"
                                :options="$toOptions($this->proveedores, 'id_proveedor')"
                                label="Proveedor"
                                placeholder="Buscar proveedor..."
                            />
                        </div>
                    </div>
                </section>

                {{-- ====================================================
                    CAMPOS DINÁMICOS
                ==================================================== --}}
                @if ($idTipoEquipo !== '' && count($this->childFields) > 0)
                    <section
                        id="equipment-child-fields"
                        wire:key="child-fields-{{ $idTipoEquipo }}"
                        class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-xl p-4 sm:p-6"
                    >
                        <h2 class="text-sm font-semibold text-[var(--theme-text-strong)] mb-5">
                            Detalles de
                            {{ collect($this->tiposEquipo)->firstWhere(
                                'id_tipo_equipo',
                                (int) $idTipoEquipo
                            )['nombre'] ?? '' }}
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach ($this->childFields as $field => $type)
                                <div
                                    class="
                                        min-w-0
                                        {{ $type === 'boolean'
                                            ? 'flex items-center gap-2 md:pt-6'
                                            : ''
                                        }}
                                    "
                                >
                                    @if ($type === 'boolean')
                                        <input
                                            type="checkbox"
                                            wire:model="childData.{{ $field }}"
                                            id="cf_{{ $field }}"
                                            class="rounded border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-[var(--theme-primary)] focus:ring-[var(--theme-primary)]"
                                        >

                                        <label for="cf_{{ $field }}" class="text-sm text-[var(--theme-text)]">
                                            {{ $this->fieldLabels[$field] ?? $field }}
                                        </label>

                                    @elseif (str_starts_with($type, 'catalog:'))
                                        @php
                                            $claveCatalogo = substr($type, 8);
                                        @endphp

                                        <x-searchable-select
                                            wire-model="childData.{{ $field }}"
                                            :options="$toOptions(
                                                $this->catalogOptions($claveCatalogo),
                                                'id_valor'
                                            )"
                                            :label="$this->fieldLabels[$field] ?? $field"
                                            placeholder="Buscar..."
                                        />

                                    @else
                                        <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                            {{ $this->fieldLabels[$field] ?? $field }}
                                        </label>

                                        @if ($type === 'date')
                                            <input
                                                type="date"
                                                wire:model="childData.{{ $field }}"
                                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                                            >
                                        @elseif ($type === 'number')
                                            <input
                                                type="number"
                                                wire:model="childData.{{ $field }}"
                                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                                            >
                                        @else
                                            <input
                                                type="text"
                                                wire:model="childData.{{ $field }}"
                                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                                            >
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ====================================================
                    INFORMACIÓN ADICIONAL
                ==================================================== --}}
                <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-xl p-4 sm:p-6">
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)] mb-5">
                        Información adicional
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
                        {{-- Propietario --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Propietario / Responsable
                            </label>

                            <input
                                type="text"
                                wire:model="propietario"
                                placeholder="Ej. María López RRHH"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >

                            <p class="mt-1.5 text-[11px] leading-relaxed text-[var(--theme-text-muted)]">
                                Si lo dejas vacío, el equipo queda como stock disponible.
                            </p>
                        </div>

                        {{-- Estatus --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idCondicionActivo"
                                :options="$toOptions($this->condiciones, 'id_valor')"
                                label="Estatus"
                                placeholder="Nuevo / Usado / Reparado..."
                            />
                        </div>

                        {{-- Garantía --}}
                        <div class="min-w-0">
                            <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                                Vigencia de garantía
                            </label>

                            <input
                                type="date"
                                wire:model="fechaFinGarantia"
                                class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                            >
                        </div>

                        {{-- Área --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idArea"
                                :options="$toOptions($this->areas, 'id_area')"
                                label="Área *"
                                placeholder="Buscar área..."
                            />

                            <p
                                x-show="fieldError('idArea')"
                                x-cloak
                                x-text="fieldError('idArea')"
                                class="mt-1 text-xs text-[var(--theme-danger)]"
                            ></p>
                        </div>

                        {{-- Departamento --}}
                        <div class="min-w-0">
                            <x-searchable-select
                                wire-model="idDepartamento"
                                :options="$toOptions($this->departamentos, 'id_departamento')"
                                label="Departamento"
                                :placeholder="
                                    $idArea === ''
                                        ? 'Elige un área primero'
                                        : 'Buscar departamento...'
                                "
                                :disabled="$idArea === ''"
                            />
                        </div>
                    </div>

                    {{-- Comentarios --}}
                    <div>
                        <label class="block text-xs text-[var(--theme-text-muted)] mb-1.5">
                            Comentarios
                        </label>

                        <textarea
                            wire:model="comentarios"
                            rows="4"
                            placeholder="Escribe algún comentario adicional..."
                            class="w-full min-w-0 text-sm text-[var(--theme-text)] bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-3 py-2.5 placeholder:text-[var(--theme-text-muted)] resize-y focus:ring-1 focus:ring-[var(--theme-primary)] focus:border-[var(--theme-primary)] outline-none transition-colors"
                        ></textarea>
                    </div>
                </section>

                {{-- ====================================================
                    ACCIONES
                ==================================================== --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-1 pb-2">
                    <a
                        href="{{ route('equipos.index') }}"
                        class="w-full sm:w-auto h-11 flex items-center justify-center border border-[var(--theme-border-strong)] rounded-lg px-5 text-sm font-medium text-[var(--theme-text-muted)] bg-[var(--theme-surface)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)] transition-colors"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        :disabled="savingVisual"
                        :aria-busy="savingVisual ? 'true' : 'false'"
                        class="w-full sm:w-auto h-11 flex items-center justify-center gap-2 bg-[var(--theme-primary)] hover:bg-[var(--theme-primary-hover)] disabled:opacity-60 disabled:cursor-not-allowed rounded-lg px-5 text-sm font-medium text-white transition-colors"
                    >
                        {{-- Icono normal --}}
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

                        {{-- Spinner --}}
                        <svg
                            x-show="savingVisual"
                            x-cloak
                            class="w-4 h-4 animate-spin"
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
            Alpine.data('equipmentCreate', () => ({
                successVisible: false,
                successMessage: '',
                successTimer: null,

                errorVisible: false,
                errorMessage: '',
                errorTimer: null,

                savingVisual: false,
                spinnerTimer: null,

                validationErrors: {},

                showSuccess(message) {
                    this.successMessage =
                        message || 'Equipo añadido correctamente.';

                    this.successVisible = true;

                    if (this.successTimer) {
                        clearTimeout(this.successTimer);
                    }

                    this.successTimer = setTimeout(() => {
                        this.successVisible = false;
                    }, 3200);
                },

                showError(message) {
                    this.errorMessage =
                        message || 'No fue posible guardar el equipo.';

                    this.errorVisible = true;

                    if (this.errorTimer) {
                        clearTimeout(this.errorTimer);
                    }

                    this.errorTimer = setTimeout(() => {
                        this.errorVisible = false;
                    }, 5000);
                },

                firstError(error) {
                    const errors =
                        error && error.errors
                            ? error.errors
                            : null;

                    if (
                        !errors ||
                        typeof errors !== 'object'
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

                fieldError(field) {
                    if (
                        !this.validationErrors ||
                        !this.validationErrors[field]
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

                clone(value) {
                    return JSON.parse(
                        JSON.stringify(
                            value ?? {}
                        )
                    );
                },

                /*
                |--------------------------------------------------------------------------
                | Snapshot
                |--------------------------------------------------------------------------
                */
                captureSnapshot() {
                    return {
                        nombreEquipo:
                            String(
                                $wire.nombreEquipo ?? ''
                            ),

                        host:
                            String(
                                $wire.host ?? ''
                            ),

                        idEstadoActivo:
                            String(
                                $wire.idEstadoActivo ?? ''
                            ),

                        idTipoEquipo:
                            String(
                                $wire.idTipoEquipo ?? ''
                            ),

                        idModelo:
                            String(
                                $wire.idModelo ?? ''
                            ),

                        fechaCompra:
                            String(
                                $wire.fechaCompra ?? ''
                            ),

                        idMarca:
                            String(
                                $wire.idMarca ?? ''
                            ),

                        direccionMac:
                            String(
                                $wire.direccionMac ?? ''
                            ),

                        numeroFactura:
                            String(
                                $wire.numeroFactura ?? ''
                            ),

                        numeroSerie:
                            String(
                                $wire.numeroSerie ?? ''
                            ),

                        idProveedor:
                            String(
                                $wire.idProveedor ?? ''
                            ),

                        propietario:
                            String(
                                $wire.propietario ?? ''
                            ),

                        idCondicionActivo:
                            String(
                                $wire.idCondicionActivo ?? ''
                            ),

                        idArea:
                            String(
                                $wire.idArea ?? ''
                            ),

                        idDepartamento:
                            String(
                                $wire.idDepartamento ?? ''
                            ),

                        fechaFinGarantia:
                            String(
                                $wire.fechaFinGarantia ?? ''
                            ),

                        comentarios:
                            String(
                                $wire.comentarios ?? ''
                            ),

                        childData:
                            this.clone(
                                $wire.childData
                            ),
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
                            payload.nombreEquipo ?? ''
                        ).trim() === ''
                    ) {
                        errors.nombreEquipo = [
                            'El nombre del equipo es obligatorio.'
                        ];
                    }

                    if (
                        String(
                            payload.idEstadoActivo ?? ''
                        ) === ''
                    ) {
                        errors.idEstadoActivo = [
                            'Selecciona un estado.'
                        ];
                    }

                    if (
                        String(
                            payload.idTipoEquipo ?? ''
                        ) === ''
                    ) {
                        errors.idTipoEquipo = [
                            'Selecciona un tipo de equipo.'
                        ];
                    }

                    if (
                        String(
                            payload.idMarca ?? ''
                        ) === ''
                    ) {
                        errors.idMarca = [
                            'Selecciona una marca.'
                        ];
                    }

                    if (
                        String(
                            payload.numeroFactura ?? ''
                        ).trim() === ''
                    ) {
                        errors.numeroFactura = [
                            'El número de factura es obligatorio.'
                        ];
                    }

                    if (
                        String(
                            payload.numeroSerie ?? ''
                        ).trim() === ''
                    ) {
                        errors.numeroSerie = [
                            'El número de serie es obligatorio.'
                        ];
                    }

                    if (
                        String(
                            payload.idArea ?? ''
                        ) === ''
                    ) {
                        errors.idArea = [
                            'Selecciona un área.'
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
                | Limpiar formulario inmediatamente
                |--------------------------------------------------------------------------
                */
                clearFormInstantly() {
                    this.validationErrors = {};

                    $wire.$set(
                        'nombreEquipo',
                        '',
                        false
                    );

                    $wire.$set(
                        'host',
                        '',
                        false
                    );

                    $wire.$set(
                        'idEstadoActivo',
                        '',
                        false
                    );

                    $wire.$set(
                        'idTipoEquipo',
                        '',
                        false
                    );

                    $wire.$set(
                        'idModelo',
                        '',
                        false
                    );

                    $wire.$set(
                        'fechaCompra',
                        '',
                        false
                    );

                    $wire.$set(
                        'idMarca',
                        '',
                        false
                    );

                    $wire.$set(
                        'direccionMac',
                        '',
                        false
                    );

                    $wire.$set(
                        'numeroFactura',
                        '',
                        false
                    );

                    $wire.$set(
                        'numeroSerie',
                        '',
                        false
                    );

                    $wire.$set(
                        'idProveedor',
                        '',
                        false
                    );

                    $wire.$set(
                        'propietario',
                        '',
                        false
                    );

                    $wire.$set(
                        'idCondicionActivo',
                        '',
                        false
                    );

                    $wire.$set(
                        'idArea',
                        '',
                        false
                    );

                    $wire.$set(
                        'idDepartamento',
                        '',
                        false
                    );

                    $wire.$set(
                        'fechaFinGarantia',
                        '',
                        false
                    );

                    $wire.$set(
                        'comentarios',
                        '',
                        false
                    );

                    $wire.$set(
                        'childData',
                        {},
                        false
                    );

                    /*
                     * La sección de campos dinámicos fue renderizada
                     * en el servidor con el tipo anterior.
                     *
                     * La ocultamos inmediatamente. Cuando el usuario
                     * seleccione otro tipo, Livewire la reconstruirá.
                     */
                    const childSection =
                        document.getElementById(
                            'equipment-child-fields'
                        );

                    if (childSection) {
                        childSection.style.display =
                            'none';
                    }
                },

                /*
                |--------------------------------------------------------------------------
                | ¿El usuario ya comenzó otro equipo?
                |--------------------------------------------------------------------------
                */
                formIsStillEmpty() {
                    const values = [
                        $wire.nombreEquipo,
                        $wire.host,
                        $wire.idEstadoActivo,
                        $wire.idTipoEquipo,
                        $wire.idModelo,
                        $wire.fechaCompra,
                        $wire.idMarca,
                        $wire.direccionMac,
                        $wire.numeroFactura,
                        $wire.numeroSerie,
                        $wire.idProveedor,
                        $wire.propietario,
                        $wire.idCondicionActivo,
                        $wire.idArea,
                        $wire.idDepartamento,
                        $wire.fechaFinGarantia,
                        $wire.comentarios,
                    ];

                    const hasValue =
                        values.some(
                            value =>
                                value !== null &&
                                value !== undefined &&
                                String(value).trim() !== ''
                        );

                    if (hasValue) {
                        return false;
                    }

                    const childData =
                        this.clone(
                            $wire.childData
                        );

                    return (
                        Object.keys(
                            childData
                        ).length === 0
                    );
                },

                /*
                |--------------------------------------------------------------------------
                | Restaurar datos si el servidor rechaza el equipo
                |--------------------------------------------------------------------------
                */
                restoreSnapshot(payload) {
                    $wire.$set(
                        'nombreEquipo',
                        payload.nombreEquipo,
                        false
                    );

                    $wire.$set(
                        'host',
                        payload.host,
                        false
                    );

                    $wire.$set(
                        'idEstadoActivo',
                        payload.idEstadoActivo,
                        false
                    );

                    $wire.$set(
                        'idTipoEquipo',
                        payload.idTipoEquipo,
                        false
                    );

                    $wire.$set(
                        'idModelo',
                        payload.idModelo,
                        false
                    );

                    $wire.$set(
                        'fechaCompra',
                        payload.fechaCompra,
                        false
                    );

                    $wire.$set(
                        'idMarca',
                        payload.idMarca,
                        false
                    );

                    $wire.$set(
                        'direccionMac',
                        payload.direccionMac,
                        false
                    );

                    $wire.$set(
                        'numeroFactura',
                        payload.numeroFactura,
                        false
                    );

                    $wire.$set(
                        'numeroSerie',
                        payload.numeroSerie,
                        false
                    );

                    $wire.$set(
                        'idProveedor',
                        payload.idProveedor,
                        false
                    );

                    $wire.$set(
                        'propietario',
                        payload.propietario,
                        false
                    );

                    $wire.$set(
                        'idCondicionActivo',
                        payload.idCondicionActivo,
                        false
                    );

                    $wire.$set(
                        'idArea',
                        payload.idArea,
                        false
                    );

                    $wire.$set(
                        'idDepartamento',
                        payload.idDepartamento,
                        false
                    );

                    $wire.$set(
                        'fechaFinGarantia',
                        payload.fechaFinGarantia,
                        false
                    );

                    $wire.$set(
                        'comentarios',
                        payload.comentarios,
                        false
                    );

                    $wire.$set(
                        'childData',
                        this.clone(
                            payload.childData
                        ),
                        false
                    );

                    const childSection =
                        document.getElementById(
                            'equipment-child-fields'
                        );

                    if (childSection) {
                        childSection.style.display =
                            '';
                    }
                },

                /*
                |--------------------------------------------------------------------------
                | Guardado optimista
                |--------------------------------------------------------------------------
                */
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

                    this.errorVisible = false;

                    /*
                     * El spinner aparece inmediatamente.
                     */
                    this.pulseSpinner();

                    let request;

                    try {
                        /*
                         * IMPORTANTE:
                         * iniciamos save(), pero todavía NO hacemos await.
                         */
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
                     * Limpieza instantánea.
                     *
                     * Esto sucede sin esperar a PostgreSQL.
                     */
                    this.clearFormInstantly();

                    /*
                     * Ahora sí esperamos la respuesta, pero el formulario
                     * ya está libre para el usuario.
                     */
                    try {
                        const result =
                            await request;

                        if (
                            !result ||
                            result.ok !== true
                        ) {
                            throw new Error(
                                'Respuesta de guardado no válida.'
                            );
                        }

                        /*
                         * Actualizar el siguiente código sin refrescar
                         * todo el componente.
                         */
                        const codePreview =
                            document.getElementById(
                                'equipment-code-preview'
                            );

                        if (
                            codePreview &&
                            result.nextCode
                        ) {
                            codePreview.textContent =
                                result.nextCode;
                        }

                        this.showSuccess(
                            result.message ||
                            'Equipo añadido correctamente.'
                        );

                    } catch (error) {
                        const serverErrors =
                            error &&
                            error.errors
                                ? error.errors
                                : {};

                        const validationMessage =
                            this.firstError(
                                error
                            );

                        /*
                         * Si todavía está vacío, restauramos.
                         *
                         * Si el usuario ya comenzó otro equipo,
                         * NO sobrescribimos su nuevo trabajo.
                         */
                        if (
                            this.formIsStillEmpty()
                        ) {
                            this.restoreSnapshot(
                                payload
                            );

                            this.validationErrors =
                                serverErrors;

                        } else {
                            this.validationErrors = {};
                        }

                        this.showError(
                            validationMessage ||
                            'No fue posible guardar el equipo. Inténtalo nuevamente.'
                        );
                    }
                },
            }));
        </script>
    @endscript
</div>