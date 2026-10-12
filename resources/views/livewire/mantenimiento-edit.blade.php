<div class="space-y-5">

    {{-- ============================================================
        INFORMACIÓN GENERAL
    ============================================================ --}}
    <section
        class="
            rounded-xl
            border
            border-[var(--theme-border)]
            bg-[var(--theme-surface)]
            p-5
        "
    >
        <h2
            class="
                mb-5
                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Información general
        </h2>


        {{-- Equipo --}}
        {{-- Equipo --}}
<div>
    <label
        class="
            mb-1.5
            block
            text-sm
            font-medium
            text-[var(--theme-text)]
        "
    >
        Equipo
    </label>

    <div
        class="
            w-full
            rounded-lg
            border
            border-[var(--theme-border)]
            bg-[var(--theme-surface-soft)]
            px-3
            py-2
            text-sm
            text-[var(--theme-text)]
        "
    >
        @if ($equipoSeleccionado)

            {{ $equipoSeleccionado->codigo_inventario }}
            —
            {{
                $equipoSeleccionado->nombre_equipo
                    ?: ($equipoSeleccionado->tipo_equipo ?? 'Equipo')
            }}

            @if ($equipoSeleccionado->numero_serie)
                — {{ $equipoSeleccionado->numero_serie }}
            @endif

        @else
            Equipo no disponible
        @endif
    </div>
</div>


        {{-- Datos automáticos del equipo --}}
        @if ($equipoSeleccionado)

            <div
                class="
                    mt-5
                    grid
                    grid-cols-1
                    gap-x-6
                    gap-y-4
                    md:grid-cols-2
                    xl:grid-cols-3
                "
            >
                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Identificador interno
                    </p>

                    <p class="mt-1 text-sm text-[var(--theme-text)]">
                        {{ $equipoSeleccionado->codigo_inventario ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Tipo de equipo
                    </p>

                    <p class="mt-1 text-sm text-[var(--theme-text)]">
                        {{ $equipoSeleccionado->tipo_equipo ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Marca
                    </p>

                    <p class="mt-1 text-sm text-[var(--theme-text)]">
                        {{ $equipoSeleccionado->marca ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Modelo
                    </p>

                    <p class="mt-1 text-sm text-[var(--theme-text)]">
                        {{ $equipoSeleccionado->modelo ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Número de serie
                    </p>

                    <p class="mt-1 text-sm text-[var(--theme-text)]">
                        {{ $equipoSeleccionado->numero_serie ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Fin de garantía
                    </p>

                    <p class="mt-1 text-sm text-[var(--theme-text)]">
                        {{
                            $equipoSeleccionado->fecha_fin_garantia
                                ? \Illuminate\Support\Carbon::parse(
                                    $equipoSeleccionado->fecha_fin_garantia
                                )->format('d/m/Y')
                                : '—'
                        }}
                    </p>
                </div>
            </div>

        @endif
    </section>


    {{-- ============================================================
        INFORMACIÓN DEL MANTENIMIENTO
    ============================================================ --}}
    <section
        class="
            rounded-xl
            border
            border-[var(--theme-border)]
            bg-[var(--theme-surface)]
            p-5
        "
    >
        <h2
            class="
                mb-5
                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Información del mantenimiento
        </h2>


        {{-- ========================================================
            FILA SUPERIOR
        ======================================================== --}}
        <div
            class="
                grid
                grid-cols-1
                gap-4
                md:grid-cols-2
                xl:grid-cols-4
            "
        >

            {{-- Tipo de mantenimiento --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Tipo de mantenimiento
                </label>

                <select
                    wire:model="tipoMantenimiento"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                >
                    <option value="">
                        Seleccionar
                    </option>

                    @foreach ($tiposMantenimiento as $item)
                        <option value="{{ $item->id_valor }}">
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>

                @error('tipoMantenimiento')
                    <p class="mt-1 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Tipo de intervención --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Tipo de intervención
                </label>

                <select
                    wire:model="tipoIntervencion"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                >
                    <option value="">
                        Seleccionar
                    </option>

                    @foreach ($tiposIntervencion as $item)
                        <option value="{{ $item->id_valor }}">
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Técnico responsable --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Técnico responsable
                </label>

                <select
                    wire:model="tecnicoId"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                >
                    <option value="">
                        Seleccionar
                    </option>

                    @foreach ($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id_usuario }}">
                            {{
                                trim(
                                    $tecnico->nombres . ' ' .
                                    $tecnico->apellido_paterno . ' ' .
                                    ($tecnico->apellido_materno ?? '')
                                )
                            }}
                        </option>
                    @endforeach
                </select>

                @error('tecnicoId')
                    <p class="mt-1 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Prioridad --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Prioridad
                </label>

                <select
                    wire:model="prioridad"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                >
                    <option value="">
                        Seleccionar
                    </option>

                    @foreach ($prioridades as $item)
                        <option value="{{ $item->id_valor }}">
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>


        {{-- ========================================================
            INICIO / FINALIZACIÓN / ESTADO
        ======================================================== --}}
        <div
            class="
                mt-5
                grid
                grid-cols-1
                gap-5
                xl:grid-cols-[1fr_1fr_0.75fr]
            "
        >

            {{-- Inicio --}}
            <div>
                <h3
                    class="
                        mb-3
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Inicio del mantenimiento
                </h3>

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-3
                        sm:grid-cols-2
                    "
                >

                    {{-- Fecha inicio --}}
                    <div>
                        <label
                            class="
                                mb-1.5
                                block
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            Fecha inicio
                        </label>

                        <input
                            type="date"
                            wire:model="fechaIntervencion"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-[var(--theme-border)]
                                bg-[var(--theme-surface)]
                                px-3
                                py-2
                                text-sm
                                text-[var(--theme-text)]
                                outline-none
                            "
                        >

                        @error('fechaIntervencion')
                            <p class="mt-1 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Hora inicio --}}
                    <div>
                        <label
                            class="
                                mb-1.5
                                block
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            Hora inicio
                        </label>

                        <input
                            type="time"
                            wire:model="horaInicio"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-[var(--theme-border)]
                                bg-[var(--theme-surface)]
                                px-3
                                py-2
                                text-sm
                                text-[var(--theme-text)]
                                outline-none
                            "
                        >

                        @error('horaInicio')
                            <p class="mt-1 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>
            </div>


            {{-- Finalización --}}
            <div
                class="
                    xl:border-l
                    xl:border-[var(--theme-border)]
                    xl:pl-5
                "
            >
                <h3
                    class="
                        mb-3
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Finalización del mantenimiento
                </h3>

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-3
                        sm:grid-cols-2
                    "
                >

                    {{-- Fecha fin --}}
                    <div>
                        <label
                            class="
                                mb-1.5
                                block
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            Fecha fin
                        </label>

                        <input
                            type="date"
                            wire:model="fechaFin"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-[var(--theme-border)]
                                bg-[var(--theme-surface)]
                                px-3
                                py-2
                                text-sm
                                text-[var(--theme-text)]
                                outline-none
                            "
                        >

                        @error('fechaFin')
                            <p class="mt-1 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Hora fin --}}
                    <div>
                        <label
                            class="
                                mb-1.5
                                block
                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            Hora fin
                        </label>

                        <input
                            type="time"
                            wire:model="horaFin"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-[var(--theme-border)]
                                bg-[var(--theme-surface)]
                                px-3
                                py-2
                                text-sm
                                text-[var(--theme-text)]
                                outline-none
                            "
                        >

                        @error('horaFin')
                            <p class="mt-1 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>
            </div>


            {{-- Estado --}}
            <div
                class="
                    flex
                    flex-col
                    justify-end
                "
            >
                <label
                    class="
                        mb-1.5
                        block
                        text-xs
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Estado
                </label>

                <select
                    wire:model="estadoMantenimiento"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                >
                    <option value="">
                        Seleccionar
                    </option>

                    @foreach ($estadosMantenimiento as $item)
                        <option value="{{ $item->id_valor }}">
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>

                @error('estadoMantenimiento')
                    <p class="mt-1 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>

    </section>


    {{-- ============================================================
        DESCRIPCIÓN DE LA INTERVENCIÓN
    ============================================================ --}}
    <section
        class="
            rounded-xl
            border
            border-[var(--theme-border)]
            bg-[var(--theme-surface)]
            p-5
        "
    >
        <h2
            class="
                mb-5
                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Descripción de la intervención
        </h2>

        <div
            class="
                grid
                grid-cols-1
                gap-4
                lg:grid-cols-3
            "
        >

            {{-- Falla --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Falla / motivo
                </label>

                <textarea
                    wire:model="fallaReportada"
                    rows="5"
                    placeholder="Describe la falla o motivo del mantenimiento..."
                    class="
                        w-full
                        resize-none
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                        placeholder:text-[var(--theme-text-muted)]
                    "
                ></textarea>
            </div>


            {{-- Diagnóstico --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Diagnóstico
                </label>

                <textarea
                    wire:model="diagnostico"
                    rows="5"
                    placeholder="Describe el diagnóstico..."
                    class="
                        w-full
                        resize-none
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                        placeholder:text-[var(--theme-text-muted)]
                    "
                ></textarea>
            </div>


            {{-- Intervención --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Intervención realizada
                </label>

                <textarea
                    wire:model="intervencionRealizada"
                    rows="5"
                    placeholder="Describe el trabajo realizado..."
                    class="
                        w-full
                        resize-none
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                        placeholder:text-[var(--theme-text-muted)]
                    "
                ></textarea>
            </div>

        </div>
    </section>


    {{-- ============================================================
        REFACCIONES / PARTES UTILIZADAS
    ============================================================ --}}
    <section
        class="
            rounded-xl
            border
            border-[var(--theme-border)]
            bg-[var(--theme-surface)]
            p-5
        "
    >
        <div
            class="
                mb-5
                flex
                flex-col
                gap-3

                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >
            <div>
                <h2
                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Refacciones / Partes utilizadas
                </h2>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Modifica, agrega o elimina las partes utilizadas durante la intervención.
                </p>
            </div>

            <button
                type="button"
                wire:click="agregarRefaccion"
                class="
                    rounded-lg
                    border
                    border-[var(--theme-border)]
                    px-3
                    py-2
                    text-sm
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                Agregar refacción
            </button>
        </div>


        @if (count($refacciones) === 0)

            <div
                class="
                    rounded-lg
                    border
                    border-dashed
                    border-[var(--theme-border)]
                    px-4
                    py-5
                    text-center
                "
            >
                <p class="text-sm text-[var(--theme-text-muted)]">
                    No se han agregado refacciones.
                </p>
            </div>

        @else

            <div class="space-y-3">

                @foreach ($refacciones as $index => $refaccion)

                    <div
                        wire:key="
                            refaccion-edit-
                            {{ $refaccion['id_mantenimiento_refaccion'] ?? 'new' }}-
                            {{ $index }}
                        "
                        class="
                            grid
                            grid-cols-1
                            gap-3

                            rounded-lg

                            border
                            border-[var(--theme-border)]

                            p-3

                            md:grid-cols-[minmax(0,2fr)_minmax(0,1.5fr)_120px_minmax(0,1fr)_auto]
                        "
                    >

                        {{-- Nombre --}}
                        <div>
                            <label
                                class="
                                    mb-1
                                    block
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Refacción
                            </label>

                            <input
                                type="text"
                                wire:model="refacciones.{{ $index }}.nombre_refaccion"
                                placeholder="Nombre"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-[var(--theme-border)]
                                    bg-[var(--theme-surface)]
                                    px-3
                                    py-2
                                    text-sm
                                    text-[var(--theme-text)]
                                    outline-none
                                "
                            >

                            @error("refacciones.$index.nombre_refaccion")
                                <p class="mt-1 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>


                        {{-- Código --}}
                        <div>
                            <label
                                class="
                                    mb-1
                                    block
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Código / parte
                            </label>

                            <input
                                type="text"
                                wire:model="refacciones.{{ $index }}.codigo_parte"
                                placeholder="Código"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-[var(--theme-border)]
                                    bg-[var(--theme-surface)]
                                    px-3
                                    py-2
                                    text-sm
                                    text-[var(--theme-text)]
                                    outline-none
                                "
                            >
                        </div>


                        {{-- Cantidad --}}
                        <div>
                            <label
                                class="
                                    mb-1
                                    block
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Cantidad
                            </label>

                            <input
                                type="number"
                                min="0.01"
                                step="0.01"
                                wire:model="refacciones.{{ $index }}.cantidad"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-[var(--theme-border)]
                                    bg-[var(--theme-surface)]
                                    px-3
                                    py-2
                                    text-sm
                                    text-[var(--theme-text)]
                                    outline-none
                                "
                            >

                            @error("refacciones.$index.cantidad")
                                <p class="mt-1 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>


                        {{-- Unidad --}}
                        <div>
                            <label
                                class="
                                    mb-1
                                    block
                                    text-xs
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Unidad
                            </label>

                            <input
                                type="text"
                                wire:model="refacciones.{{ $index }}.unidad"
                                placeholder="Pieza, unidad..."
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-[var(--theme-border)]
                                    bg-[var(--theme-surface)]
                                    px-3
                                    py-2
                                    text-sm
                                    text-[var(--theme-text)]
                                    outline-none
                                "
                            >
                        </div>


                        {{-- Quitar --}}
                        <div class="flex items-end">
                            <button
                                type="button"
                                wire:click="eliminarRefaccion({{ $index }})"
                                class="
                                    rounded-lg
                                    border
                                    border-[var(--theme-border)]
                                    px-3
                                    py-2
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Quitar
                            </button>
                        </div>

                    </div>

                @endforeach

            </div>

        @endif
    </section>


    {{-- ============================================================
        INFORMACIÓN ADICIONAL
    ============================================================ --}}
    <section
        class="
            rounded-xl
            border
            border-[var(--theme-border)]
            bg-[var(--theme-surface)]
            p-5
        "
    >
        <h2
            class="
                mb-5
                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            Información adicional
        </h2>

        <div
            class="
                grid
                grid-cols-1
                gap-4
                lg:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]
            "
        >

            {{-- Observaciones --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Observaciones
                </label>

                <textarea
                    wire:model="observaciones"
                    rows="4"
                    placeholder="Observaciones adicionales..."
                    class="
                        w-full
                        resize-none
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                ></textarea>
            </div>


            {{-- Próximo mantenimiento --}}
            <div>
                <label
                    class="
                        mb-1.5
                        block
                        text-sm
                        font-medium
                        text-[var(--theme-text)]
                    "
                >
                    Próximo mantenimiento
                </label>

                <input
                    type="date"
                    wire:model="fechaProximoMantenimiento"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-[var(--theme-border)]
                        bg-[var(--theme-surface)]
                        px-3
                        py-2
                        text-sm
                        text-[var(--theme-text)]
                        outline-none
                    "
                >
            </div>

        </div>
    </section>


    {{-- ============================================================
        ACCIONES
    ============================================================ --}}
    <div
        class="
            flex
            flex-col-reverse
            gap-3
            pb-4

            sm:flex-row
            sm:justify-end
        "
    >

        {{-- Cancelar --}}
        <a
            href="{{ route('mantenimientos.index') }}"
            wire:navigate
            class="
                inline-flex
                items-center
                justify-center

                rounded-lg

                border
                border-[var(--theme-border)]

                px-4
                py-2.5

                text-sm
                font-medium
                text-[var(--theme-text)]

                transition-colors

                hover:bg-[var(--theme-surface-soft)]
            "
        >
            Cancelar
        </a>


        {{-- Guardar cambios --}}
        <button
            type="button"

            wire:click="guardar"

            wire:loading.attr="disabled"
            wire:target="guardar"

            class="
                inline-flex
                items-center
                justify-center

                rounded-lg

                bg-[var(--theme-primary)]

                px-5
                py-2.5

                text-sm
                font-medium
                text-white

                transition-colors

                hover:bg-[var(--theme-primary-hover)]

                disabled:cursor-not-allowed
                disabled:opacity-50
            "
        >
            <span
                wire:loading.remove
                wire:target="guardar"
            >
                Guardar cambios
            </span>

            <span
                wire:loading
                wire:target="guardar"
            >
                Guardando...
            </span>
        </button>

    </div>

</div>
