<a href="{{ ($columna['href'])($row) }}" download @click.stop
    aria-label="Descargar {{ $texto }}"
    class="inline-flex items-center gap-2 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">
    <svg class="h-4 w-4 text-[var(--theme-danger)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M14 2H6v20h12V6l-4-4ZM14 2v5h5M9 12h6M9 16h6"/></svg>
    {{ $texto }}
</a>
