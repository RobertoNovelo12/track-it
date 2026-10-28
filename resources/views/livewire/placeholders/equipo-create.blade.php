<div aria-busy="true" aria-label="Cargando formulario de equipo">
    <div class="space-y-5">
        {{-- TIPO DE EQUIPO (misma estructura que el formulario) --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="mb-4 text-sm font-semibold text-[var(--theme-text-strong)]">
                        Tipo de equipo
                    </h2>

                    <div class="max-w-lg">
                        <label
                            for="loading-equipment-type"
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            Tipo de equipo *
                        </label>

                        <select
                            id="loading-equipment-type"
                            disabled
                            class="h-[42px] w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text-muted)] disabled:cursor-default disabled:opacity-100"
                        >
                            <option>Cargando tipos de equipo...</option>
                        </select>
                    </div>

                    <p class="mt-3 text-xs text-[var(--theme-text-muted)]">
                        Selecciona un tipo para mostrar sus campos configurados.
                    </p>
                </div>

                <div aria-hidden="true" class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
                    <div class="h-3 w-40 animate-pulse rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="h-[72px] w-[200px] animate-pulse rounded-md bg-[var(--theme-surface-soft)]"></div>
                </div>
            </div>
        </section>

        {{-- INFORMACIÓN GENERAL --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                Información general
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ([
                    [
                        'id' => 'nombre',
                        'label' => 'Nombre del equipo',
                        'type' => 'text',
                    ],
                    [
                        'id' => 'estado',
                        'label' => 'Estado',
                        'type' => 'select',
                    ],
                    [
                        'id' => 'marca',
                        'label' => 'Marca',
                        'type' => 'select',
                    ],
                    [
                        'id' => 'serie',
                        'label' => 'Número de serie',
                        'type' => 'text',
                    ],
                    [
                        'id' => 'factura',
                        'label' => 'Número de factura',
                        'type' => 'text',
                    ],
                ] as $field)
                    <div class="min-w-0">
                        <label
                            for="loading-equipment-{{ $field['id'] }}"
                            class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                        >
                            {{ $field['label'] }} *
                        </label>

                        @if ($field['type'] === 'select')
                            <select
                                id="loading-equipment-{{ $field['id'] }}"
                                disabled
                                class="h-[42px] w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text-muted)] disabled:cursor-default disabled:opacity-100"
                            >
                                <option>Cargando...</option>
                            </select>
                        @else
                            <input
                                id="loading-equipment-{{ $field['id'] }}"
                                type="text"
                                disabled
                                class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] disabled:cursor-default disabled:opacity-100"
                            >
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- INFORMACIÓN ADICIONAL --}}
        <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                Información adicional
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="min-w-0">
                    <label
                        for="loading-equipment-hotel"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Hotel *
                    </label>

                    <select
                        id="loading-equipment-hotel"
                        disabled
                        class="h-[42px] w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text-muted)] disabled:cursor-default disabled:opacity-100"
                    >
                        <option>Cargando hoteles...</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- ACCIONES --}}
        <div class="flex justify-end gap-3">
            <a
                href="{{ route('equipos.index') }}"
                class="rounded-lg border border-[var(--theme-border-strong)] px-5 py-3 text-sm text-[var(--theme-text)]"
            >
                Cancelar
            </a>

            <button
                type="button"
                disabled
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--theme-primary)] px-5 py-3 text-sm font-medium text-white disabled:cursor-default disabled:opacity-50"
            >
                <svg
                    class="h-4 w-4 animate-spin motion-reduce:animate-none"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                ><use href="{{ \App\Support\Icono::url('i-ef15586') }}"></use></svg>

                <span>Cargando...</span>
            </button>
        </div>
    </div>
</div>