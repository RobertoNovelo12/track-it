<a href="{{ ($columna['href'])($row) }}" download @click.stop
    aria-label="Descargar {{ $texto }}"
    class="inline-flex items-center gap-2 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">
    <svg class="h-4 w-4 text-[var(--theme-danger)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-6c5b6b4') }}"></use></svg>
    {{ $texto }}
</a>
