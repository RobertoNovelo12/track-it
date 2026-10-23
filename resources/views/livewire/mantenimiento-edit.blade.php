<div class="space-y-5" data-avisar-cambios>

    {{-- ============================================================
        INFORMACIÓN GENERAL
    ============================================================ --}}
    <section
        class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
    >
        <h2
            class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]"
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
        class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
    >
        <h2
            class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]"
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

                <x-searchable-select
                    wire-model="tipoMantenimiento"
                    :empty-as-null="false"
                    placeholder="Seleccionar"
                    :show-clear="true"
                    clear-label="Seleccionar"
                    :options="[
                        ...collect($tiposMantenimiento)->map(fn ($item) => ['value' => $item->id_valor, 'label' => $item->nombre])->all(),
                    ]"
                />

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

                <x-searchable-select
                    wire-model="tipoIntervencion"
                    :empty-as-null="false"
                    placeholder="Seleccionar"
                    :show-clear="true"
                    clear-label="Seleccionar"
                    :options="[
                        ...collect($tiposIntervencion)->map(fn ($item) => ['value' => $item->id_valor, 'label' => $item->nombre])->all(),
                    ]"
                />
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

                <x-searchable-select
                    wire-model="tecnicoId"
                    :empty-as-null="false"
                    placeholder="Seleccionar"
                    :show-clear="true"
                    clear-label="Seleccionar"
                    :options="[
                        ...collect($tecnicos)->map(fn ($tecnico) => ['value' => $tecnico->id_usuario, 'label' => trim( $tecnico->nombres . ' ' . $tecnico->apellido_paterno . ' ' . ($tecnico->apellido_materno ?? '') )])->all(),
                    ]"
                />

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

                <x-searchable-select
                    wire-model="prioridad"
                    :empty-as-null="false"
                    placeholder="Seleccionar"
                    :show-clear="true"
                    clear-label="Seleccionar"
                    :options="[
                        ...collect($prioridades)->map(fn ($item) => ['value' => $item->id_valor, 'label' => $item->nombre])->all(),
                    ]"
                />
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
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                >

                    {{-- Fecha inicio --}}
                    <div>
                        <label
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Fecha inicio
                        </label>

                        <input
                            type="date"
                            wire:model="fechaIntervencion"
                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
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
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Hora inicio
                        </label>

                        <input
                            type="time"
                            wire:model="horaInicio"
                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
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
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                >

                    {{-- Fecha fin --}}
                    <div>
                        <label
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Fecha fin
                        </label>

                        <input
                            type="date"
                            wire:model="fechaFin"
                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
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
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Hora fin
                        </label>

                        <input
                            type="time"
                            wire:model="horaFin"
                            class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
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
                    class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                >
                    Estado
                </label>

                <x-searchable-select
                    wire-model="estadoMantenimiento"
                    :empty-as-null="false"
                    placeholder="Seleccionar"
                    :show-clear="true"
                    clear-label="Seleccionar"
                    :options="[
                        ...collect($estadosMantenimiento)->map(fn ($item) => ['value' => $item->id_valor, 'label' => $item->nombre])->all(),
                    ]"
                />

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
        class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
    >
        <h2
            class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]"
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
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
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
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
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
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
                ></textarea>
            </div>

        </div>
    </section>


    {{-- ============================================================
        REFACCIONES / PARTES UTILIZADAS
    ============================================================ --}}
    {{-- Refacciones en Alpine: agregar y quitar es instantáneo.
        Se sincronizan con Livewire (entangle diferido) al guardar. --}}
    <section
        x-data="{
            filas: $wire.entangle('refacciones'),
            errores: {},

            agregar() {
                this.filas.push({ id_mantenimiento_refaccion: null, nombre_refaccion: '', codigo_parte: '', cantidad: 1, unidad: '' });

                this.$nextTick(() => {
                    const campos = this.$root.querySelectorAll('[data-refaccion-nombre]');
                    campos[campos.length - 1]?.focus();
                });
            },

            quitar(indice) {
                this.filas.splice(indice, 1);
                this.errores = {};
            },

            error(indice, campo) {
                return this.errores['refacciones.' + indice + '.' + campo]?.[0] ?? '';
            },

            leerErrores(elemento) {
                try {
                    this.errores = JSON.parse(elemento.dataset.errores || '{}');
                } catch (e) {
                    this.errores = {};
                }
            },
        }"
        class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
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
                    class="text-sm font-semibold text-[var(--theme-text-strong)]"
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
                x-on:click="agregar()"
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


        {{-- Errores de validación de las refacciones (los lee Alpine) --}}
        <div
            hidden
            data-errores="{{ json_encode(collect($errors->getMessages())->filter(fn ($mensajes, $clave) => str_starts_with($clave, 'refacciones.'))->all(), JSON_FORCE_OBJECT) }}"
            x-init="leerErrores($el); new MutationObserver(() => leerErrores($el)).observe($el, { attributes: true })"
        ></div>

        {{-- wire:ignore: las filas las pinta Alpine; Livewire no las toca. --}}
        <div wire:ignore>
            <div x-show="filas.length === 0">
                <div class="rounded-lg border border-dashed border-[var(--theme-border)] px-4 py-5 text-center">
                    <p class="text-sm text-[var(--theme-text-muted)]">
                        No se han agregado refacciones.
                    </p>
                </div>
            </div>

            <div x-show="filas.length > 0" class="space-y-3">
                <template x-for="(fila, indice) in filas" :key="indice">
                    <div class="grid grid-cols-1 gap-4 rounded-lg border border-[var(--theme-border)] p-3 md:grid-cols-[minmax(0,2fr)_minmax(0,1.5fr)_120px_minmax(0,1fr)_auto]">
                        <div>
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Refacción
                            </label>

                            <input
                                type="text"
                                data-refaccion-nombre
                                x-model="fila.nombre_refaccion"
                                placeholder="Nombre"
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                            >

                            <p
                                x-show="error(indice, 'nombre_refaccion')"
                                x-text="error(indice, 'nombre_refaccion')"
                                class="mt-1 text-xs text-red-500"
                            ></p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Código / parte
                            </label>

                            <input
                                type="text"
                                x-model="fila.codigo_parte"
                                placeholder="Código"
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                            >
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Cantidad
                            </label>

                            <input
                                type="number"
                                min="0.01"
                                step="0.01"
                                x-model="fila.cantidad"
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                            >

                            <p
                                x-show="error(indice, 'cantidad')"
                                x-text="error(indice, 'cantidad')"
                                class="mt-1 text-xs text-red-500"
                            ></p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                Unidad
                            </label>

                            <input
                                type="text"
                                x-model="fila.unidad"
                                placeholder="Pieza, unidad..."
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                            >
                        </div>

                        <div class="flex items-end">
                            <button
                                type="button"
                                x-on:click="quitar(indice)"
                                class="rounded-lg border border-[var(--theme-border)] px-3 py-2 text-sm text-[var(--theme-text-muted)]"
                            >
                                Quitar
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>


    {{-- ============================================================
        INFORMACIÓN ADICIONAL
    ============================================================ --}}
    <section
        class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6"
    >
        <h2
            class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]"
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
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
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
                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
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
            class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
        >
            Cancelar
        </a>


        {{-- Guardar cambios --}}
        <x-ui.button
            wire:click="guardar"
            data-guardar
            target="guardar"
            size="lg"
            loading-text="Guardando…"
            class="w-full sm:w-auto"
        >
            Guardar cambios
        </x-ui.button>

    </div>

</div>
