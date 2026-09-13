<div>

    @php
        // Convierte un arreglo de filas ['id_x' => .., 'nombre' => ..] al
        // formato ['value' => .., 'label' => ..] que espera x-searchable-select.
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
        MENSAJE DE ÉXITO
    ============================================================ --}}
    @if (session('status'))

        <div
            class="
                mb-4
                text-sm
                text-emerald-700
                bg-emerald-50
                border border-emerald-200
                rounded-lg
                px-4 py-3
            "
        >
            {{ session('status') }}
        </div>

    @endif


    <form
        wire:submit.prevent="save"
        class="space-y-4 sm:space-y-6"
    >

        {{-- ============================================================
            INFORMACIÓN GENERAL
        ============================================================ --}}
        <section
            class="
                bg-white
                border border-[#50514F]/10
                rounded-xl
                p-4
                sm:p-6
            "
        >

            <h2
                class="
                    text-sm
                    font-semibold
                    text-[#50514F]
                    mb-5
                "
            >
                Información general
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

                {{-- Nombre del equipo --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            text-xs
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            placeholder:text-[#50514F]/40
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
                    >

                    @error('nombreEquipo')
                        <p class="mt-1 text-xs text-red-600">
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
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            placeholder:text-[#50514F]/40
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
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

                    @error('idEstadoActivo')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Tipo de equipo --}}
                <div class="min-w-0">

                    <x-searchable-select
                        wire-model="idTipoEquipo"
                        :options="$toOptions($this->tiposEquipo, 'id_tipo_equipo')"
                        label="Tipo de equipo *"
                        placeholder="Buscar tipo de equipo..."
                    />

                    @error('idTipoEquipo')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Marca --}}
                <div class="min-w-0">

                    <x-searchable-select
                        wire-model="idMarca"
                        :options="$toOptions($this->marcas, 'id_marca')"
                        label="Marca *"
                        placeholder="Buscar marca..."
                    />

                    @error('idMarca')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

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
                        :disabled="$idMarca === '' || $idTipoEquipo === ''"
                    />

                </div>


                {{-- Dirección MAC --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            text-xs
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            placeholder:text-[#50514F]/40
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
                    >

                </div>


                {{-- Fecha de compra --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            text-xs
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
                    >

                </div>


                {{-- Número de factura --}}
                <div class="min-w-0">

                    <label
                        class="
                            block
                            text-xs
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            placeholder:text-[#50514F]/40
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
                    >

                    @error('numeroFactura')
                        <p class="mt-1 text-xs text-red-600">
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
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            placeholder:text-[#50514F]/40
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
                    >

                    @error('numeroSerie')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

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


        {{-- ============================================================
            CAMPOS DINÁMICOS SEGÚN TIPO DE EQUIPO
        ============================================================ --}}
        @if ($idTipoEquipo !== '' && count($this->childFields) > 0)

            <section
                wire:key="child-fields-{{ $idTipoEquipo }}"
                class="
                    bg-white
                    border border-[#50514F]/10
                    rounded-xl
                    p-4
                    sm:p-6
                "
            >

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[#50514F]
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
                                        border-[#50514F]/30
                                        text-[#247BA0]
                                        focus:ring-[#247BA0]
                                    "
                                >

                                <label
                                    for="cf_{{ $field }}"
                                    class="text-sm text-[#50514F]"
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
                                        $this->catalogOptions($claveCatalogo),
                                        'id_valor'
                                    )"
                                    :label="$this->fieldLabels[$field] ?? $field"
                                    placeholder="Buscar..."
                                />


                            @else

                                <label
                                    class="
                                        block
                                        text-xs
                                        text-[#50514F]/60
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
                                            border border-[#50514F]/15
                                            rounded-md
                                            px-3 py-2.5
                                            focus:ring-1
                                            focus:ring-[#247BA0]
                                            focus:border-[#247BA0]
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
                                            border border-[#50514F]/15
                                            rounded-md
                                            px-3 py-2.5
                                            focus:ring-1
                                            focus:ring-[#247BA0]
                                            focus:border-[#247BA0]
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
                                            border border-[#50514F]/15
                                            rounded-md
                                            px-3 py-2.5
                                            focus:ring-1
                                            focus:ring-[#247BA0]
                                            focus:border-[#247BA0]
                                        "
                                    >

                                @endif

                            @endif

                        </div>

                    @endforeach

                </div>

            </section>

        @endif


        {{-- ============================================================
            INFORMACIÓN ADICIONAL
        ============================================================ --}}
        <section
            class="
                bg-white
                border border-[#50514F]/10
                rounded-xl
                p-4
                sm:p-6
            "
        >

            <h2
                class="
                    text-sm
                    font-semibold
                    text-[#50514F]
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
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            placeholder:text-[#50514F]/40
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
                        "
                    >

                    <p
                        class="
                            mt-1.5
                            text-[11px]
                            leading-relaxed
                            text-[#50514F]/50
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
                            text-[#50514F]/60
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
                            border border-[#50514F]/15
                            rounded-md
                            px-3 py-2.5
                            focus:ring-1
                            focus:ring-[#247BA0]
                            focus:border-[#247BA0]
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
                        <p class="mt-1 text-xs text-red-600">
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
                        text-[#50514F]/60
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
                        border border-[#50514F]/15
                        rounded-md
                        px-3 py-2.5
                        placeholder:text-[#50514F]/40
                        resize-y
                        focus:ring-1
                        focus:ring-[#247BA0]
                        focus:border-[#247BA0]
                    "
                ></textarea>

            </div>

        </section>


        {{-- ============================================================
            ACCIONES
        ============================================================ --}}
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
                    border-[#50514F]/20
                    rounded-lg
                    px-5
                    text-sm
                    font-medium
                    text-[#50514F]/70
                    bg-white
                    hover:bg-[#50514F]/5
                    transition-colors
                "
            >
                Cancelar
            </a>


            <button
                type="submit"
                class="
                    w-full
                    sm:w-auto
                    h-11
                    flex
                    items-center
                    justify-center
                    gap-2
                    bg-[#247BA0]
                    hover:bg-[#1d6688]
                    rounded-lg
                    px-5
                    text-sm
                    font-medium
                    text-white
                    transition-colors
                "
            >

                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                <span>
                    Añadir equipo
                </span>

            </button>

        </div>

    </form>

</div>