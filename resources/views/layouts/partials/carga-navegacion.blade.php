{{-- ============================================================
    ESQUELETO DE CARGA AL NAVEGAR
    ------------------------------------------------------------
    Al tocar un enlace con wire:navigate, si la página tarda más de
    120 ms, el contenido se sustituye por un esqueleto animado para
    que la respuesta se sienta inmediata. Al llegar la página nueva
    desaparece solo. Estilos en línea (no requieren Vite).
============================================================ --}}
<style>
    #esqueletoNavegacion {
        display: none;
    }

    html[data-navegando="true"] #esqueletoNavegacion {
        display: block;
    }

    html[data-navegando="true"] #contenido-principal > :not(#esqueletoNavegacion) {
        display: none !important;
    }

    .esq-bloque {
        border-radius: 0.5rem;
        background: linear-gradient(
            90deg,
            var(--theme-surface-soft) 0%,
            color-mix(in srgb, var(--theme-surface-soft) 60%, var(--theme-border) 40%) 50%,
            var(--theme-surface-soft) 100%
        );
        background-size: 200% 100%;
        animation: esq-brillo 1.2s ease-in-out infinite;
    }

    .esq-tarjeta {
        padding: 1rem;
        border: 1px solid var(--theme-border);
        border-radius: 0.75rem;
        background: var(--theme-surface);
    }

    .esq-fila {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-top: 1px solid var(--theme-border);
    }

    .esq-fila:first-child {
        border-top: 0;
    }

    @keyframes esq-brillo {
        from { background-position: 100% 0; }
        to { background-position: -100% 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .esq-bloque { animation: none; }
    }
</style>

<div id="esqueletoNavegacion" aria-hidden="true">
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        {{-- Título y ruta --}}
        <div class="esq-bloque" style="height: 1.5rem; width: min(14rem, 60%);"></div>
        <div class="esq-bloque" style="height: 0.75rem; width: min(9rem, 40%);"></div>

        {{-- Acciones --}}
        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <div class="esq-bloque" style="height: 2.5rem; flex: 1; max-width: 10rem;"></div>
            <div class="esq-bloque" style="height: 2.5rem; flex: 1; max-width: 10rem;"></div>
        </div>

        {{-- Tarjeta con filas --}}
        <div class="esq-tarjeta">
            @for ($fila = 0; $fila < 5; $fila++)
                <div class="esq-fila">
                    <div class="esq-bloque" style="height: 2.25rem; width: 2.25rem; flex-shrink: 0;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 0.4rem;">
                        <div class="esq-bloque" style="height: 0.8rem; width: {{ [72, 58, 66, 50, 62][$fila] }}%;"></div>
                        <div class="esq-bloque" style="height: 0.65rem; width: {{ [40, 34, 46, 30, 38][$fila] }}%;"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

<script>
    (() => {
        // Una sola vez por carga completa (el layout se reemplaza al navegar).
        if (window.trackitCargaNavegacion) {
            return;
        }

        window.trackitCargaNavegacion = true;

        let temporizador = null;
        let limite = null;

        const terminar = () => {
            clearTimeout(temporizador);
            clearTimeout(limite);
            document.documentElement.removeAttribute('data-navegando');
            document.getElementById('contenido-principal')?.removeAttribute('aria-busy');
        };

        document.addEventListener('livewire:navigate', () => {
            terminar();

            temporizador = setTimeout(() => {
                document.documentElement.setAttribute('data-navegando', 'true');
                document.getElementById('contenido-principal')?.setAttribute('aria-busy', 'true');
            }, 120);

            // Por si la navegación se cancela o falla.
            limite = setTimeout(terminar, 10000);
        });

        document.addEventListener('livewire:navigated', terminar);
    })();
</script>
