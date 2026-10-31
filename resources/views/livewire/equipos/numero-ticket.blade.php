<div>
    <label for="numero-ticket" class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">N° Tiket: <span class="text-[var(--theme-danger)]">*</span></label>
    {{-- Texto libre, tal como viene en el tiket (sin prefijo fijo). --}}
    <input id="numero-ticket" type="text" maxlength="26" required wire:model.live.debounce.350ms="numeroTicket" placeholder="Ej. 99273" class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)]">
    @error('numeroTicket') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
</div>
