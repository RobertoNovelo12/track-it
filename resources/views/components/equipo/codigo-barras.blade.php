{{-- ============================================================
    CÓDIGO DE BARRAS DE UN EQUIPO
    ------------------------------------------------------------
    Contiene solo el número del código de inventario
    (ACT-000061 → 000061). La imagen la genera la ruta
    equipos.codigo-barras (App\Support\CodigoBarras, Code 128).

    Con el código conocido (Editar, Ver detalles):
        <x-equipo.codigo-barras :codigo="$equipo['codigo_inventario']" />

    Con una expresión de Alpine (Crear: vista previa del siguiente):
        <x-equipo.codigo-barras x-codigo="codigoInventarioPreview" vista-previa />

    Props:
    - codigo        Código de inventario (texto).
    - x-codigo      Expresión de Alpine con el código.
    - vista-previa  Indica que aún no está asignado (se guarda al crear).
    - descargable   Muestra «Descargar» (por defecto sí).
    - compacto      Versión más pequeña.
============================================================ --}}
@props([
    'codigo' => null,
    'xCodigo' => null,
    'vistaPrevia' => false,
    'descargable' => true,
    'compacto' => false,
])

@php
    $base = rtrim(url('/equipos/codigo-barras'), '/') . '/';
    $numero = \App\Support\CodigoBarras::numero($codigo);
    /*
     * El código se muestra SIEMPRE a su tamaño natural: reducirlo con CSS
     * hace que el navegador redondee las barras a píxeles enteros y se
     * pierden espacios de un módulo (deja de poder leerse, sobre todo en
     * pantallas a escala 1x). Para la versión compacta se pide al servidor
     * una imagen más baja (?alto=), sin tocar el ancho de las barras.
     */
    $alto = $compacto ? 40 : 60;
    $altoParam = '?alto=' . $alto;
    $claseImagen = 'block max-w-none';
@endphp

@if ($xCodigo)
    {{-- Dinámico (Alpine): la expresión se evalúa en el formulario que lo contiene --}}
    @php
        $numeroJs = "String(" . $xCodigo . " ?? '').replace(/\\D+/g, '')";
    @endphp

    <div
        {{ $attributes->class(['flex flex-col items-start gap-1.5']) }}
        x-show="{{ $numeroJs }} !== ''"
    >
        <div class="inline-flex max-w-full flex-col items-center overflow-x-auto rounded-md border border-[var(--theme-border)] bg-white px-3 pb-1 pt-2">
            <img
                x-bind:src="{{ \Illuminate\Support\Js::from($base) }} + {{ $numeroJs }} + '.svg{{ $altoParam }}'"
                x-bind:alt="'Código de barras ' + {{ $numeroJs }}"
                class="{{ $claseImagen }}"
                decoding="async"
            >
            <span class="mt-0.5 font-mono text-[11px] tracking-[0.25em] text-slate-900" x-text="{{ $numeroJs }}"></span>
        </div>

        @if ($vistaPrevia)
            <span class="text-[11px] text-[var(--theme-text-muted)]">
                Vista previa: se asigna al guardar el equipo.
            </span>
        @endif
    </div>
@elseif ($numero !== null)
    {{-- Fijo --}}
    <div {{ $attributes->class(['flex flex-col items-start gap-1.5']) }}>
        <div class="inline-flex max-w-full flex-col items-center overflow-x-auto rounded-md border border-[var(--theme-border)] bg-white px-3 pb-1 pt-2">
            <img
                src="{{ route('equipos.codigo-barras', ['numero' => $numero, 'alto' => $alto]) }}"
                alt="Código de barras {{ $numero }}"
                class="{{ $claseImagen }}"
                decoding="async"
            >
            <span class="mt-0.5 font-mono text-[11px] tracking-[0.25em] text-slate-900">{{ $numero }}</span>
        </div>

        @if ($descargable)
            <a
                href="{{ route('equipos.codigo-barras', ['numero' => $numero, 'descargar' => 1]) }}"
                download
                class="inline-flex items-center gap-1 text-[11px] font-medium text-[var(--theme-primary)] hover:text-[var(--theme-primary-hover)]"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-4515817') }}"></use></svg>
                Descargar código de barras
            </a>
        @endif
    </div>
@endif
