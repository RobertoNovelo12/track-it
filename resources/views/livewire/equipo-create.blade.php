<div
    x-data="{
        successVisible: false,
        successMessage: '',
        successTimer: null,

        showSuccess(message) {
            this.successMessage = message;
            this.successVisible = true;

            if (this.successTimer) {
                clearTimeout(this.successTimer);
            }

            this.successTimer = setTimeout(() => {
                this.successVisible = false;
            }, 2800);
        }
    }"
    @equipo-guardado.window="
        showSuccess(
            $event.detail.message ?? 'Equipo añadido correctamente.'
        )
    "
>

    @php
        // Convierte un arreglo de filas ['id_x' => .., 'nombre' => ..]
        // al formato ['value' => .., 'label' => ..] que espera
        // x-searchable-select.
        $toOptions = fn ($items, string $valueKey, string $labelKey = 'nombre') =>
            collect($items)
                ->map(fn ($i) => [
                    'value' => $i[$valueKey],
                    'label' => $i[$labelKey]
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

        {{-- Icono --}}
        <div
            class="
                shrink-0
                w-8
                h-8
                rounded-full
                bg-emerald-500/15
                flex
                items-center
                justify-center
                text-emerald-500
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


        {{-- Texto --}}
        <div class="min-w-0 flex-1">

            <p class="text-sm font-semibold text-emerald-500">
                Registro completado
            </p>

            <p
                class="mt-0.5 text-xs text-[var(--theme-text)]"
                x-text="successMessage"
            ></p>

        </div>


        {{-- Cerrar --}}
        <button
            type="button"
            @click="successVisible = false"
            class="
                shrink-0
                text-emerald-500/60
                hover:text-emerald-500
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
        FORMULARIO
    ============================================================ --}}
    <form
        wire:submit.prevent="save"
        wire:key="equipo-form-{{ $formKey }}"
        class="relative"
    >

        {{-- ========================================================
            FIELDSET

            Se deshabilita automáticamente mientras save() trabaja.
        ======================================================== --}}
        <fieldset
            wire:loading.attr="disabled"
            wire:target="save"
            wire:loading.class="pointer-events-none opacity-60"
            class="
                space-y-4
                sm:space-y-6
                transition-opacity
                duration-150
            "
        >

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

                {{-- Encabezado --}}
                <div
                    class="
                        flex
                        flex-col
                        gap-1.5

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


                    {{-- Código próximo de inventario --}}
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
                        >
                            {{ $this->codigoInventarioPreview }}
                        </span>

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

                    {{-- Nombre del equipo --}}
                    <div class="min-w-0">

                        <label
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
                            type="text"
                            wire:model="nombreEquipo"
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

                        @error('nombreEquipo')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Host --}}
                    <div class="min-w-0">

                        <label
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
                            type="text"
                            wire:model="host"
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


                    {{-- Estado --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idEstadoActivo"
                            :options="$toOptions(
                                $this->estados,
                                'id_estado_equipo'
                            )"
                            label="Estado *"
                            placeholder="Buscar estado..."
                        />

                        @error('idEstadoActivo')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Tipo de equipo --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idTipoEquipo"
                            :options="$toOptions(
                                $this->tiposEquipo,
                                'id_tipo_equipo'
                            )"
                            label="Tipo de equipo *"
                            placeholder="Buscar tipo de equipo..."
                        />

                        @error('idTipoEquipo')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Marca --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idMarca"
                            :options="$toOptions(
                                $this->marcas,
                                'id_marca'
                            )"
                            label="Marca *"
                            placeholder="Buscar marca..."
                        />

                        @error('idMarca')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Modelo --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idModelo"
                            :options="$toOptions(
                                $this->modelos,
                                'id_modelo'
                            )"
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


                    {{-- Dirección MAC --}}
                    <div class="min-w-0">

                        <label
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
                            type="text"
                            wire:model="direccionMac"
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


                    {{-- Fecha de compra --}}
                    <div class="min-w-0">

                        <label
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
                            type="date"
                            wire:model="fechaCompra"
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


                    {{-- Número de factura --}}
                    <div class="min-w-0">

                        <label
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
                            type="text"
                            wire:model="numeroFactura"
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

                        @error('numeroFactura')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Número de serie --}}
                    <div class="min-w-0">

                        <label
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
                            type="text"
                            wire:model="numeroSerie"
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

                        @error('numeroSerie')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Proveedor --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idProveedor"
                            :options="$toOptions(
                                $this->proveedores,
                                'id_proveedor'
                            )"
                            label="Proveedor"
                            placeholder="Buscar proveedor..."
                        />

                    </div>

                </div>

            </section>


            {{-- ====================================================
                CAMPOS DINÁMICOS SEGÚN TIPO DE EQUIPO
            ==================================================== --}}
            @if (
                $idTipoEquipo !== '' &&
                count($this->childFields) > 0
            )

                <section
                    wire:key="child-fields-{{ $idTipoEquipo }}"
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
                        {{ collect($this->tiposEquipo)->firstWhere(
                            'id_tipo_equipo',
                            (int) $idTipoEquipo
                        )['nombre'] ?? '' }}
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
                                        class="
                                            rounded
                                            border-[var(--theme-border-strong)]
                                            bg-[var(--theme-surface)]
                                            text-[var(--theme-primary)]
                                            focus:ring-[var(--theme-primary)]
                                        "
                                    >

                                    <label
                                        for="cf_{{ $field }}"
                                        class="text-sm text-[var(--theme-text)]"
                                    >
                                        {{ $this->fieldLabels[$field] ?? $field }}
                                    </label>


                                @elseif (str_starts_with($type, 'catalog:'))

                                    @php
                                        $claveCatalogo = substr($type, 8);
                                    @endphp

                                    <x-searchable-select
                                        wire-model="childData.{{ $field }}"
                                        :options="$toOptions(
                                            $this->catalogOptions(
                                                $claveCatalogo
                                            ),
                                            'id_valor'
                                        )"
                                        :label="
                                            $this->fieldLabels[$field]
                                            ?? $field
                                        "
                                        placeholder="Buscar..."
                                    />


                                @else

                                    <label
                                        class="
                                            block
                                            text-xs
                                            text-[var(--theme-text-muted)]
                                            mb-1.5
                                        "
                                    >
                                        {{ $this->fieldLabels[$field] ?? $field }}
                                    </label>


                                    @if ($type === 'date')

                                        <input
                                            type="date"
                                            wire:model="childData.{{ $field }}"
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


                                    @elseif ($type === 'number')

                                        <input
                                            type="number"
                                            wire:model="childData.{{ $field }}"
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


                                    @else

                                        <input
                                            type="text"
                                            wire:model="childData.{{ $field }}"
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

                    {{-- Propietario --}}
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
                            wire:model="propietario"
                            placeholder="Ej. María López RRHH"
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
                            class="
                                mt-1.5
                                text-[11px]
                                leading-relaxed
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Si lo dejas vacío, el equipo queda como stock disponible.
                        </p>

                    </div>


                    {{-- Estatus --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idCondicionActivo"
                            :options="$toOptions(
                                $this->condiciones,
                                'id_valor'
                            )"
                            label="Estatus"
                            placeholder="Nuevo / Usado / Reparado..."
                        />

                    </div>


                    {{-- Garantía --}}
                    <div class="min-w-0">

                        <label
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
                            type="date"
                            wire:model="fechaFinGarantia"
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


                    {{-- Área --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idArea"
                            :options="$toOptions(
                                $this->areas,
                                'id_area'
                            )"
                            label="Área *"
                            placeholder="Buscar área..."
                        />

                        @error('idArea')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Departamento --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            wire-model="idDepartamento"
                            :options="$toOptions(
                                $this->departamentos,
                                'id_departamento'
                            )"
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

                    <label
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
                        wire:model="comentarios"
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

                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]

                        bg-[var(--theme-surface)]
                        hover:bg-[var(--theme-surface-soft)]
                        hover:text-[var(--theme-text)]

                        transition-colors
                    "
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
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

                    {{-- Icono normal --}}
                    <svg
                        wire:loading.remove
                        wire:target="save"
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
                        wire:loading
                        wire:target="save"
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


                    <span wire:loading.remove wire:target="save">
                        Añadir equipo
                    </span>

                    <span wire:loading wire:target="save">
                        Guardando...
                    </span>

                </button>

            </div>

        </fieldset>


        {{-- ========================================================
            OVERLAY DE GUARDADO
        ======================================================== --}}
        <div
            wire:loading.flex
            wire:target="save"
            class="
                fixed
                inset-0
                z-[140]

                items-center
                justify-center

                bg-[var(--theme-overlay)]
                backdrop-blur-[1px]
            "
        >

            <div
                class="
                    flex
                    items-center
                    gap-3

                    bg-[var(--theme-surface)]
                    border
                    border-[var(--theme-border)]
                    rounded-xl
                    shadow-lg

                    px-5
                    py-4
                "
            >

                <svg
                    class="
                        w-5
                        h-5
                        animate-spin
                        text-[var(--theme-primary)]
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
                        opacity="0.2"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>


                <div>

                    <p
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Guardando equipo...
                    </p>

                    <p
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                            mt-0.5
                        "
                    >
                        Espera un momento
                    </p>

                </div>

            </div>

        </div>

    </form>

</div>
