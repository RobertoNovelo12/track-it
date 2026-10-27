
<div class="space-y-5">
    @php
        $panel = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5';

        $input = 'w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]';

        $tiposDistintos = $tiposSeleccion->count() > 1;
    @endphp

    @if ($modo === 'elegir')

        <section class="{{ $panel }}">
            <h2 class="font-semibold text-[var(--theme-text-strong)]">
                Dar de baja {{ $elegidos->count() }} equipos
            </h2>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Seleccionados en Inventario. Elige cómo registrar la baja.
            </p>

            @if ($tiposDistintos)
                <div
                    role="alert"
                    class="mt-4 flex items-start gap-3 rounded-lg border border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] px-3 py-2.5 text-xs text-[var(--theme-text)]"
                >
                    <svg
                        class="mt-0.5 h-4 w-4 shrink-0 text-[var(--theme-warning)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M12 3 2 20h20L12 3Z"/>
                        <path d="M12 10v4M12 17h.01"/>
                    </svg>

                    <p>
                        <span class="font-semibold">
                            Vas a dar de baja equipos de diferente tipo:
                        </span>
                        {{ $tiposSeleccion->join(', ', ' y ') }}.
                        Revisa que sea correcto antes de continuar.
                    </p>
                </div>
            @endif

            <div class="mt-4 overflow-x-auto rounded-lg border border-[var(--theme-border)]">
                <table class="w-full min-w-[560px] text-left text-xs">
                    <thead class="bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
                        <tr>
                            <th class="px-3 py-2 font-medium">Código</th>
                            <th class="px-3 py-2 font-medium">Equipo</th>
                            <th class="px-3 py-2 font-medium">Tipo</th>
                            <th class="px-3 py-2 font-medium">Marca / modelo</th>
                            <th class="px-3 py-2 font-medium">Estado</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--theme-border)] text-[var(--theme-text)]">
                        @foreach ($elegidos as $elegido)
                            <tr wire:key="bm-elegir-{{ $elegido->id_equipo }}">
                                <td class="px-3 py-2 font-medium">
                                    {{ $elegido->codigo_inventario ?: '—' }}
                                </td>

                                <td class="px-3 py-2">
                                    {{ $elegido->nombre_equipo ?: '—' }}
                                </td>

                                <td class="px-3 py-2">
                                    {{ $elegido->tipo ?: 'Sin tipo' }}
                                </td>

                                <td class="px-3 py-2">
                                    {{ $elegido->marca ?: '—' }}
                                    /
                                    {{ $elegido->modelo ?: '—' }}
                                </td>

                                <td class="px-3 py-2">
                                    <x-status-badge
                                        :status="$elegido->estado ?? 'Sin estado'"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($activosSeleccion < $elegidos->count())
                <p class="mt-2 text-xs text-[var(--theme-text-muted)]">
                    Los equipos que ya estaban dados de baja
                    solo se incluyen en el acta.
                </p>
            @endif

            <div class="mt-5 grid gap-3 md:grid-cols-2">

                <button
                    type="button"
                    wire:click="todasEnUnDocumento"
                    wire:loading.attr="disabled"
                    class="rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-4 text-left transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)]"
                >
                    <span class="block text-sm font-semibold text-[var(--theme-text-strong)]">
                        Todas en un documento
                    </span>

                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                        Un formulario y una sola acta para todos
                        los equipos seleccionados.
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="unaPorUna"
                    wire:loading.attr="disabled"
                    class="rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-4 text-left transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)]"
                >
                    <span class="block text-sm font-semibold text-[var(--theme-text-strong)]">
                        Baja uno por uno
                    </span>

                    <span class="mt-1 block text-xs text-[var(--theme-text-muted)]">
                        Cada equipo tendrá su propio formulario,
                        fotografías y acta PDF. Al guardar,
                        se abrirá el siguiente automáticamente.
                    </span>
                </button>

            </div>

            <div class="mt-4 flex justify-end">
                <a
                    href="{{ route('equipos.index') }}"
                    wire:navigate
                    class="inline-flex h-9 items-center rounded-lg border border-[var(--theme-border-strong)] px-4 text-sm text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                >
                    Cancelar
                </a>
            </div>
        </section>

    @elseif (!$detalles)

        @include('livewire.equipos.baja-multiple-seleccion')

    @else

        <form
            wire:submit="guardar"
            class="grid grid-cols-1 items-start gap-5 xl:grid-cols-2"
        >
            <div class="space-y-4">

                @if ($desdeInventario)
                    <section class="{{ $panel }} flex flex-wrap items-start justify-between gap-3">

                        <div class="min-w-0">
                            <h2 class="font-semibold text-[var(--theme-text-strong)]">
                                {{ $modo === 'conjunta' ? 'Baja en un documento' : 'Acta de baja múltiple' }}
                                · {{ $elegidos->count() }}
                                {{ $elegidos->count() === 1 ? 'equipo' : 'equipos' }}
                            </h2>

                            @if ($modo === 'conjunta')
                                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                    Al generar el PDF se darán de baja
                                    {{ $activosSeleccion }}
                                    {{ $activosSeleccion === 1 ? 'equipo' : 'equipos' }}
                                    con estos datos.
                                </p>
                            @endif

                            @if ($tiposDistintos)
                                <p class="mt-2 text-xs font-medium text-[var(--theme-warning)]">
                                    Incluye equipos de diferente tipo:
                                    {{ $tiposSeleccion->join(', ', ' y ') }}.
                                </p>
                            @endif

                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($elegidos as $elegido)
                                    <span class="rounded-md border border-[var(--theme-border)] px-2 py-0.5 text-[11px] font-medium text-[var(--theme-text)]">
                                        {{ $elegido->codigo_inventario ?: $elegido->nombre_equipo }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- No existe la opción Terminar sin acta --}}
                        @if ($modo === 'conjunta' && $activosSeleccion > 0)
                            <button
                                type="button"
                                wire:click="volver" wire:loading.attr="disabled" wire:target="volver"
                                class="inline-flex h-9 shrink-0 items-center rounded-lg border border-[var(--theme-border-strong)] px-3 text-xs font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                            >
                                Cambiar modo
                            </button>
                        @endif

                    </section>
                @endif

                @if ($errors->any())
                    <div
                        role="alert"
                        class="rounded-lg border border-[var(--theme-danger)] bg-[var(--theme-danger-soft)] p-4 text-sm text-[var(--theme-danger)]"
                    >
                        <p class="font-medium">
                            No se pudo completar la baja.
                            Revisa los campos marcados.
                        </p>

                        @error('guardar')
                            <p class="mt-1">{{ $message }}</p>
                        @enderror

                        @error('seleccionados')
                            <p class="mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- INFORMACIÓN GENERAL --}}
                <section class="{{ $panel }} space-y-4">
                    <h2 class="font-semibold text-[var(--theme-text-strong)]">
                        Información general de la baja
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @include('livewire.equipos.numero-ticket')

                        <div>
                            <label
                                for="bm-solicitante"
                                class="mb-2 block text-xs text-[var(--theme-text)]"
                            >
                                Quién solicita la baja
                                <span class="text-[var(--theme-danger)]">*</span>
                            </label>

                            <input
                                id="bm-solicitante"
                                wire:model.live.debounce.350ms="solicitante"
                                maxlength="255"
                                placeholder="Ingresa el nombre completo"
                                class="{{ $input }}"
                            >

                            @error('solicitante')
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="bm-hotel"
                                class="mb-2 block text-xs text-[var(--theme-text)]"
                            >
                                Hotel
                                <span class="text-[var(--theme-danger)]">*</span>
                            </label>

                            <select
                                id="bm-hotel"
                                wire:model.live="hotelId"
                                class="{{ $input }}"
                            >
                                <option value="">Selecciona un hotel</option>

                                @foreach ($hoteles as $hotel)
                                    <option value="{{ $hotel->id_unidad }}">
                                        {{ $hotel->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('hotelId')
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="bm-departamento"
                                class="mb-2 block text-xs text-[var(--theme-text)]"
                            >
                                Departamento
                                <span class="text-[var(--theme-danger)]">*</span>
                            </label>

                            <select
                                id="bm-departamento"
                                wire:model.live="departamentoId"
                                class="{{ $input }}"
                                @disabled(!$hotelId)
                            >
                                <option value="">Selecciona un departamento</option>

                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento->id_departamento }}">
                                        {{ $departamento->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('departamentoId')
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="bm-fecha"
                                class="mb-2 block text-xs text-[var(--theme-text)]"
                            >
                                Fecha de baja
                                <span class="text-[var(--theme-danger)]">*</span>
                            </label>

                            <input
                                id="bm-fecha"
                                type="date"
                                wire:model.live="fechaBaja"
                                class="{{ $input }}"
                            >

                            @error('fechaBaja')
                                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                    <div>
                        <label
                            for="bm-motivo"
                            class="mb-2 block text-xs text-[var(--theme-text)]"
                        >
                            Motivo de la baja
                            <span class="text-[var(--theme-danger)]">*</span>
                        </label>

                        <input
                            id="bm-motivo"
                            wire:model.live.debounce.350ms="motivoBaja"
                            maxlength="500"
                            placeholder="Ingresa el motivo de la baja"
                            class="{{ $input }}"
                        >

                        @error('motivoBaja')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="bm-observaciones"
                            class="mb-2 block text-xs text-[var(--theme-text)]"
                        >
                            Observaciones
                        </label>

                        <textarea
                            id="bm-observaciones"
                            wire:model.live.debounce.350ms="observaciones"
                            maxlength="500"
                            rows="3"
                            placeholder="Ingresa las observaciones de la baja"
                            class="{{ $input }}"
                        ></textarea>

                        <p class="mt-1 text-right text-xs text-[var(--theme-text-muted)]">
                            {{ mb_strlen($observaciones) }}/500
                        </p>

                        @error('observaciones')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </section>

                {{-- FOTOGRAFÍAS --}}
                <section class="{{ $panel }}">
                    <h2 class="font-semibold text-[var(--theme-text-strong)]">
                        Evidencia fotográfica
                    </h2>

                    <p class="mb-4 mt-1 text-xs text-[var(--theme-text-muted)]">
                        Agrega las vistas frontal, trasera y las dos laterales
                        del conjunto de equipos.
                    </p>

                    <div class="grid gap-3 sm:grid-cols-2">

                        @foreach ([
                            'fotoFrontal' => 'Vista frontal',
                            'fotoTrasera' => 'Vista trasera',
                            'fotoLateral1' => 'Vista lateral 1',
                            'fotoLateral2' => 'Vista lateral 2',
                        ] as $campo => $titulo)

                            <div
                                wire:key="bm-{{ $campo }}"
                                class="rounded-lg border border-[var(--theme-border)] p-3"
                            >
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-[var(--theme-text-strong)]">
                                        {{ $titulo }}
                                    </h3>

                                    @if ($$campo)
                                        <button
                                            type="button"
                                            wire:click="eliminarFoto('{{ $campo }}')"
                                            class="text-xs text-[var(--theme-danger)]"
                                        >
                                            Quitar
                                        </button>
                                    @endif
                                </div>

                                @if (isset($documento['fotos'][$titulo]))
                                    <img
                                        src="{{ $documento['fotos'][$titulo] }}"
                                        alt="{{ $titulo }} de los equipos"
                                        class="h-36 w-full rounded-lg object-contain"
                                    >
                                @else
                                    <label class="flex min-h-32 cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] p-3 text-center text-xs text-[var(--theme-text)]">
                                        <svg
                                            class="h-5 w-5 text-[var(--theme-text-muted)]"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            aria-hidden="true"
                                        >
                                            <path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>
                                        </svg>

                                        <span>Seleccionar fotografía</span>

                                        <span class="text-[var(--theme-text-muted)]">
                                            JPG, PNG o WEBP · Máx. 5 MB
                                        </span>

                                        <input
                                            type="file"
                                            wire:model="{{ $campo }}"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="sr-only"
                                        >
                                    </label>
                                @endif

                                <p
                                    wire:loading
                                    wire:target="{{ $campo }}"
                                    class="mt-2 text-xs text-[var(--theme-text-muted)]"
                                >
                                    Cargando fotografía…
                                </p>

                                @error($campo)
                                    <p
                                        role="alert"
                                        class="mt-2 text-xs text-[var(--theme-danger)]"
                                    >
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endforeach

                    </div>
                </section>

                {{-- GENERAR EL ACTA --}}
                <div class="flex items-center justify-end gap-3">
                    <x-ui.button
                        type="submit"
                        variant="secondary"
                        target="guardar"
                        disable-target="guardar,fotoFrontal,fotoTrasera,fotoLateral1,fotoLateral2"
                        loading-text="Generando PDF…"
                    >
                        <x-slot:icon>
                            <svg
                                class="h-4 w-4 shrink-0 text-[var(--theme-danger)]"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path d="M14 2H6v20h12V6l-4-4ZM14 2v5h5M9 12h6M9 16h6"/>
                            </svg>
                        </x-slot:icon>

                        {{ $modo === 'conjunta'
                            ? 'Dar de baja y generar PDF'
                            : 'Generar PDF'
                        }}
                    </x-ui.button>
                </div>

            </div>

            {{-- COLUMNA DERECHA --}}
            <div class="min-w-0 space-y-4">

                <section class="{{ $panel }} min-w-0">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-[var(--theme-text-strong)]">
                            Vista rápida del documento final
                        </h2>

                        <span class="rounded border border-[var(--theme-border-strong)] px-2 py-1 text-xs text-[var(--theme-text-muted)]">
                            PDF
                        </span>
                    </div>

                    <div
                        class="baja-preview h-[600px] min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain pr-1 [scrollbar-width:thin] [scrollbar-color:#64748b_transparent]"
                        tabindex="0"
                        role="region"
                        aria-label="Vista previa del documento de baja múltiple"
                    >
                        @include(
                            'reportes.partials.baja-multiple-documento',
                            ['documento' => $documento]
                        )
                    </div>

                    <p class="mt-3 text-center text-xs text-[var(--theme-text-muted)]">
                        Vista preliminar. El tiket capturado se incluirá en el acta.
                    </p>
                </section>

                @include('livewire.equipos.firmas-baja')

                <section class="{{ $panel }}">
                    <h2 class="mb-4 font-semibold text-[var(--theme-text-strong)]">
                        Autorización
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">

                        @foreach ([
                            'autoriza' => 'Quién autoriza',
                            'puesto' => 'Puesto',
                        ] as $campo => $titulo)

                            <div>
                                <label
                                    for="bm-{{ $campo }}"
                                    class="mb-2 block text-xs text-[var(--theme-text)]"
                                >
                                    {{ $titulo }}
                                    <span class="text-[var(--theme-danger)]">*</span>
                                </label>

                                <input
                                    id="bm-{{ $campo }}"
                                    wire:model.live.debounce.350ms="{{ $campo }}"
                                    maxlength="255"
                                    class="{{ $input }}"
                                >

                                @error($campo)
                                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        @endforeach
                    </div>
                </section>
            </div>
        </form>

    @endif
</div>
