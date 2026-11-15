@extends('layouts.guest')

@section('title', 'Configurar instancia')
@section('page-label', 'CONFIGURACIÓN INICIAL')

@php
    $campo = 'w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)] focus:border-[var(--theme-primary)] transition-colors';
    $etiqueta = 'mb-1 block text-xs font-medium text-[var(--theme-text)]';
    $error = 'mt-1 text-xs text-[var(--theme-danger)]';
    $etiquetas = collect($plantillas)->map(fn ($p) => ['unidad' => $p['etiquetas']['unidad'], 'unidades' => $p['etiquetas']['unidades']]);
    $descripciones = [
        'hoteles' => 'Hoteles con sus departamentos y ubicaciones.',
        'escuelas' => 'Escuelas o instituciones con edificios, departamentos y ubicaciones.',
    ];
    $elegida = (string) old('plantilla', $plantillaForzada ?? $plantillaActual);
    $unidad = $etiquetas[$elegida]['unidad'] ?? \App\Services\PlantillaInstalacionService::t('unidad');
    $unidades = $etiquetas[$elegida]['unidades'] ?? \App\Services\PlantillaInstalacionService::t('unidades');

    /*
     * Un paso por pantalla, con pocos campos cada uno.
     * La plantilla va primero porque cambia los textos de los pasos siguientes.
     */
    $pasos = array_values(array_filter([
        $plantillaForzada ? null : ['clave' => 'plantilla', 'titulo' => 'Tipo de organización', 'campos' => ['plantilla']],
        ['clave' => 'organizacion', 'titulo' => 'Tu organización', 'campos' => ['organizacion']],
        ['clave' => 'color', 'titulo' => 'Color principal', 'campos' => ['color']],
        ['clave' => 'sede', 'titulo' => 'Primera sede', 'campos' => ['sede']],
        ['clave' => 'logo', 'titulo' => 'Logotipo', 'campos' => ['logo']],
        // Solo aparece si se eligió un logotipo (data-requiere-logo); va justo después del logotipo.
        ['clave' => 'logo_oscuro', 'titulo' => 'Logotipo en modo oscuro', 'campos' => ['logo_oscuro', 'logo_modo_oscuro']],
        ['clave' => 'unidad', 'titulo' => 'Primer :unidad', 'campos' => ['unidad']],
        ['clave' => 'departamento', 'titulo' => 'Primer departamento', 'campos' => ['departamento']],
        ['clave' => 'nombre', 'titulo' => 'Tus datos', 'campos' => ['nombres', 'apellido_paterno', 'apellido_materno']],
        ['clave' => 'cuenta', 'titulo' => 'Tu cuenta', 'campos' => ['username', 'correo']],
        ['clave' => 'password', 'titulo' => 'Tu contraseña', 'campos' => ['password', 'password_confirmation']],
    ]));

    // Si el servidor devolvió errores, se abre el primer paso que tenga uno.
    // Un error en el logotipo oscuro se muestra en el paso del logotipo
    // (los archivos no se conservan tras recargar y ese paso depende de él).
    $pasoInicial = 0;
    $pasoConError = null;
    foreach ($pasos as $i => $p) {
        if (collect($p['campos'])->contains(fn ($c) => $errors->has($c))) {
            $pasoInicial = $p['clave'] === 'logo_oscuro' ? $i - 1 : $i;
            $pasoConError = $pasos[$pasoInicial]['titulo'];
            break;
        }
    }

    $paleta = \App\Services\OrganizacionService::PALETA_COLORES;
    $colorElegido = strtoupper((string) old('color', $colorActual));
    if (! array_key_exists($colorElegido, $paleta)) {
        $colorElegido = \App\Services\OrganizacionService::COLOR_PRIMARIO_PREDETERMINADO;
    }
    $modoOscuroElegido = (string) old('logo_modo_oscuro', 'original');
@endphp

@section('content')

{{-- Usuario temporal tras restablecer (App\Http\Controllers\InstanciaController).
    El layout de invitado no carga Alpine: los pasos usan JavaScript simple. --}}
<x-auth-card heading="Configura la instancia" icon="user-plus">

    <form
        id="asistente-instancia"
        method="POST"
        action="{{ route('instancia.configurar.guardar') }}"
        enctype="multipart/form-data"
        novalidate
        data-etiquetas="{{ json_encode($etiquetas) }}"
        data-paso-inicial="{{ $pasoInicial }}"
    >
        @csrf

        {{-- Progreso --}}
        <div class="mb-6">
            <div class="mb-2 flex items-center justify-between text-xs text-[var(--theme-text-muted)]">
                <span>Paso <span data-paso-actual>{{ $pasoInicial + 1 }}</span> de <span data-paso-total>{{ count($pasos) - 1 }}</span></span>
                <span data-paso-titulo></span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                <div data-barra class="h-full rounded-full bg-[var(--theme-primary)] transition-all duration-300" style="width: 0"></div>
            </div>
        </div>

        {{-- Si el servidor rechazó algo, se dice claramente que aún no se guardó nada. --}}
        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-md border border-[var(--theme-danger)] bg-[color-mix(in_srgb,var(--theme-danger)_8%,transparent)] px-4 py-3 text-xs text-[var(--theme-text)]">
                <p class="font-semibold text-[var(--theme-danger)]">Aún no se guardó la configuración.</p>
                <p class="mt-1">
                    Corrige el paso «{{ str_replace(':unidad', $unidad, $pasoConError ?? 'marcado') }}» y vuelve a terminar.
                    Por seguridad, las contraseñas y las imágenes deben elegirse de nuevo.
                </p>
            </div>
        @endif

        @foreach ($pasos as $i => $paso)
            <section
                data-paso="{{ $i }}"
                data-titulo="{{ $paso['titulo'] }}"
                @if ($paso['clave'] === 'logo_oscuro') data-requiere-logo @endif
                @class(['space-y-4', 'hidden' => $i !== $pasoInicial])
            >
                @switch($paso['clave'])

                    @case('plantilla')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">¿Qué tipo de organización administrarás?</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Define cómo se nombran las unidades en todo el sistema.</p>
                        </div>
                        <div class="space-y-3">
                            @foreach ($plantillas as $clave => $p)
                                <label class="flex cursor-pointer items-start gap-3 rounded-md border border-[var(--theme-border-strong)] px-4 py-3 transition-colors hover:bg-[var(--theme-surface-soft)] has-[:checked]:border-[var(--theme-primary)] has-[:checked]:bg-[var(--theme-primary-soft)]">
                                    <input type="radio" name="plantilla" value="{{ $clave }}" @checked($elegida === $clave) required class="mt-1">
                                    <span>
                                        <span class="block text-sm font-medium text-[var(--theme-text-strong)]">{{ $p['nombre'] }}</span>
                                        <span class="block text-xs text-[var(--theme-text-muted)]">{{ $descripciones[$clave] ?? $p['etiquetas']['unidad_plural'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('plantilla') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        @break

                    @case('organizacion')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">¿Cómo se llama tu organización?</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Aparece en los reportes y como texto del logotipo. Puedes cambiarlo después.</p>
                        </div>
                        <div>
                            <label for="organizacion" class="{{ $etiqueta }}">Nombre de la organización</label>
                            <input id="organizacion" type="text" name="organizacion" value="{{ old('organizacion') }}" maxlength="150" required class="{{ $campo }}">
                            @error('organizacion') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        @break

                    @case('logo')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Sube el logotipo</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Se muestra en el menú, el inicio de sesión y los reportes. PNG, JPG o WEBP de hasta 1 MB; mejor PNG con fondo transparente.</p>
                        </div>
                        <label for="logo" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-md border border-dashed border-[var(--theme-border-strong)] px-4 py-6 text-center transition-colors hover:bg-[var(--theme-surface-soft)]">
                            <img data-logo-vista alt="Vista previa del logotipo" class="hidden max-h-20 max-w-[220px] object-contain">
                            <span data-logo-texto class="text-sm text-[var(--theme-text)]">Elegir imagen…</span>
                            <span data-logo-ayuda class="text-xs text-[var(--theme-text-muted)]">Opcional: si lo omites se usa el logotipo de Track IT.</span>
                        </label>
                        <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="sr-only">
                        <p data-logo-error class="{{ $error }} hidden"></p>
                        <div data-logo-quitar class="hidden text-center">
                            <button type="button" class="rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 py-2 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">
                                Quitar imagen
                            </button>
                        </div>
                        <p class="text-xs text-[var(--theme-text-muted)]">Este logotipo también se usa en todos los documentos y reportes.</p>
                        @error('logo') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        @error('logo_oscuro') <p class="{{ $error }}">Logotipo para modo oscuro: {{ $message }}</p> @enderror
                        @break

                    @case('logo_oscuro')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">¿Cómo se verá en modo oscuro?</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Si tu logotipo no se ve bien sobre fondo oscuro, sube una versión clara o elige una apariencia. Puedes cambiarlo después en Organización.</p>
                        </div>

                        <label for="logo_oscuro" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-md border border-dashed border-[var(--theme-border-strong)] bg-[#111416] px-4 py-5 text-center transition-opacity hover:opacity-90">
                            <img data-oscuro-vista alt="Vista previa en modo oscuro" class="max-h-16 max-w-[200px] object-contain">
                            <span data-oscuro-texto class="text-sm text-[#F1F3F4]">Subir versión para modo oscuro (opcional)…</span>
                        </label>
                        <input id="logo_oscuro" type="file" name="logo_oscuro" accept="image/png,image/jpeg,image/webp" class="sr-only">
                        <p data-oscuro-error class="{{ $error }} hidden"></p>
                        <div data-oscuro-quitar class="hidden text-center">
                            <button type="button" class="rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 py-2 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">
                                Quitar imagen
                            </button>
                        </div>

                        <div data-modos-oscuro class="space-y-2">
                            <p class="{{ $etiqueta }}">O elige cómo mostrar el logotipo principal</p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach (\App\Services\OrganizacionService::MODOS_OSCURO as $claveModo => $modo)
                                    <label class="flex cursor-pointer items-start gap-2 rounded-md border border-[var(--theme-border-strong)] px-3 py-2 transition-colors hover:bg-[var(--theme-surface-soft)] has-[:checked]:border-[var(--theme-primary)] has-[:checked]:bg-[var(--theme-primary-soft)]">
                                        <input type="radio" name="logo_modo_oscuro" value="{{ $claveModo }}" @checked($modoOscuroElegido === $claveModo) class="mt-0.5 accent-[var(--theme-primary)]">
                                        <span>
                                            <span class="block text-xs font-medium text-[var(--theme-text-strong)]">{{ $modo['etiqueta'] }}</span>
                                            <span class="block text-[11px] leading-snug text-[var(--theme-text-muted)]">{{ $modo['descripcion'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('logo_modo_oscuro') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        @break

                    @case('color')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Elige el color principal</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Se usa en botones, enlaces y elementos destacados de todo el sistema. Puedes cambiarlo después.</p>
                        </div>
                        <div class="grid grid-cols-5 gap-3" role="radiogroup" aria-label="Color principal">
                            @foreach ($paleta as $valorColor => $nombreColor)
                                <label class="group flex cursor-pointer flex-col items-center gap-1.5" title="{{ $nombreColor }}">
                                    <input type="radio" name="color" value="{{ $valorColor }}" @checked($colorElegido === $valorColor) required class="peer sr-only">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--theme-border-strong)] ring-offset-2 ring-offset-[var(--theme-surface)] transition-transform group-hover:scale-110 peer-checked:ring-2 peer-checked:ring-[var(--theme-text-strong)] peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--theme-primary)]" style="background-color: {{ $valorColor }}">
                                        <svg class="hidden h-4 w-4 text-white group-has-[:checked]:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L20 7"/></svg>
                                    </span>
                                    <span class="text-center text-[10px] leading-tight text-[var(--theme-text-muted)]">{{ str_replace(' (predeterminado)', '', $nombreColor) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="flex items-center justify-between rounded-md border border-[var(--theme-border)] px-4 py-3">
                            <span class="text-xs text-[var(--theme-text-muted)]">Vista previa</span>
                            <span data-color-muestra class="rounded-md px-4 py-2 text-xs font-medium text-white" style="background: {{ $colorElegido }}">Botón de ejemplo</span>
                        </div>
                        @error('color') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        @break

                    @case('sede')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Agrega la primera sede</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Una sede agrupa <span data-unidades>{{ $unidades }}</span>, por ejemplo por ciudad o campus.</p>
                        </div>
                        <div>
                            <label for="sede" class="{{ $etiqueta }}">Nombre de la sede</label>
                            <input id="sede" type="text" name="sede" value="{{ old('sede') }}" placeholder="p. ej. Campus principal" maxlength="120" required class="{{ $campo }}">
                            @error('sede') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        @break

                    @case('unidad')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Agrega el primer <span data-unidad>{{ $unidad }}</span></h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Pertenece a la sede que acabas de crear.</p>
                        </div>
                        <div>
                            <label for="unidad" class="{{ $etiqueta }}">Nombre del <span data-unidad>{{ $unidad }}</span></label>
                            <input id="unidad" type="text" name="unidad" value="{{ old('unidad') }}" maxlength="150" required class="{{ $campo }}">
                            @error('unidad') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        @break

                    @case('departamento')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Agrega el primer departamento</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Quedará vinculado al <span data-unidad>{{ $unidad }}</span>. Tu cuenta pertenecerá a este departamento.</p>
                        </div>
                        <div>
                            <label for="departamento" class="{{ $etiqueta }}">Nombre del departamento</label>
                            <input id="departamento" type="text" name="departamento" value="{{ old('departamento') }}" placeholder="p. ej. Sistemas" maxlength="150" required class="{{ $campo }}">
                            @error('departamento') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        @break

                    @case('nombre')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Ahora, tus datos</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Serás el administrador del sistema.</p>
                        </div>
                        <div>
                            <label for="nombres" class="{{ $etiqueta }}">Nombre(s)</label>
                            <input id="nombres" type="text" name="nombres" value="{{ old('nombres') }}" maxlength="100" required autocomplete="given-name" class="{{ $campo }}">
                            @error('nombres') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="apellido_paterno" class="{{ $etiqueta }}">Apellido paterno</label>
                                <input id="apellido_paterno" type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" maxlength="100" required class="{{ $campo }}">
                                @error('apellido_paterno') <p class="{{ $error }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="apellido_materno" class="{{ $etiqueta }}">Apellido materno <span class="text-[var(--theme-text-muted)]">(opcional)</span></label>
                                <input id="apellido_materno" type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" maxlength="100" class="{{ $campo }}">
                                @error('apellido_materno') <p class="{{ $error }}">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @break

                    @case('cuenta')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Crea tu cuenta</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Con este usuario iniciarás sesión.</p>
                        </div>
                        <div>
                            <label for="username" class="{{ $etiqueta }}">Usuario</label>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="p. ej. roberto.novelo" maxlength="50" minlength="3" pattern="[A-Za-z0-9._\-]+" required autocomplete="username" class="{{ $campo }}">
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Letras, números, puntos, guiones y guiones bajos, sin espacios.</p>
                            @error('username') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="correo" class="{{ $etiqueta }}">Correo</label>
                            <input id="correo" type="email" name="correo" value="{{ old('correo') }}" maxlength="150" required autocomplete="email" class="{{ $campo }}">
                            @error('correo') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        @break

                    @case('password')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Elige una contraseña</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                Al menos 10 caracteres, con mayúscula, minúscula, número y carácter especial, sin espacios.
                            </p>
                        </div>
                        <div>
                            <label for="password" class="{{ $etiqueta }}">Contraseña</label>
                            <input id="password" type="password" name="password" minlength="10" required autocomplete="new-password" class="{{ $campo }}">
                            @error('password') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="{{ $etiqueta }}">Confirmar contraseña</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" minlength="10" required autocomplete="new-password" class="{{ $campo }}">
                            <p data-error-confirmacion class="{{ $error }} hidden">Las contraseñas no coinciden.</p>
                        </div>
                        <p class="text-xs text-[var(--theme-text-muted)]">Al terminar, inicia sesión con tu cuenta; se te pedirá activar la verificación en dos pasos.</p>
                        @break

                @endswitch
            </section>
        @endforeach

        {{-- Navegación --}}
        <div class="mt-6 flex items-center justify-between gap-3">
            <button type="button" data-atras class="rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 py-2.5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">
                Atrás
            </button>

            <button type="button" data-siguiente class="flex-1 rounded-md bg-[var(--theme-primary)] py-2.5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] sm:flex-none sm:px-8">
                Siguiente
            </button>

            <button type="submit" data-terminar class="hidden flex-1 rounded-md bg-[var(--theme-primary)] py-2.5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-wait disabled:opacity-70 sm:flex-none sm:px-8">
                Guardar y terminar
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="pt-5 text-center">
        @csrf
        <button type="submit" class="text-xs text-[var(--theme-primary)] transition-colors hover:text-[var(--theme-primary-hover)] hover:underline">
            Cerrar sesión
        </button>
    </form>

</x-auth-card>

<script>
    (() => {
        const form = document.getElementById('asistente-instancia');
        if (!form) return;

        const pasos = [...form.querySelectorAll('[data-paso]')];
        const etiquetas = JSON.parse(form.dataset.etiquetas || '{}');
        const atras = form.querySelector('[data-atras]');
        const siguiente = form.querySelector('[data-siguiente]');
        const terminar = form.querySelector('[data-terminar]');
        const logo = form.querySelector('#logo');
        const logoOscuro = form.querySelector('#logo_oscuro');
        let actual = Number(form.dataset.pasoInicial || 0);

        const LIMITE_BYTES = 1024 * 1024;
        const TIPOS = ['image/png', 'image/jpeg', 'image/webp'];

        const elegida = () => {
            const radio = form.querySelector('input[name="plantilla"]:checked');
            return radio ? etiquetas[radio.value] : null;
        };

        const unidadElegida = () => elegida()?.unidad || form.querySelector('[data-unidad]')?.textContent || 'unidad';

        const actualizarTextos = () => {
            const et = elegida();
            if (!et) return;
            form.querySelectorAll('[data-unidad]').forEach((el) => { el.textContent = et.unidad; });
            form.querySelectorAll('[data-unidades]').forEach((el) => { el.textContent = et.unidades; });
        };

        const hayLogo = () => Boolean(logo?.files?.length);
        const hayLogoOscuro = () => Boolean(logoOscuro?.files?.length);

        // El paso de modo oscuro solo cuenta si se eligió un logotipo.
        const activo = (n) => !(pasos[n].hasAttribute('data-requiere-logo') && !hayLogo());
        const activos = () => pasos.map((_, n) => n).filter(activo);
        const siguienteActivo = (desde, dir) => {
            for (let n = desde + dir; n >= 0 && n < pasos.length; n += dir) {
                if (activo(n)) return n;
            }
            return null;
        };

        // Texto del botón: «Omitir» cuando el paso del logotipo queda vacío.
        const actualizarBotonSiguiente = () => {
            const enLogo = pasos[actual].contains(logo);
            siguiente.textContent = enLogo && !hayLogo() ? 'Omitir' : 'Siguiente';
        };

        const mostrar = (i) => {
            actual = i;
            pasos.forEach((p, n) => p.classList.toggle('hidden', n !== i));

            const lista = activos();
            const posicion = lista.indexOf(i);
            const ultimo = siguienteActivo(i, 1) === null;

            atras.classList.toggle('invisible', siguienteActivo(i, -1) === null);
            siguiente.classList.toggle('hidden', ultimo);
            terminar.classList.toggle('hidden', !ultimo);
            actualizarBotonSiguiente();

            form.querySelector('[data-paso-actual]').textContent = posicion + 1;
            form.querySelector('[data-paso-total]').textContent = lista.length;
            form.querySelector('[data-paso-titulo]').textContent = pasos[i].dataset.titulo.replace(':unidad', unidadElegida());
            form.querySelector('[data-barra]').style.width = ((posicion + 1) / lista.length * 100) + '%';

            if (pasos[i].contains(form.querySelector('[data-oscuro-vista]'))) actualizarVistaOscura();

            pasos[i].querySelector('input:not([type=radio]):not([type=file]), input[type=radio]:checked, input[type=radio]')?.focus();
        };

        // Valida solo los campos del paso visible (los obligatorios del navegador).
        const pasoValido = () => {
            const campos = [...pasos[actual].querySelectorAll('input')];

            for (const campo of campos) {
                // Quitar espacios sobrantes solo en campos de texto (el de archivo no admite asignar valor).
                if (['text', 'email'].includes(campo.type)) {
                    campo.value = campo.value.trim();
                }

                if (!campo.checkValidity()) {
                    campo.reportValidity();
                    return false;
                }
            }

            const pass = form.querySelector('#password');
            const conf = form.querySelector('#password_confirmation');
            const avisoConf = form.querySelector('[data-error-confirmacion]');

            if (pasos[actual].contains(conf) && pass.value !== conf.value) {
                avisoConf.classList.remove('hidden');
                conf.focus();
                return false;
            }

            avisoConf?.classList.add('hidden');

            return true;
        };

        siguiente.addEventListener('click', () => {
            const destino = siguienteActivo(actual, 1);
            if (destino !== null && pasoValido()) mostrar(destino);
        });

        atras.addEventListener('click', () => {
            const destino = siguienteActivo(actual, -1);
            if (destino !== null) mostrar(destino);
        });

        // Enter avanza al siguiente paso en lugar de enviar el formulario.
        form.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && siguienteActivo(actual, 1) !== null) {
                e.preventDefault();
                siguiente.click();
            }
        });

        form.addEventListener('submit', (e) => {
            if (!pasoValido()) {
                e.preventDefault();
                return;
            }

            terminar.disabled = true;
            terminar.textContent = 'Guardando…';
        });

        form.querySelectorAll('input[name="plantilla"]').forEach((r) => r.addEventListener('change', actualizarTextos));

        /*
         * Imágenes: se revisan aquí las mismas reglas que el servidor
         * (tipo, 1 MB, 40×20 a 4000×4000 px) para no perder lo capturado
         * al final por un archivo que el servidor rechazaría.
         */
        const revisarImagen = (archivo) => new Promise((resolver) => {
            if (!TIPOS.includes(archivo.type)) return resolver('Utiliza una imagen PNG, JPG o WEBP.');
            if (archivo.size > LIMITE_BYTES) return resolver('La imagen supera 1 MB. Elige una más ligera.');

            const img = new Image();
            const url = URL.createObjectURL(archivo);
            img.onload = () => {
                const { naturalWidth: w, naturalHeight: h } = img;
                URL.revokeObjectURL(url);
                resolver(w < 40 || h < 20 || w > 4000 || h > 4000
                    ? `La imagen mide ${w}×${h} px; debe medir entre 40×20 y 4000×4000 píxeles.`
                    : null);
            };
            img.onerror = () => { URL.revokeObjectURL(url); resolver('No se pudo leer la imagen. Elige otra.'); };
            img.src = url;
        });

        const urls = {};
        const urlDe = (input) => {
            const archivo = input?.files?.[0];
            if (!archivo) return null;
            if (urls[input.id]?.archivo !== archivo) {
                if (urls[input.id]) URL.revokeObjectURL(urls[input.id].url);
                urls[input.id] = { archivo, url: URL.createObjectURL(archivo) };
            }
            return urls[input.id].url;
        };

        // Selector de imagen con vista previa, mensaje de error y botón «Quitar imagen».
        const prepararSelector = (input, prefijo, textoVacio, alCambiar) => {
            if (!input) return;
            const vista = form.querySelector(`[data-${prefijo}-vista]`);
            const texto = form.querySelector(`[data-${prefijo}-texto]`);
            const error = form.querySelector(`[data-${prefijo}-error]`);
            const quitar = form.querySelector(`[data-${prefijo}-quitar]`);

            const pintar = () => {
                const url = urlDe(input);
                if (prefijo === 'logo') {
                    vista.classList.toggle('hidden', !url);
                    if (url) vista.src = url;
                }
                texto.textContent = url ? input.files[0].name + ' · cambiar' : textoVacio;
                quitar.classList.toggle('hidden', !url);
                alCambiar?.();
            };

            input.addEventListener('change', async () => {
                error.classList.add('hidden');
                const archivo = input.files?.[0];

                if (archivo) {
                    const problema = await revisarImagen(archivo);
                    if (problema) {
                        input.value = '';
                        error.textContent = problema;
                        error.classList.remove('hidden');
                    }
                }

                pintar();
            });

            quitar.querySelector('button').addEventListener('click', () => {
                input.value = '';
                error.classList.add('hidden');
                pintar();
            });

            input._pintar = pintar;
        };

        // Vista previa en modo oscuro: la versión subida o el logotipo principal con la apariencia elegida.
        const actualizarVistaOscura = () => {
            const vista = form.querySelector('[data-oscuro-vista]');
            if (!vista) return;

            const propia = urlDe(logoOscuro);
            const modo = form.querySelector('input[name="logo_modo_oscuro"]:checked')?.value || 'original';

            vista.src = propia || urlDe(logo) || '';
            vista.classList.toggle('org-logo-preview-dark', !propia);
            if (propia) vista.removeAttribute('data-dark-mode'); else vista.dataset.darkMode = modo;
            vista.style.setProperty('--org-logo-bg', '#FFFFFF');

            form.querySelector('[data-modos-oscuro]')?.classList.toggle('hidden', Boolean(propia));
        };

        prepararSelector(logo, 'logo', 'Elegir imagen…', () => {
            // Sin logotipo principal no hay versión oscura.
            if (!hayLogo() && hayLogoOscuro()) {
                logoOscuro.value = '';
                logoOscuro._pintar?.();
            }
            actualizarBotonSiguiente();
            mostrar(actual);
        });

        prepararSelector(logoOscuro, 'oscuro', 'Subir versión para modo oscuro (opcional)…', actualizarVistaOscura);

        form.querySelectorAll('input[name="logo_modo_oscuro"]').forEach((r) => r.addEventListener('change', actualizarVistaOscura));

        // Color: botón de ejemplo con el color elegido.
        form.querySelectorAll('input[name="color"]').forEach((r) => r.addEventListener('change', () => {
            form.querySelector('[data-color-muestra]').style.background = r.value;
        }));

        actualizarTextos();
        mostrar(activo(actual) ? actual : (siguienteActivo(actual, -1) ?? 0));
    })();
</script>

@endsection
