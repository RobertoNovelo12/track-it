{{--
    Botón dividido del escáner: «Enviar a mi PC» (o al último usuario
    elegido) + flecha para elegir a quién más enviarlo.
    Usa el estado de layouts/partials/escaner-movil.
--}}
<div x-show="puedeEnviar" class="flex min-w-0">
    <button
        type="button"
        x-on:click="enviar()"
        x-bind:disabled="enviando"
        class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 rounded-l-[var(--radius-md)] bg-[var(--theme-primary)] px-3 text-sm font-medium text-white disabled:opacity-60"
    >
        <span x-show="enviando" class="shrink-0"><x-ui.spinner size="sm" /></span>
        <span class="truncate" x-text="destino ? 'Enviar a ' + destino.corto : 'Enviar a mi PC'">Enviar a mi PC</span>
    </button>

    <button
        type="button"
        x-on:click="abrirDestinatarios()"
        x-bind:disabled="enviando"
        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-r-[var(--radius-md)] border-l border-white/25 bg-[var(--theme-primary)] text-white disabled:opacity-60"
        aria-label="Elegir a quién enviarlo"
        aria-haspopup="listbox"
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </button>
</div>
