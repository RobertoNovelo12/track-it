
@props([
    'wireModel' => null,
    'xModel' => null,
    'options' => [],
    'xOptions' => null,
    'label' => '',
    'placeholder' => 'Buscar...',
    'xPlaceholder' => null,
    'disabled' => false,
    'xDisabled' => null,
    'showClear' => true,
    'clearLabel' => 'Todos',
    'clearValue' => '',
    'compact' => false,
    'reportAutoSearch' => false,

    /*
     * Valor al limpiar o elegir la opción vacía:
     * true  -> null (comportamiento original)
     * false -> '' (igual que un <select> nativo con <option value="">)
     */
    'emptyAsNull' => true,

    /*
     * Para formularios HTML normales (sin Livewire): agrega un
     * <input type="hidden" name="..."> con el valor seleccionado.
     */
    'name' => null,

    /*
     * Permite escribir un valor que no está en la lista (p. ej. un
     * colaborador sin cuenta). Se guarda como customPrefix + texto
     * («texto:Juan Pérez»); las opciones normales conservan su valor.
     * Las opciones pueden traer 'search' con texto extra para buscar
     * (número de colaborador, usuario, correo...).
     */
    'allowCustom' => false,
    'customPrefix' => 'texto:',
    'customHint' => 'sin cuenta',

    /*
     * Cuántas opciones se pintan al abrir; al llegar al final de la lista
     * se agregan las siguientes. La búsqueda siempre revisa todas.
     */
    'pageSize' => 10,
    'loadingText' => 'Cargando más…',
])

@php
    /*
    |--------------------------------------------------------------------------
    | Modo del componente
    |--------------------------------------------------------------------------
    |
    | wire-model: funcionamiento con Livewire.
    | x-model: funcionamiento local con Alpine.
    |
    */

    $localMode = filled($xModel);

    $optionsJson = collect($options)
        ->map(fn ($option) => [
            ...$option,
            'value' => (string) ($option['value'] ?? ''),
            'label' => (string) ($option['label'] ?? ''),
        ])
        ->values()
        ->all();
@endphp

<div
    x-data="{
        open: false,
        query: '',

        /*
        |--------------------------------------------------------------------------
        | Valor seleccionado
        |--------------------------------------------------------------------------
        */

        selected:
            @if ($localMode)
                ''
            @elseif (filled($wireModel))
                @if ($reportAutoSearch)
                    $wire.entangle(@js($wireModel))
                @else
                    $wire.entangle(@js($wireModel)).live
                @endif
            @else
                ''
            @endif
        ,

        /*
        |--------------------------------------------------------------------------
        | Datos
        |--------------------------------------------------------------------------
        */

        options: @js($optionsJson),

        disabled: @js((bool) $disabled),

        currentPlaceholder: @js((string) $placeholder),

        showClear: @js((bool) $showClear),

        clearLabel: @js((string) $clearLabel),

        clearValue: @js($clearValue),
        allowCustom: @js((bool) $allowCustom),
        customPrefix: @js((string) $customPrefix),

        porPagina: @js(max(1, (int) $pageSize)),
        limite: @js(max(1, (int) $pageSize)),
        cargandoMas: false,

        /*
        |--------------------------------------------------------------------------
        | Inicialización
        |--------------------------------------------------------------------------
        */

        init() {
            this.$nextTick(() => {
                this.syncSelectedLabel();
            });

            this.$watch('selected', () => {
                if (!this.open) {
                    this.syncSelectedLabel();
                }
            });

            // Nueva búsqueda: volver al primer lote y al inicio de la lista.
            this.$watch('query', () => {
                this.limite = this.porPagina;

                if (this.$refs.lista) {
                    this.$refs.lista.scrollTop = 0;
                }
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Carga por lotes
        |--------------------------------------------------------------------------
        */

        get visibles() {
            return this.filtered.slice(0, this.limite);
        },

        get hayMas() {
            return this.filtered.length > this.limite;
        },

        revisarScroll(evento) {
            const nodo = evento.target;

            if (nodo.scrollTop + nodo.clientHeight >= nodo.scrollHeight - 40) {
                this.cargarMas();
            }
        },

        cargarMas() {
            if (!this.hayMas || this.cargandoMas) {
                return;
            }

            this.cargandoMas = true;

            requestAnimationFrame(() => {
                this.limite += this.porPagina;
                this.cargandoMas = false;
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Opciones dinámicas
        |--------------------------------------------------------------------------
        */

        normalizeOptions(value) {
            if (!Array.isArray(value)) {
                return [];
            }

            return value.map(option => ({
                ...option,
                value: String(option?.value ?? ''),
                label: String(option?.label ?? ''),
            }));
        },

        syncOptions(value) {
            this.options = this.normalizeOptions(value);

            this.$nextTick(() => {
                this.syncSelectedLabel();
            });
        },

        syncDisabled(value) {
            this.disabled = Boolean(value);

            if (this.disabled) {
                this.open = false;
                this.syncSelectedLabel();
            }
        },

        syncPlaceholder(value) {
            this.currentPlaceholder = String(value ?? '');
        },

        /*
        |--------------------------------------------------------------------------
        | Opciones filtradas
        |--------------------------------------------------------------------------
        */

        get filtered() {
            const query = String(this.query ?? '')
                .trim()
                .toLowerCase();

            if (query === '') {
                return this.options;
            }

            return this.options.filter(option =>
                (String(option.label ?? '') + ' ' + String(option.search ?? ''))
                    .toLowerCase()
                    .includes(query)
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Texto libre (allow-custom)
        |--------------------------------------------------------------------------
        */
        isCustomValue(value) {
            return this.allowCustom
                && typeof value === 'string'
                && value.startsWith(this.customPrefix);
        },

        get exactOption() {
            const query = String(this.query ?? '').trim().toLowerCase();

            if (query === '') {
                return null;
            }

            return this.options.find(option =>
                String(option.label ?? '').trim().toLowerCase() === query
                || String(option.nombre ?? '').trim().toLowerCase() === query
            ) ?? null;
        },

        get showCustom() {
            return this.allowCustom
                && String(this.query ?? '').trim() !== ''
                && ! this.exactOption;
        },

        pickCustom() {
            const texto = String(this.query ?? '').trim().replace(/\s+/g, ' ');

            if (texto === '') {
                return;
            }

            this.pick(this.customPrefix + texto, texto);
        },

        /*
         * Enter o salir del campo con texto escrito: si coincide con una
         * opción se elige esa; si no, se usa el texto escrito.
         */
        commitTyped() {
            if (! this.allowCustom) {
                return;
            }

            const exacta = this.exactOption;

            if (exacta) {
                this.pick(exacta.value, exacta.label);

                return;
            }

            this.pickCustom();
        },

        /*
        |--------------------------------------------------------------------------
        | Obtener opción seleccionada
        |--------------------------------------------------------------------------
        */

        selectedOption() {
            if (
                this.selected === null ||
                this.selected === undefined ||
                this.selected === ''
            ) {
                return null;
            }

            const selectedValue = String(this.selected);

            return this.options.find(
                option => String(option.value) === selectedValue
            ) ?? null;
        },

        /*
        |--------------------------------------------------------------------------
        | Texto visible
        |--------------------------------------------------------------------------
        */

        syncSelectedLabel() {
            if (this.isCustomValue(this.selected)) {
                this.query = String(this.selected).slice(this.customPrefix.length);

                return;
            }

            const option = this.selectedOption();

            if (option) {
                this.query = option.label;
                return;
            }

            if (
                this.showClear &&
                this.clearValue !== '' &&
                this.clearValue !== null &&
                this.selected !== null &&
                this.selected !== undefined &&
                String(this.selected) === String(this.clearValue)
            ) {
                this.query = this.clearLabel;
                return;
            }

            this.query = '';
        },

        /*
        |--------------------------------------------------------------------------
        | Abrir
        |--------------------------------------------------------------------------
        */

        openAndFocus() {
            if (this.disabled) {
                return;
            }

            if (!this.open) {
                this.query = '';
            }

            this.open = true;

            this.$nextTick(() => {
                this.$refs.search?.focus();
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Cerrar
        |--------------------------------------------------------------------------
        */

        closeDropdown(confirmar = true) {
            if (!this.open) {
                return;
            }

            const escrito = String(this.query ?? '').trim();

            if (
                confirmar
                && this.allowCustom
                && escrito !== ''
                && ! (this.selectedOption() && this.selectedOption().label === this.query)
                && ! (this.isCustomValue(this.selected) && String(this.selected).slice(this.customPrefix.length) === escrito)
            ) {
                this.commitTyped();

                return;
            }

            this.open = false;
            this.syncSelectedLabel();
        },

        /*
        |--------------------------------------------------------------------------
        | Flecha
        |--------------------------------------------------------------------------
        */

        toggleDropdown() {
            if (this.disabled) {
                return;
            }

            if (this.open) {
                this.closeDropdown();
                return;
            }

            this.openAndFocus();
        },

        /*
        |--------------------------------------------------------------------------
        | Notificar cambio para reportes
        |--------------------------------------------------------------------------
        */

        notificarCambio() {
            @if ($reportAutoSearch && filled($wireModel))
                this.$dispatch('reporte-filtro-cambiado', {
                    campo: @js($wireModel)
                });
            @endif
        },

        /*
        |--------------------------------------------------------------------------
        | Seleccionar
        |--------------------------------------------------------------------------
        */

        pick(value, label) {
            if (this.disabled) {
                return;
            }

            const nuevoValor = value === ''
                ? (@js($reportAutoSearch || ! $emptyAsNull) ? '' : null)
                : String(value);

            const valorAnterior = this.selected;

            this.selected = nuevoValor;
            this.query = String(label ?? '');
            this.open = false;

            if (
                String(valorAnterior ?? '') !==
                String(nuevoValor ?? '')
            ) {
                this.notificarCambio();
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Limpiar
        |--------------------------------------------------------------------------
        */

        clear() {
            if (this.disabled) {
                return;
            }

            const nuevoValor =
                this.clearValue === '' ||
                this.clearValue === null
                    ? (@js($reportAutoSearch || ! $emptyAsNull) ? '' : null)
                    : String(this.clearValue);

            const valorAnterior = this.selected;

            this.selected = nuevoValor;

            this.query =
                this.clearValue === '' ||
                this.clearValue === null
                    ? ''
                    : this.clearLabel;

            this.open = false;

            if (
                String(valorAnterior ?? '') !==
                String(nuevoValor ?? '')
            ) {
                this.notificarCambio();
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Escritura
        |--------------------------------------------------------------------------
        */

        handleInput() {
            if (this.disabled) {
                return;
            }

            this.open = true;
        }
    }"

    @if ($localMode)
        x-modelable="selected"
        x-model="{{ $xModel }}"
    @endif

    {{--
        Un solo x-effect: el navegador descarta los atributos repetidos, así
        que con varios x-effect solo funcionaba el primero (x-options) y
        x-disabled / x-placeholder nunca se aplicaban.
    --}}
    @if (filled($xOptions) || filled($xDisabled) || filled($xPlaceholder))
        x-effect="
            @if (filled($xOptions)) syncOptions({{ $xOptions }}); @endif
            @if (filled($xDisabled)) syncDisabled({{ $xDisabled }}); @endif
            @if (filled($xPlaceholder)) syncPlaceholder({{ $xPlaceholder }}); @endif
        "
    @endif

    @click.outside="closeDropdown()"
    @keydown.escape.window="closeDropdown(false)"

    class="relative"
>
    {{-- ============================================================
        LABEL
    ============================================================ --}}

    @if ($label)
        <label
            class="
                block
                mb-1.5
                text-xs
                text-[var(--theme-text-muted)]
            "
        >
            {{ $label }}
        </label>
    @endif

    @if (filled($name))
        <input type="hidden" name="{{ $name }}" :value="selected ?? ''">
    @endif

    {{-- ============================================================
        CONTROL
    ============================================================ --}}

    <div
        @click="
            if (!disabled && !open) {
                openAndFocus();
            }
        "

        {{-- Altura fija de 40 px (igual que los campos de texto; ver ui-styles). --}}
        @class([
            'relative flex items-center w-full border border-[var(--theme-border-strong)] rounded-md',
            'bg-[var(--theme-surface)] focus-within:ring-1 focus-within:ring-[var(--theme-primary)] focus-within:border-[var(--theme-primary)]',
            'h-10' => ! $compact,
        ])

        :class="
            disabled
                ? 'cursor-not-allowed'
                : ''
        "
    >
        {{-- INPUT VISIBLE --}}

        <input
            type="text"

            x-ref="search"
            x-model="query"

            @focus="
                if (!disabled && !open) {
                    openAndFocus();
                }
            "

            @input="handleInput()"
            @if ($allowCustom)
                @keydown.enter.prevent="commitTyped()"
            @endif

            :disabled="disabled"
            :placeholder="currentPlaceholder"

            autocomplete="off"

            @class([
                'w-full',
                'min-w-0',

                'text-[var(--theme-text)]',

                'border-0',
                'bg-transparent',

                'pl-3',
                'pr-10',

                'placeholder:text-[var(--theme-text-muted)]',

                'focus:ring-0',

                'disabled:cursor-not-allowed',

                'text-xs py-2' => $compact,
                'text-sm h-full py-0' => ! $compact,
            ])
        >

        {{-- FLECHA --}}

        <button
            type="button"

            @click.stop="toggleDropdown()"

            :disabled="disabled"
            :aria-expanded="open ? 'true' : 'false'"

            aria-label="Abrir o cerrar opciones"

            class="
                absolute
                right-0
                top-0
                bottom-0

                w-9

                flex
                items-center
                justify-center

                text-[var(--theme-text-muted)]

                hover:text-[var(--theme-text)]

                disabled:cursor-not-allowed

                transition-colors
            "
        >
            <svg
                class="
                    w-4
                    h-4

                    pointer-events-none

                    transition-transform
                    duration-150
                "

                :class="
                    open
                        ? 'rotate-180'
                        : ''
                "

                viewBox="0 0 24 24"

                fill="none"
                stroke="currentColor"
                stroke-width="1.5"

                aria-hidden="true"
            ><use href="{{ \App\Support\Icono::url('i-abe9c4c') }}"></use></svg>
        </button>
    </div>

    {{-- ============================================================
        LISTADO
    ============================================================ --}}

    <div
        x-show="open && !disabled"
        x-cloak
        x-ref="lista"
        @scroll.passive="revisarScroll($event)"

        class="
            absolute
            z-30

            mt-1

            w-full
            min-w-full

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border-strong)]

            rounded-md

            theme-shadow-xl

            max-h-[184px]

            overflow-y-auto
        "
    >
        {{-- ========================================================
            LIMPIAR / VALOR GENERAL
        ======================================================== --}}

        @if ($showClear)
            <div
                @click="clear()"

                @class([
                    'text-[var(--theme-text-muted)]',

                    'hover:bg-[var(--theme-primary-soft-subtle)]',

                    'cursor-pointer',

                    'px-3 py-2 text-xs' => $compact,
                    'px-3 py-2 text-sm' => ! $compact,
                ])
            >
                {{ $clearLabel }}
            </div>
        @endif

        {{-- Texto libre: usar lo escrito (allow-custom) --}}
        @if ($allowCustom)
            <div
                x-show="showCustom"
                @click="pickCustom()"
                @class([
                    'flex items-center justify-between gap-2 border-b border-[var(--theme-border)]',
                    'text-[var(--theme-primary)]',
                    'hover:bg-[var(--theme-primary-soft-subtle)]',
                    'cursor-pointer',
                    'px-3 py-2 text-xs' => $compact,
                    'px-3 py-2 text-sm' => ! $compact,
                ])
            >
                <span class="min-w-0 truncate">
                    Usar «<span x-text="String(query ?? '').trim()"></span>»
                </span>

                <span class="shrink-0 rounded-md bg-[var(--theme-warning-soft)] px-2 py-0.5 text-[10px] font-medium text-[var(--theme-warning)]">
                    {{ $customHint }}
                </span>
            </div>
        @endif

        {{-- ========================================================
            OPCIONES
        ======================================================== --}}

        <template
            x-for="option in visibles"
            :key="option.value"
        >
            <div
                @click="
                    pick(
                        option.value,
                        option.label
                    )
                "

                x-text="option.label"

                @class([
                    'text-[var(--theme-text)]',

                    'hover:bg-[var(--theme-primary-soft-subtle)]',

                    'cursor-pointer',

                    'px-3 py-2 text-xs' => $compact,
                    'px-3 py-2 text-sm' => ! $compact,
                ])
            ></div>
        </template>

        {{-- Quedan más opciones: se agregan al llegar aquí. --}}
        <div
            x-show="hayMas"
            @class([
                'flex items-center justify-center gap-2 text-[var(--theme-text-muted)]',
                'px-3 py-2 text-xs' => $compact,
                'px-3 py-2.5 text-xs' => ! $compact,
            ])
            role="status"
        >
            <span class="inline-block h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-[var(--theme-border-strong)] border-t-[var(--theme-primary)]" aria-hidden="true"></span>
            <span>{{ $loadingText }}</span>
        </div>

        {{-- ========================================================
            SIN RESULTADOS
        ======================================================== --}}

        <div
            x-show="filtered.length === 0 && ! showCustom"

            @class([
                'text-[var(--theme-text-muted)]',

                'px-3 py-2 text-xs' => $compact,
                'px-3 py-2 text-sm' => ! $compact,
            ])
        >
            Sin resultados
        </div>
    </div>
</div>
