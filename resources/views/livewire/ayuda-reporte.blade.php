@php
    $inputClass = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]';
@endphp

{{-- La página y el navegador se agregan solos al enviar: la página es la
     última que se visitó antes de Ayuda (la recuerda «Volver»). --}}
<form
    wire:submit="enviar"
    data-avisar-cambios
    x-data
    x-on:submit.capture="
        let previa = '';
        try { previa = sessionStorage.getItem('trackit.volver.previa') || document.referrer || ''; } catch (e) { previa = document.referrer || ''; }
        $wire.pagina = previa;
        $wire.navegador = navigator.userAgent;
    "
    class="space-y-4"
>
    <div>
        <label for="reporte-asunto" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">Asunto *</label>
        <input id="reporte-asunto" type="text" wire:model="asunto" maxlength="120" placeholder="Ej. No puedo exportar el inventario a PDF" class="{{ $inputClass }}">
        @error('asunto') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="reporte-descripcion" class="mb-1.5 block text-xs text-[var(--theme-text-muted)]">¿Qué pasó? *</label>
        <textarea id="reporte-descripcion" rows="5" wire:model="descripcion" maxlength="2000" placeholder="Qué estabas haciendo, qué esperabas que pasara y qué mensaje apareció." class="{{ $inputClass }}"></textarea>
        @error('descripcion') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
    </div>

    <p class="text-xs text-[var(--theme-text-muted)]">
        Se adjuntan automáticamente la página en la que estabas, tu navegador y la fecha.
    </p>

    @if ($enviado)
        <p class="rounded-md bg-[var(--theme-success-soft)] px-3 py-2 text-xs font-medium text-[var(--theme-success)]">
            Reporte enviado. El equipo de soporte lo verá en sus notificaciones.
        </p>
    @endif

    <div class="flex justify-end">
        <x-ui.button type="submit" target="enviar" loading-text="Enviando…" class="w-full sm:w-auto">
            Enviar reporte
        </x-ui.button>
    </div>
</form>
