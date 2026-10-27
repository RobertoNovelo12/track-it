<div>
    <label for="numero-ticket" class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">N° Tiket: <span class="text-[var(--theme-danger)]">*</span></label>
    <div class="flex items-center gap-2">
        <span class="text-sm text-[var(--theme-text)]">GPRVM-</span>
        <input id="numero-ticket" type="text" inputmode="numeric" pattern="[0-9]+" maxlength="20" required wire:model.live.debounce.350ms="numeroTicket" placeholder="99273" class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)]">
    </div>
    @error('numeroTicket') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
</div>
