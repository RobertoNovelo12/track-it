{{-- ============================================================
    TARJETAS - MÓVIL
============================================================ --}}
@php
    /*
     * Si no se define título, se usa la primera columna;
     * en ese caso no se repite dentro de la tarjeta.
     */
    $primeraColumna = $columnasMovil->first();

    $tituloTarjeta = $mobileTitle instanceof \Closure
        ? $mobileTitle
        : fn ($row) => $primeraColumna ? $valor($row, $primeraColumna) : '';

    $camposTarjeta = $mobileTitle instanceof \Closure
        ? $columnasMovil
        : $columnasMovil->slice(1)->values();
@endphp

<div
    class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-3 transition-opacity duration-[var(--motion-base)]"
    wire:loading.delay.class="opacity-60"
    @if (filled($loadingTarget ?? null)) wire:target="{{ $loadingTarget }}" @elseif (filled($loadingExcept ?? null)) wire:target.except="{{ $loadingExcept }}" @endif
>

    @forelse ($registros as $row)

        @php
            $id = $clave($row);
            $puedeSeleccionar = $selectable && !($rowSelectable instanceof \Closure && !$rowSelectable($row));
            $accionesFila = $acciones($row);

            $opcionesMovil = $accionesFila['menu'];

            if ($accionesFila['detail']) {
                array_unshift($opcionesMovil, [
                    'label' => 'Ver detalle',
                    'href' => $accionesFila['detail'],
                ]);
            }

            $subtitulo = $mobileSubtitle instanceof \Closure
                ? $mobileSubtitle($row)
                : null;
        @endphp

        <article
            data-dado-de-baja="{{ $rowDecommissioned instanceof \Closure && $rowDecommissioned($row) ? '1' : '0' }}"
            @if ($selectionModel)
                x-data="{ menuOpen: false,
                    get selected() { return $wire.get(@js($selectionModel)).map(String).includes(@js((string) $id)); },
                    set selected(value) {
                        let ids = $wire.get(@js($selectionModel)).map(String);
                        if (value && !ids.includes(@js((string) $id)) && (!@js($selectionLimit) || ids.length < @js($selectionLimit))) ids.push(@js((string) $id));
                        if (!value) ids = ids.filter(id => id !== @js((string) $id));
                        $wire.set(@js($selectionModel), ids);
                    }
                }"
            @else
                x-data="{ selected: false, menuOpen: false }"
            @endif
            @if ($highlightKey !== null && (string) $highlightKey === (string) $id)
                style="box-shadow: inset 3px 0 var(--theme-primary); background-color: var(--theme-primary-soft-subtle);"
                aria-label="Archivo recién generado"
            @endif
            @if ($selectable)
                :data-export-selected="@js($puedeSeleccionar) && selected ? @js((string) $id) : null"
            @endif
            @data-table-menu-open.window="if ($event.detail !== $el) menuOpen = false"

            @click="if (@js($puedeSeleccionar)) selected = ! selected"

            wire:key="{{ $keyPrefix }}-card-{{ $id }}"

            class="
                bg-[var(--theme-surface)]

                border

                rounded-xl

                p-4

                shadow-sm

                transition-all

                cursor-pointer
                select-none
            "

            :class="
                selected
                    ? 'border-[var(--theme-primary-border-strong)] ring-1 ring-[var(--theme-primary-soft)]'
                    : 'border-[var(--theme-border)]'
            "
        >

            {{-- =================================================
                CABECERA
            ================================================= --}}
            <div class="flex items-start gap-3">

                {{-- SELECTOR VISUAL --}}
                @if ($selectable)
                    <button
                        type="button"

                        @disabled(!$puedeSeleccionar)
                        @click.stop="if (@js($puedeSeleccionar)) selected = ! selected"

                        class="
                            shrink-0

                            mt-0.5

                            w-5
                            h-5

                            rounded-full

                            border-2

                            flex
                            items-center
                            justify-center

                            transition-all
                        "

                        :class="
                            selected
                                ? 'border-[var(--theme-primary)] bg-[var(--theme-primary)]'
                                : 'border-[var(--theme-border-strong)] bg-[var(--theme-surface)]'
                        "

                        aria-label="Seleccionar registro"
                    >
                        <svg
                            x-show="selected"
                            x-cloak

                            class="w-3 h-3 text-white"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="3"
                        ><use href="{{ \App\Support\Icono::url('i-e7e7dec') }}"></use></svg>
                    </button>
                @endif


                {{-- TÍTULO / SUBTÍTULO --}}
                <div class="min-w-0 flex-1">

                    <p
                        class="
                            text-sm
                            font-semibold
                            leading-snug

                            text-[var(--theme-text-strong)]

                            break-words
                        "
                    >
                        {{ $tituloTarjeta($row) }}
                    </p>

                    @if (filled($subtitulo))
                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                            {{ $subtitulo }}
                        </p>
                    @endif

                </div>


                {{-- MÁS ACCIONES --}}
                @if ($opcionesMovil !== [])
                    <div
                        class="relative shrink-0"
                        @click.outside="menuOpen = false"
                        @trackit-cerrar-menus.window="menuOpen = false"
                    >

                        <button
                            type="button"

                            @click.stop="
                                menuOpen = ! menuOpen;

                                if (menuOpen) {
                                    $dispatch('data-table-menu-open', $root);
                                }
                            "

                            class="
                                w-8
                                h-8

                                flex
                                items-center
                                justify-center

                                rounded-md

                                text-[var(--theme-text)]

                                hover:bg-[var(--theme-surface-soft)]

                                transition-colors
                            "

                            aria-label="Más acciones"
                            :aria-expanded="menuOpen"
                        >
                            <svg
                                class="w-5 h-5"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                aria-hidden="true"
                            ><use href="{{ \App\Support\Icono::url('i-6f7d564') }}"></use></svg>
                        </button>

                        <div
                            x-show="menuOpen"
                            x-cloak
                            @click.stop

                            class="
                                absolute

                                right-0
                                top-9

                                w-40

                                bg-[var(--theme-surface)]

                                border
                                border-[var(--theme-border)]

                                rounded-lg

                                shadow-lg

                                z-20

                                overflow-hidden

                                text-left
                            "
                        >
                            @include('components.data-table.partials.menu-items', [
                                'items' => $opcionesMovil,
                                'itemPadding' => 'py-2.5',
                            ])
                        </div>

                    </div>
                @endif

            </div>


            {{-- =================================================
                INFORMACIÓN
            ================================================= --}}
            @if ($camposTarjeta->isNotEmpty())
                <div class="mt-3 space-y-2">

                    @foreach ($camposTarjeta as $columna)
                        @continue($columna['mobile'] === 'filled' && $estaVacio($row, $columna))

                        @php
                            $etiqueta = $columna['mobileLabel'] ?? $columna['label'];
                            $texto = $valor($row, $columna);
                        @endphp

                        @if ($columna['type'] === 'download')
                            @include('components.data-table.partials.download')
                        @elseif ($columna['type'] === 'list')
                            <div class="text-xs text-[var(--theme-text-muted)]">
                                <span>{{ $etiqueta }}:</span>

                                <div class="mt-1 flex flex-wrap gap-1">
                                    @forelse ($lista($row, $columna) as $elemento)
                                        <span class="rounded-md border border-[var(--theme-border)] px-2 py-0.5 text-[11px] text-[var(--theme-text)]">
                                            {{ $elemento }}
                                        </span>
                                    @empty
                                        <span>{{ $columna['default'] }}</span>
                                    @endforelse
                                </div>
                            </div>
                        @elseif ($columna['type'] === 'status')
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-[var(--theme-text-muted)]">
                                    {{ $etiqueta }}:
                                </span>

                                <x-status-badge :status="$texto" />
                            </div>
                        @else
                            <p class="text-xs text-[var(--theme-text-muted)]">
                                <span>{{ $etiqueta }}:</span>

                                <span class="break-all">{{ $texto }}</span>
                            </p>
                        @endif
                    @endforeach

                </div>
            @endif

        </article>

    @empty

        {{-- =====================================================
            SIN RESULTADOS
        ===================================================== --}}
        <div class="sm:col-span-2 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">
            <x-ui.empty-state
                :variant="$emptyVariant"
                :title="$emptyTitle"
                :description="$emptyMessage"
            />
        </div>

    @endforelse

</div>
