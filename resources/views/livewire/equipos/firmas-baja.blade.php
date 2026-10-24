<section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">
    <h2 class="mb-4 font-semibold text-[var(--theme-text-strong)]">Firmas de baja</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach (\App\Livewire\EquipoBaja::FIRMANTES as $clave => $titulo)
            <div>
                <label for="firma-{{ $clave }}" class="mb-2 block text-xs text-[var(--theme-text)]">{{ $titulo }}</label>
                <input id="firma-{{ $clave }}" wire:model.live.debounce.350ms="firmas.{{ $clave }}" maxlength="255" placeholder="Nombre completo" class="w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] focus:border-[var(--theme-primary)]">
                @error('firmas.'.$clave)<p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</section>
