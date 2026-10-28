{{-- ============================================================
    BUSCADOR (Administración)
    Los atributos (wire:model, placeholder, id) pasan al <input>.
============================================================ --}}
@props(['label' => 'Buscar'])

<div class="relative min-w-0 flex-1">

    <div class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-[var(--theme-text-muted)]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-f6ba916') }}"></use></svg>
    </div>

    <input
        type="search"
        aria-label="{{ $label }}"
        {{ $attributes->class('w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] py-2.5 pl-10 pr-3 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] transition-colors focus:border-[var(--theme-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)]') }}
    >

</div>
