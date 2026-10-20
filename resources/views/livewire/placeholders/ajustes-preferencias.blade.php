<div class="animate-pulse">

    {{-- Las pestañas falsas solo hacen falta en la primera carga (lazy).
         Dentro de Ajustes ya se ven las reales: allí se pasa sinNavegacion. --}}
    @unless ($sinNavegacion ?? false)
    {{-- ============================================================
        NAVEGACIÓN
    ============================================================ --}}
    <div class="mb-5 hidden gap-2 sm:flex">
        @for ($i = 0; $i < 4; $i++)
            <div class="h-9 w-28 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)]"></div>
        @endfor
    </div>
    @endunless


    {{-- ============================================================
        SECCIONES (Apariencia, Inicio y listados, Formato regional)
        Misma estructura que livewire/ajustes-preferencias.
    ============================================================ --}}
    <div class="space-y-5">
        @foreach ([2, 3, 2] as $filas)
            <section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">
                <div class="border-b border-[var(--theme-border)] px-4 py-4 sm:px-5">
                    <div class="h-4 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                    <div class="mt-2 h-3 w-64 max-w-full rounded bg-[var(--theme-surface-soft)]"></div>
                </div>

                <div class="divide-y divide-[var(--theme-border)]">
                    @for ($fila = 0; $fila < $filas; $fila++)
                        <div class="flex flex-col gap-3 px-4 py-4 sm:px-5 md:flex-row md:items-center md:justify-between md:gap-8">
                            <div class="min-w-0 flex-1">
                                <div class="h-4 w-40 rounded bg-[var(--theme-surface-soft)]"></div>
                                <div class="mt-2 h-3 w-80 max-w-full rounded bg-[var(--theme-surface-soft)]"></div>
                            </div>

                            <div class="h-10 w-full shrink-0 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] md:w-72"></div>
                        </div>
                    @endfor
                </div>
            </section>
        @endforeach

        <div class="flex justify-end">
            <div class="h-10 w-full rounded-lg bg-[var(--theme-surface-soft)] sm:w-48"></div>
        </div>
    </div>

</div>
