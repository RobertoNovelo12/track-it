{{-- ============================================================
    Administración > Plantillas > Restablecer instancia
    Un paso por pantalla, con barra de progreso arriba (mismo estilo
    que el asistente de configuración inicial).
============================================================ --}}
@php
    $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)]';
    $etiqueta = 'mb-1.5 block text-xs font-medium text-[var(--theme-text)]';
    $botonPrimario = 'relative inline-flex h-10 flex-1 items-center justify-center rounded-md bg-[var(--theme-primary)] px-6 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-wait disabled:opacity-60 sm:flex-none';
    $botonPeligro = 'relative inline-flex h-10 flex-1 items-center justify-center rounded-md bg-[var(--theme-danger)] px-6 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:cursor-wait disabled:opacity-60 sm:flex-none';
    $botonSecundario = 'inline-flex h-10 items-center justify-center rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]';
    $cancelar = route('catalogos.index', ['section' => 'plantillas']);

    $pasos = [
        1 => ['titulo' => 'Confirma tu identidad', 'texto' => 'Escribe tu contraseña para continuar.'],
        2 => ['titulo' => 'Revisa lo que se elimina', 'texto' => 'El sistema quedará como recién instalado. Esto no se puede deshacer.'],
        3 => ['titulo' => 'Confirmación final', 'texto' => 'Escribe la palabra indicada para confirmar.'],
        4 => ['titulo' => 'Código del autenticador', 'texto' => 'Escribe el código de 6 dígitos de tu aplicación autenticadora.'],
    ];

    $spinner = '<span class="absolute inset-0 items-center justify-center" wire:loading.flex wire:target="%s"><svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><use href="'.\App\Support\Icono::url('i-ef15586').'"></use></svg></span>';
@endphp

<div class="mx-auto w-full max-w-xl">
    <section class="rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-6 py-8 sm:px-10 sm:py-10">

        {{-- Encabezado --}}
        <div class="mb-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--theme-danger)_14%,transparent)] text-[var(--theme-danger)]">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7M4 4v4h4"/></svg>
            </span>
            <h2 class="mt-3 text-base font-semibold uppercase tracking-wide text-[var(--theme-text-strong)]">Restablecer instancia</h2>
        </div>

        {{-- Progreso (arriba, como el asistente de configuración inicial) --}}
        <div class="mb-7">
            <div class="mb-2 flex items-center justify-between text-xs text-[var(--theme-text-muted)]">
                <span>Paso {{ $paso }} de {{ count($pasos) }}</span>
                <span>{{ $pasos[$paso]['titulo'] }}</span>
            </div>
            <div class="flex gap-1.5" aria-hidden="true">
                @foreach ($pasos as $n => $p)
                    <span @class([
                        'h-1.5 flex-1 rounded-full transition-colors',
                        'bg-[var(--theme-danger)]' => $n <= $paso,
                        'bg-[var(--theme-surface-soft)]' => $n > $paso,
                    ])></span>
                @endforeach
            </div>
        </div>

        {{-- Título del paso --}}
        <div class="mb-5">
            <h3 class="text-base font-semibold text-[var(--theme-text-strong)]">{{ $pasos[$paso]['titulo'] }}</h3>
            <p class="mt-1 text-sm text-[var(--theme-text-muted)]">{{ $pasos[$paso]['texto'] }}</p>
        </div>

        @if (! $tieneDosPasos)
            <div class="mb-5 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-4 py-3 text-sm text-[var(--theme-text)]">
                Para restablecer necesitas tener activa la verificación en dos pasos.
                <a href="{{ route('ajustes.index', ['section' => 'seguridad']) }}" class="text-[var(--theme-primary)] hover:underline">Actívala en Ajustes</a>.
            </div>
        @endif

        {{-- Paso 1: contraseña --}}
        @if ($paso === 1)
            <form wire:submit="verificarPassword" class="space-y-5">
                <div>
                    <label for="restablecer-password" class="{{ $etiqueta }}">Contraseña</label>
                    <input id="restablecer-password" type="password" wire:model="password" autocomplete="current-password" placeholder="Tu contraseña actual" autofocus class="{{ $campo }}">
                    @error('password') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <a href="{{ $cancelar }}" class="{{ $botonSecundario }}">Cancelar</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="verificarPassword" class="{{ $botonPrimario }}">
                        <span wire:loading.class="invisible" wire:target="verificarPassword">Continuar</span>
                        {!! sprintf($spinner, 'verificarPassword') !!}
                    </button>
                </div>
            </form>
        @endif

        {{-- Paso 2: qué se elimina y qué se conserva + primera confirmación --}}
        @if ($paso === 2)
            <form wire:submit="confirmar1" class="space-y-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-md border border-[color-mix(in_srgb,var(--theme-danger)_40%,transparent)] bg-[color-mix(in_srgb,var(--theme-danger)_6%,transparent)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[var(--theme-danger)]">Se elimina</p>
                        <ul class="mt-2 space-y-1.5 text-sm text-[var(--theme-text)]">
                            <li>{{ number_format($conteos['usuarios']) }} usuarios, incluida tu cuenta</li>
                            <li>{{ number_format($conteos['sedes']) }} sedes</li>
                            <li>{{ number_format($conteos['unidades']) }} @plantilla('unidades') y sus ubicaciones</li>
                            <li>{{ number_format($conteos['departamentos']) }} departamentos</li>
                            <li>{{ number_format($conteos['equipos']) }} equipos y sus movimientos</li>
                            <li>Bitácora, notificaciones y sesiones</li>
                            <li>Logotipo y color principal</li>
                        </ul>
                    </div>
                    <div class="rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[var(--theme-text-strong)]">Se conserva</p>
                        <ul class="mt-2 space-y-1.5 text-sm text-[var(--theme-text)]">
                            <li>Marcas y modelos</li>
                            <li>Tipos de equipo y formularios</li>
                            <li>Programas y proveedores</li>
                            <li>Tipos de ubicación y catálogos</li>
                            <li>Roles y permisos</li>
                        </ul>
                    </div>
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-md border border-[var(--theme-border-strong)] px-4 py-3 text-sm text-[var(--theme-text)] has-[:checked]:border-[var(--theme-danger)]">
                    <input type="checkbox" wire:model="entiendo" class="mt-0.5 h-4 w-4 accent-[var(--theme-danger)]">
                    <span>Entiendo que esta información se eliminará y no se puede recuperar.</span>
                </label>
                @error('entiendo') <p class="-mt-3 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

                <div class="flex items-center justify-between gap-3 pt-1">
                    <a href="{{ $cancelar }}" class="{{ $botonSecundario }}">Cancelar</a>
                    <button type="submit" class="{{ $botonPrimario }}">Continuar</button>
                </div>
            </form>
        @endif

        {{-- Paso 3: escribir RESTABLECER --}}
        @if ($paso === 3)
            <form wire:submit="confirmar2" class="space-y-5">
                <div>
                    <label for="restablecer-frase" class="{{ $etiqueta }}">
                        Escribe <span class="font-mono font-semibold text-[var(--theme-danger)]">{{ \App\Livewire\RestablecerInstancia::FRASE }}</span>
                    </label>
                    <input id="restablecer-frase" type="text" wire:model="frase" autocomplete="off" autofocus placeholder="{{ \App\Livewire\RestablecerInstancia::FRASE }}" class="{{ $campo }} font-mono uppercase">
                    @error('frase') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <a href="{{ $cancelar }}" class="{{ $botonSecundario }}">Cancelar</a>
                    <button type="submit" class="{{ $botonPrimario }}">Continuar</button>
                </div>
            </form>
        @endif

        {{-- Paso 4: código del autenticador --}}
        @if ($paso === 4)
            <form wire:submit="restablecer" class="space-y-5">
                <div>
                    <label for="restablecer-codigo" class="{{ $etiqueta }}">Código de verificación</label>
                    <input id="restablecer-codigo" type="text" inputmode="numeric" maxlength="6" wire:model="codigo" autocomplete="one-time-code" autofocus placeholder="000000" class="{{ $campo }} text-center font-mono text-lg tracking-[0.5em]">
                    @error('codigo') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>

                <p class="text-xs text-[var(--theme-text-muted)]">
                    Al restablecer se cerrará tu sesión y se mostrará una sola vez el usuario temporal con su contraseña.
                </p>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <a href="{{ $cancelar }}" class="{{ $botonSecundario }}">Cancelar</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="restablecer" class="{{ $botonPeligro }}">
                        <span wire:loading.class="invisible" wire:target="restablecer">Restablecer instancia</span>
                        {!! sprintf($spinner, 'restablecer') !!}
                    </button>
                </div>
            </form>
        @endif

    </section>
</div>
