<div class="space-y-5">

    <form
            data-avisar-cambios
        wire:submit="guardar"
        class="space-y-5"
    >

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
            <div>
                <x-searchable-select
                    label="Equipo"
                    wire-model="equipoId"

                    :options="collect($equipos)->map(fn ($equipo) => [
                        'value' => $equipo->id_equipo,
                        'label' => trim(
                            ($equipo->codigo_inventario ?? '') .
                            ' — ' .
                            ($equipo->nombre_equipo ?? 'Equipo') .
                            ' — ' .
                            (
                                $equipo->numero_serie
                                ?? $equipo->service_tag
                                ?? 'Sin serie'
                            )
                        ),
                    ])->values()->all()"

                    placeholder="Buscar equipo..."
                    :show-all-on-open="true"
                />

                @error('equipoId')
                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-danger)]
                        "
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Información automática del equipo --}}
            @if ($equipoSeleccionado)

                <div
                    class="
                        mt-5
                        grid
                        grid-cols-1
                        gap-x-8
                        gap-y-4
                        border-t
                        border-[var(--theme-border)]
                        pt-5

                        md:grid-cols-2
                        xl:grid-cols-3
                    "
                >

                    {{-- Identificador interno --}}
                    <div>
                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Identificador interno
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text)]">
                            {{ $equipoSeleccionado->codigo_inventario ?? '—' }}
                        </p>
                    </div>


                    {{-- Tipo --}}
                    <div>
                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Tipo de equipo
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text)]">
                            {{ $equipoSeleccionado->tipo_equipo ?? '—' }}
                        </p>
                    </div>


                    {{-- Marca --}}
                    <div>
                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Marca
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text)]">
                            {{ $equipoSeleccionado->marca ?? '—' }}
                        </p>
                    </div>


                    {{-- Modelo --}}
                    <div>
                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Modelo
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text)]">
                            {{ $equipoSeleccionado->modelo ?? '—' }}
                        </p>
                    </div>


                    {{-- Número de serie --}}
                    <div>
                        <p class="text-xs text-[var(--theme-text-muted)]">
                            Número de serie
                        </p>

                        <p class="mt-1 text-sm text-[var(--theme-text)]">
                            {{
                                $equipoSeleccionado->numero_serie
                                ?? $equipoSeleccionado->service_tag
                                ?? '—'
                            }}
                        </p>
                    </div>


                    {{-- Garantía --}}
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


            {{-- Primera fila --}}
            <div
                class="
                    grid
                    grid-cols-1
                    gap-4

                    md:grid-cols-2
                    xl:grid-cols-4
                "
            >

                {{-- Tipo mantenimiento --}}
                <div>
                    <x-searchable-select
                        label="Tipo de mantenimiento"
                        wire-model="tipoMantenimiento"

                        :options="collect($tiposMantenimiento)->map(fn ($item) => [
                            'value' => $item->id_valor,
                            'label' => $item->nombre,
                        ])->values()->all()"

                        placeholder="Buscar tipo..."
                        :show-all-on-open="true"
                    />

                    @error('tipoMantenimiento')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Tipo intervención --}}
                <div>
                    <x-searchable-select
                        label="Tipo de intervención"
                        wire-model="tipoIntervencion"

                        :options="collect($tiposIntervencion)->map(fn ($item) => [
                            'value' => $item->id_valor,
                            'label' => $item->nombre,
                        ])->values()->all()"

                        placeholder="Buscar intervención..."
                        :show-all-on-open="true"
                    />

                    @error('tipoIntervencion')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Técnico --}}
                <div>
                    <x-searchable-select
                        label="Técnico responsable"
                        wire-model="tecnicoId"

                        :options="collect($tecnicos)->map(fn ($item) => [
                            'value' => $item->id_usuario,
                            'label' => trim(
                                ($item->nombres ?? '') . ' ' .
                                ($item->apellido_paterno ?? '') . ' ' .
                                ($item->apellido_materno ?? '')
                            ),
                        ])->values()->all()"

                        placeholder="Buscar técnico..."
                        :show-all-on-open="true"
                    />

                    @error('tecnicoId')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Prioridad --}}
                <div>
                    <x-searchable-select
                        label="Prioridad"
                        wire-model="prioridad"

                        :options="collect($prioridades)->map(fn ($item) => [
                            'value' => $item->id_valor,
                            'label' => $item->nombre,
                        ])->values()->all()"

                        placeholder="Buscar prioridad..."
                        :show-all-on-open="true"
                    />

                    @error('prioridad')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>



            {{-- Segunda fila --}}
            <div
                class="
                    mt-5
                    grid
                    grid-cols-1
                    gap-5

                    xl:grid-cols-[1fr_1fr_310px]
                "
            >

                {{-- ====================================================
                    INICIO
                ==================================================== --}}
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
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
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
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </div>



                {{-- ====================================================
                    FINALIZACIÓN
                ==================================================== --}}
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
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
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
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </div>



                {{-- Estado --}}
                <div class="self-end">

                    <x-searchable-select
                        label="Estado"
                        wire-model="estadoMantenimiento"

                        :options="collect($estadosMantenimiento)->map(fn ($item) => [
                            'value' => $item->id_valor,
                            'label' => $item->nombre,
                        ])->values()->all()"

                        placeholder="Buscar estado..."
                        :show-all-on-open="true"
                    />

                    @error('estadoMantenimiento')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
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

                    xl:grid-cols-3
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
                        maxlength="500"
                        placeholder="Describe la falla o motivo del mantenimiento..."

                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
                    ></textarea>

                    @error('fallaReportada')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
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
                        maxlength="500"
                        placeholder="Describe el diagnóstico..."

                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
                    ></textarea>

                    @error('diagnostico')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
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
                        maxlength="500"
                        placeholder="Describe el trabajo realizado..."

                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)] resize-none"
                    ></textarea>

                    @error('intervencionRealizada')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

        </section>



        {{-- ============================================================
            REFACCIONES
        ============================================================ --}}
        {{-- Refacciones en Alpine: agregar y quitar es instantáneo.
            Se sincronizan con Livewire (entangle diferido) al guardar. --}}
        <section
            x-data="{
                filas: $wire.entangle('refacciones'),
                errores: {},

                agregar() {
                    this.filas.push({ nombre_refaccion: '', codigo_parte: '', cantidad: 1, unidad: '' });

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

                <h2
                    class="text-sm font-semibold text-[var(--theme-text-strong)]"
                >
                    Refacciones / partes utilizadas
                </h2>


                <button
                    type="button"
                    x-on:click="agregar()"

                    class="
                        inline-flex
                        h-10
                        items-center
                        justify-center
                        rounded-lg
                        bg-[var(--theme-primary)]
                        px-4
                        text-sm
                        font-semibold
                        text-white
                        transition-opacity

                        hover:opacity-90
                    "
                >
                    Agregar
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
                    <div
                        class="rounded-lg border border-dashed border-[var(--theme-border)] px-4 py-6 text-center text-sm text-[var(--theme-text-muted)]"
                    >
                        No se agregaron refacciones.
                    </div>
                </div>

                <div x-show="filas.length > 0" class="space-y-3">
                    <template x-for="(fila, indice) in filas" :key="indice">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_120px_140px_auto] md:items-end">
                            <div>
                                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                    Nombre de la refacción
                                </label>

                                <input
                                    type="text"
                                    data-refaccion-nombre
                                    x-model="fila.nombre_refaccion"
                                    placeholder="Nombre de la refacción..."
                                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                >

                                <p
                                    x-show="error(indice, 'nombre_refaccion')"
                                    x-text="error(indice, 'nombre_refaccion')"
                                    class="mt-1 text-xs text-[var(--theme-danger)]"
                                ></p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                    Código
                                </label>

                                <input
                                    type="text"
                                    x-model="fila.codigo_parte"
                                    placeholder="Código..."
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
                                    class="mt-1 text-xs text-[var(--theme-danger)]"
                                ></p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">
                                    Unidad
                                </label>

                                <input
                                    type="text"
                                    x-model="fila.unidad"
                                    placeholder="Ej. PZA"
                                    class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                                >
                            </div>

                            <button
                                type="button"
                                x-on:click="quitar(indice)"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--theme-border-strong)] px-3 text-sm font-medium text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]"
                            >
                                Quitar
                            </button>
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
                    gap-5

                    lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]
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
                        maxlength="500"
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

                    @error('fechaProximoMantenimiento')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
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

                sm:flex-row
                sm:justify-end
            "
        >

            <a
                href="{{ route('mantenimientos.index') }}"
                wire:navigate

                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>


            <x-ui.button
                type="submit"
                target="guardar"
                size="lg"
                loading-text="Guardando…"
                class="w-full sm:w-auto"
            >
                Guardar mantenimiento
            </x-ui.button>

        </div>

    </form>

</div>