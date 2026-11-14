@php
    $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)]';
    $botonPrimario = 'relative inline-flex h-9 items-center justify-center rounded-md bg-[var(--theme-primary)] px-4 text-xs font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-wait disabled:opacity-60';
    $botonPeligro = 'relative inline-flex h-9 items-center justify-center rounded-md bg-[var(--theme-danger)] px-4 text-xs font-medium text-white transition-colors hover:opacity-90 disabled:cursor-wait disabled:opacity-60';
    $botonSecundario = 'inline-flex h-9 items-center justify-center rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]';
    $pasos = ['Contraseña', 'Confirmación', 'Confirmación final', 'Autenticador'];
@endphp

<section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">
    <div class="space-y-6 p-4 sm:p-6">

        {{-- Qué se elimina y qué se conserva --}}
        <div class="rounded-xl border border-[var(--theme-danger)] p-4 sm:p-5">
            <h3 class="text-base font-semibold text-[var(--theme-danger)]">Esta acción no se puede deshacer</h3>
            <p class="mt-2 text-sm text-[var(--theme-text-muted)]">
                Se eliminará la información de la organización y el sistema quedará como recién instalado.
                Se creará un usuario temporal con una contraseña aleatoria para configurarlo de nuevo.
            </p>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--theme-text-strong)]">Se elimina</p>
                    <ul class="mt-2 space-y-1 text-sm text-[var(--theme-text)]">
                        <li>{{ number_format($conteos['usuarios']) }} usuarios (incluida tu cuenta)</li>
                        <li>{{ number_format($conteos['sedes']) }} sedes</li>
                        <li>{{ number_format($conteos['unidades']) }} @plantilla('unidades') y sus ubicaciones</li>
                        <li>{{ number_format($conteos['departamentos']) }} departamentos</li>
                        <li>{{ number_format($conteos['equipos']) }} equipos con sus asignaciones, mantenimientos y bajas</li>
                        <li>Bitácora, notificaciones y sesiones abiertas</li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--theme-text-strong)]">Se conserva</p>
                    <ul class="mt-2 space-y-1 text-sm text-[var(--theme-text)]">
                        <li>Marcas y modelos</li>
                        <li>Tipos de equipo y sus formularios</li>
                        <li>Programas y proveedores</li>
                        <li>Tipos de ubicación y catálogos</li>
                        <li>Roles, permisos e identidad visual</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Progreso --}}
        <ol class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach ($pasos as $i => $nombre)
                <li @class([
                    'rounded-md border px-3 py-2 text-xs',
                    'border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] font-semibold text-[var(--theme-text-strong)]' => $paso === $i + 1,
                    'border-[var(--theme-border)] text-[var(--theme-text-muted)]' => $paso !== $i + 1,
                ])>
                    {{ $i + 1 }}. {{ $nombre }}
                </li>
            @endforeach
        </ol>

        @if (! $tieneDosPasos)
            <div class="rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3 text-sm text-[var(--theme-text)]">
                Para restablecer necesitas tener activa la verificación en dos pasos.
                <a href="{{ route('ajustes.index', ['section' => 'seguridad']) }}" class="text-[var(--theme-primary)] hover:underline">Actívala en Ajustes</a>.
            </div>
        @endif

        {{-- Paso 1: contraseña --}}
        @if ($paso === 1)
            <form wire:submit="verificarPassword" class="max-w-md space-y-3">
                <label for="restablecer-password" class="block text-sm font-medium text-[var(--theme-text-strong)]">Escribe tu contraseña</label>
                <input id="restablecer-password" type="password" wire:model="password" autocomplete="current-password" class="{{ $campo }}">
                @error('password') <p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" wire:loading.attr="disabled" wire:target="verificarPassword" class="{{ $botonPrimario }}">
                        <span wire:loading.class="invisible" wire:target="verificarPassword">Continuar</span>
                        <span wire:loading.flex wire:target="verificarPassword" class="absolute inset-0 items-center justify-center">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-ef15586') }}"></use></svg>
                        </span>
                    </button>
                    <a href="{{ route('catalogos.index', ['section' => 'plantillas']) }}" class="{{ $botonSecundario }}">Cancelar</a>
                </div>
            </form>
        @endif

        {{-- Paso 2: primera confirmación --}}
        @if ($paso === 2)
            <form wire:submit="confirmar1" class="max-w-xl space-y-3">
                <label class="flex items-start gap-3 text-sm text-[var(--theme-text)]">
                    <input type="checkbox" wire:model="entiendo" class="mt-0.5 h-4 w-4 rounded border-[var(--theme-border-strong)]">
                    <span>Entiendo que se eliminarán todos los usuarios, sedes, @plantilla('unidades'), departamentos y el inventario, y que no se puede deshacer.</span>
                </label>
                @error('entiendo') <p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="{{ $botonPrimario }}">Continuar</button>
                    <a href="{{ route('catalogos.index', ['section' => 'plantillas']) }}" class="{{ $botonSecundario }}">Cancelar</a>
                </div>
            </form>
        @endif

        {{-- Paso 3: segunda confirmación --}}
        @if ($paso === 3)
            <form wire:submit="confirmar2" class="max-w-md space-y-3">
                <label for="restablecer-frase" class="block text-sm font-medium text-[var(--theme-text-strong)]">
                    Para confirmar, escribe <span class="font-mono">{{ \App\Livewire\RestablecerInstancia::FRASE }}</span>
                </label>
                <input id="restablecer-frase" type="text" wire:model="frase" autocomplete="off" class="{{ $campo }} font-mono uppercase">
                @error('frase') <p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="{{ $botonPrimario }}">Continuar</button>
                    <a href="{{ route('catalogos.index', ['section' => 'plantillas']) }}" class="{{ $botonSecundario }}">Cancelar</a>
                </div>
            </form>
        @endif

        {{-- Paso 4: código del autenticador --}}
        @if ($paso === 4)
            <form wire:submit="restablecer" class="max-w-md space-y-3">
                <label for="restablecer-codigo" class="block text-sm font-medium text-[var(--theme-text-strong)]">Código de tu aplicación autenticadora</label>
                <input id="restablecer-codigo" type="text" inputmode="numeric" maxlength="6" wire:model="codigo" autocomplete="one-time-code" class="{{ $campo }} font-mono tracking-widest">
                @error('codigo') <p class="text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" wire:loading.attr="disabled" wire:target="restablecer" class="{{ $botonPeligro }}">
                        <span wire:loading.class="invisible" wire:target="restablecer">Restablecer instancia</span>
                        <span wire:loading.flex wire:target="restablecer" class="absolute inset-0 items-center justify-center">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-ef15586') }}"></use></svg>
                            <span class="sr-only">Restableciendo...</span>
                        </span>
                    </button>
                    <a href="{{ route('catalogos.index', ['section' => 'plantillas']) }}" class="{{ $botonSecundario }}">Cancelar</a>
                </div>
            </form>
        @endif

    </div>
</section>
