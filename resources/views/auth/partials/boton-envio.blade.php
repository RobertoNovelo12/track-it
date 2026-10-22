{{-- Botón de envío de los formularios de acceso: evita doble envío y muestra un spinner. --}}
<button
    id="{{ $id }}"
    type="submit"
    class="flex w-full items-center justify-center gap-2 rounded-md bg-[var(--theme-primary)] py-3 text-sm font-medium tracking-wide text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-wait disabled:opacity-80"
>
    <span data-normal class="inline-flex items-center justify-center gap-2">
        {{ $texto }}
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
        </svg>
    </span>

    <span data-cargando class="hidden items-center justify-center gap-2">
        <x-ui.spinner size="sm" />
        <span>{{ $cargando }}</span>
    </span>
</button>

<script>
    (() => {
        const boton = document.getElementById(@js($id));
        const normal = boton?.querySelector('[data-normal]');
        const cargando = boton?.querySelector('[data-cargando]');

        /*
         * Se cambian las dos clases de display: si solo se agrega «hidden»,
         * «inline-flex» sigue ganando y el texto normal no se oculta.
         */
        const mostrar = (enCarga) => {
            if (! boton) {
                return;
            }

            boton.disabled = enCarga;
            normal?.classList.toggle('hidden', enCarga);
            normal?.classList.toggle('inline-flex', ! enCarga);
            cargando?.classList.toggle('hidden', ! enCarga);
            cargando?.classList.toggle('inline-flex', enCarga);
        };

        boton?.form?.addEventListener('submit', () => mostrar(true));

        // Al volver con «atrás», el navegador puede restaurar el botón en carga.
        window.addEventListener('pageshow', (evento) => {
            if (evento.persisted) {
                mostrar(false);
            }
        });
    })();
</script>
