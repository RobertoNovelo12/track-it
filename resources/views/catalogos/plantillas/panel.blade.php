@php
    $plantilla = app(\App\Services\PlantillaInstalacionService::class)->actual();
    $etiquetas = $plantilla['etiquetas'];
@endphp
<section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">
    <header class="border-b border-[var(--theme-border)] px-4 py-5 sm:px-6">
        <h2 class="text-lg font-semibold text-[var(--theme-text-strong)]">Plantilla de la aplicación</h2>
        <p class="mt-2 text-sm text-[var(--theme-text-muted)]">Define cómo se organiza esta instalación.</p>
    </header>
    <div class="space-y-6 p-4 sm:p-6">
        <div class="rounded-xl border border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-xl font-semibold text-[var(--theme-text-strong)]">{{ $plantilla['nombre'] }}</h3>
                <span class="rounded-full border border-[var(--theme-border)] px-3 py-1 text-xs text-[var(--theme-text)]">Plantilla activa · Permanente</span>
            </div>
            <p class="mt-3 text-sm text-[var(--theme-text-muted)]">
                Esta instalación utiliza {{ mb_strtolower($etiquetas['unidad_plural']) }} y departamentos.
                La plantilla se elige una sola vez y no puede cambiarse después.
            </p>
        </div>
        <div>
            <h3 class="font-semibold text-[var(--theme-text-strong)]">Lo que puedes personalizar</h3>
            <p class="mt-2 text-sm text-[var(--theme-text-muted)]">Puedes seguir agregando y editando estos registros. La plantilla no fija sus nombres ni su cantidad.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <a href="{{ route('catalogos.index', ['section' => 'hoteles']) }}" class="block rounded-xl border border-[var(--theme-border)] p-5 hover:border-[var(--theme-primary)]">
                <h4 class="font-semibold text-[var(--theme-text-strong)]">{{ $etiquetas['unidad_plural'] }}</h4>
                <p class="mt-2 text-sm text-[var(--theme-text-muted)]">Administra sus nombres, sedes, contactos y departamentos asignados.</p>
                <span class="mt-4 block text-sm text-[var(--theme-primary)]">Administrar {{ mb_strtolower($etiquetas['unidad_plural']) }} →</span>
            </a>
            <a href="{{ route('catalogos.index', ['section' => 'departamentos']) }}" class="block rounded-xl border border-[var(--theme-border)] p-5 hover:border-[var(--theme-primary)]">
                <h4 class="font-semibold text-[var(--theme-text-strong)]">Departamentos</h4>
                <p class="mt-2 text-sm text-[var(--theme-text-muted)]">Un departamento puede atender una o varias unidades, o todas, incluidas las que agregues después.</p>
                <span class="mt-4 block text-sm text-[var(--theme-primary)]">Administrar departamentos →</span>
            </a>
            <a href="{{ route('catalogos.index', ['section' => 'equipos']) }}" class="block rounded-xl border border-[var(--theme-border)] p-5 hover:border-[var(--theme-primary)]">
                <h4 class="font-semibold text-[var(--theme-text-strong)]">Tipos de equipo y formularios</h4>
                <p class="mt-2 text-sm text-[var(--theme-text-muted)]">Configura los campos, las secciones y las opciones de cada tipo de equipo.</p>
                <span class="mt-4 block text-sm text-[var(--theme-primary)]">Administrar tipos de equipo →</span>
            </a>
        </div>
    </div>
</section>
