{{-- ============================================================
    ORGANIZACIÓN › COLORES
    ------------------------------------------------------------
    Color principal de la organización: botones, enlaces, pestañas
    y elementos seleccionados. En modo oscuro se aclara un poco
    para conservar el contraste. La vista previa usa las mismas
    mezclas que layouts/partials/organizacion-logo-styles.
============================================================ --}}
@php
    $colorPredeterminado = \App\Services\OrganizacionService::COLOR_PRIMARIO_PREDETERMINADO;
    $colorGuardado = $org->colorPrimario();

    $paleta = [
        $colorPredeterminado => 'Azul (predeterminado)',
        '#1E3A8A' => 'Azul marino',
        '#0F766E' => 'Verde azulado',
        '#15803D' => 'Verde',
        '#A16207' => 'Dorado',
        '#B45309' => 'Ámbar',
        '#B91C1C' => 'Rojo',
        '#BE185D' => 'Rosa',
        '#7C3AED' => 'Violeta',
        '#374151' => 'Grafito',
    ];

    $previas = [
        ['titulo' => 'Modo claro', 'oscuro' => false, 'fondo' => '#FFFCFF', 'superficie' => '#FFFFFF', 'borde' => 'rgba(80, 81, 79, 0.10)', 'texto' => '#1F2023', 'atenuado' => '#7A7B79'],
        ['titulo' => 'Modo oscuro', 'oscuro' => true, 'fondo' => '#111416', 'superficie' => '#181C1F', 'borde' => 'rgba(255, 255, 255, 0.08)', 'texto' => '#F1F3F4', 'atenuado' => '#969DA1'],
    ];
@endphp

<form
    wire:submit="guardarColores"
    class="space-y-6"
    x-data="{
        color: $wire.entangle('colorPrimario'),

        get valido() {
            return /^#[0-9A-Fa-f]{6}$/.test(String(this.color ?? ''));
        },

        get hex() {
            return this.valido ? String(this.color).toUpperCase() : @js($colorPredeterminado);
        },

        /* Contraste del texto blanco sobre el color (WCAG). */
        get contraste() {
            const canal = (v) => {
                v = v / 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            };
            const h = this.hex;
            const l = 0.2126 * canal(parseInt(h.slice(1, 3), 16))
                + 0.7152 * canal(parseInt(h.slice(3, 5), 16))
                + 0.0722 * canal(parseInt(h.slice(5, 7), 16));

            return 1.05 / (l + 0.05);
        },

        /* Mismas mezclas que el layout (claro / oscuro). */
        variables(oscuro) {
            const c = this.hex;

            return oscuro
                ? `--p: color-mix(in srgb, ${c} 85%, #FFFFFF); --p-hover: color-mix(in srgb, ${c} 72%, #FFFFFF); --p-soft: color-mix(in srgb, ${c} 14%, transparent); --p-border: color-mix(in srgb, ${c} 40%, transparent);`
                : `--p: ${c}; --p-hover: color-mix(in srgb, ${c} 82%, #000000); --p-soft: color-mix(in srgb, ${c} 10%, transparent); --p-border: color-mix(in srgb, ${c} 30%, transparent);`;
        },
    }"
>
    <div>
        <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">Color principal</h3>
        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
            Se usa en botones, enlaces, pestañas y elementos seleccionados de todo el sistema.
            En modo oscuro se aclara un poco automáticamente para que se lea bien.
        </p>
    </div>

    {{-- Selector --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            <input
                type="color"
                x-model="color"
                class="h-10 w-14 cursor-pointer rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-1"
                aria-label="Elegir color principal"
            >

            <input
                type="text"
                x-model="color"
                maxlength="7"
                spellcheck="false"
                class="{{ $inputClass }} w-32 font-mono uppercase"
                aria-label="Código hexadecimal del color"
            >

            <span
                x-show="valido && hex !== @js($colorPredeterminado)"
                class="rounded-md bg-[var(--theme-surface-soft)] px-2 py-0.5 text-[11px] text-[var(--theme-text-muted)]"
            >Personalizado</span>
        </div>

        <div class="flex flex-wrap gap-2" role="list" aria-label="Colores sugeridos">
            @foreach ($paleta as $valor => $nombre)
                <button
                    type="button"
                    role="listitem"
                    x-on:click="color = @js($valor)"
                    class="relative h-8 w-8 rounded-full border border-[var(--theme-border-strong)] transition-transform hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--theme-primary)] focus-visible:ring-offset-2"
                    style="background-color: {{ $valor }}"
                    title="{{ $nombre }}"
                    aria-label="{{ $nombre }}"
                >
                    <svg
                        x-show="hex === @js($valor)"
                        class="absolute inset-0 m-auto h-4 w-4 text-white"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                    ><use href="{{ \App\Support\Icono::url('i-e1c3743') }}"></use></svg>
                </button>
            @endforeach
        </div>

        <p x-show="! valido" class="text-xs text-[var(--theme-danger)]">Escribe un color en formato #RRGGBB.</p>

        <p x-show="valido && contraste < 3" x-cloak class="text-xs text-[var(--theme-warning)]">
            Este color es muy claro: el texto blanco de los botones podría leerse mal. Prueba con un tono más oscuro.
        </p>

        @error('colorPrimario')
            <p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p>
        @enderror
    </div>

    {{-- Vista previa con los colores reales del sistema --}}
    <div>
        <p class="mb-2 text-xs text-[var(--theme-text-muted)]">Vista previa</p>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($previas as $previa)
                <figure>
                    <div
                        class="space-y-4 rounded-lg border p-4"
                        style="background-color: {{ $previa['fondo'] }}; border-color: {{ $previa['borde'] }}; color: {{ $previa['texto'] }};"
                        x-bind:style="variables({{ $previa['oscuro'] ? 'true' : 'false' }}) + ' background-color: {{ $previa['fondo'] }}; border-color: {{ $previa['borde'] }}; color: {{ $previa['texto'] }};'"
                    >
                        {{-- Pestañas --}}
                        <div class="flex gap-1 border-b text-xs font-medium" style="border-color: {{ $previa['borde'] }};">
                            <span class="border-b-2 px-3 py-2" style="border-color: var(--p); color: var(--p);">Seleccionada</span>
                            <span class="border-b-2 border-transparent px-3 py-2" style="color: {{ $previa['atenuado'] }};">Otra pestaña</span>
                        </div>

                        {{-- Botones --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex h-9 items-center rounded-md px-4 text-sm font-medium text-white" style="background-color: var(--p);">Guardar</span>
                            <span class="inline-flex h-9 items-center rounded-md border px-4 text-sm font-medium" style="border-color: var(--p-border); background-color: {{ $previa['superficie'] }};">Cancelar</span>
                            <span class="text-sm font-medium underline-offset-2" style="color: var(--p);">Ver detalle →</span>
                        </div>

                        {{-- Elemento seleccionado y aviso --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-medium" style="background-color: var(--p-soft); border-color: var(--p); color: var(--p);">Filtro activo</span>
                            <span class="inline-flex items-center gap-2 text-xs">
                                <span class="flex h-4 w-4 items-center justify-center rounded" style="background-color: var(--p);">
                                    <svg class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-e1c3743') }}"></use></svg>
                                </span>
                                Casilla marcada
                            </span>
                        </div>
                    </div>

                    <figcaption class="mt-1.5 text-[11px] text-[var(--theme-text-muted)]">{{ $previa['titulo'] }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-[var(--theme-border)] pt-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            @if ($colorGuardado)
                <button
                    type="button"
                    x-on:click="$dispatch('confirmar', { titulo: '¿Restaurar los colores predeterminados?', mensaje: 'El sistema volverá al color principal original.', confirmar: 'Restaurar', peligro: false, accion: () => $wire.restaurarColores() })"
                    wire:loading.attr="disabled"
                    class="text-xs text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] hover:underline"
                >
                    Restaurar colores predeterminados
                </button>
            @endif
        </div>

        <x-ui.button
            type="submit"
            target="guardarColores"
            size="lg"
            loading-text="Guardando…"
            class="w-full sm:w-auto"
        >
            Guardar colores
        </x-ui.button>
    </div>
</form>
