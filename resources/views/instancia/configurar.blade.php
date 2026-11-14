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
        ['clave' => 'logo', 'titulo' => 'Logotipo', 'campos' => ['logo']],
        ['clave' => 'color', 'titulo' => 'Color principal', 'campos' => ['color']],
        ['clave' => 'sede', 'titulo' => 'Primera sede', 'campos' => ['sede']],
        ['clave' => 'unidad', 'titulo' => 'Primer :unidad', 'campos' => ['unidad']],
        ['clave' => 'departamento', 'titulo' => 'Primer departamento', 'campos' => ['departamento']],
        ['clave' => 'nombre', 'titulo' => 'Tus datos', 'campos' => ['nombres', 'apellido_paterno', 'apellido_materno']],
        ['clave' => 'cuenta', 'titulo' => 'Tu cuenta', 'campos' => ['username', 'correo']],
        ['clave' => 'password', 'titulo' => 'Tu contraseña', 'campos' => ['password', 'password_confirmation']],
    ]));

    // Si el servidor devolvió errores, se abre el primer paso que tenga uno.
    $pasoInicial = 0;
    foreach ($pasos as $i => $p) {
        if (collect($p['campos'])->contains(fn ($c) => $errors->has($c))) {
            $pasoInicial = $i;
            break;
        }
    }
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
                <span>Paso <span data-paso-actual>{{ $pasoInicial + 1 }}</span> de {{ count($pasos) }}</span>
                <span data-paso-titulo></span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                <div data-barra class="h-full rounded-full bg-[var(--theme-primary)] transition-all duration-300" style="width: 0"></div>
            </div>
        </div>

        @foreach ($pasos as $i => $paso)
            <section
                data-paso="{{ $i }}"
                data-titulo="{{ $paso['titulo'] }}"
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
                            <span class="text-xs text-[var(--theme-text-muted)]">Opcional: si lo omites se usa el logotipo de Track IT.</span>
                        </label>
                        <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="sr-only">
                        @error('logo') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        @if ($errors->any() && ! $errors->has('logo'))
                            <p class="text-xs text-[var(--theme-text-muted)]">Si habías elegido un logotipo, vuelve a seleccionarlo.</p>
                        @endif
                        @break

                    @case('color')
                        <div>
                            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">Elige el color principal</h2>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Se usa en botones, enlaces y elementos destacados de todo el sistema.</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <input id="color" type="color" name="color" value="{{ old('color', $colorActual) }}" required class="h-12 w-16 cursor-pointer rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-1">
                            <div>
                                <label for="color" class="{{ $etiqueta }}">Color</label>
                                <span data-color-hex class="font-mono text-sm text-[var(--theme-text-strong)]">{{ strtoupper(old('color', $colorActual)) }}</span>
                            </div>
                            <span data-color-muestra class="ml-auto rounded-md px-4 py-2 text-xs font-medium text-white" style="background: {{ old('color', $colorActual) }}">Botón de ejemplo</span>
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
        let actual = Number(form.dataset.pasoInicial || 0);

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

        const mostrar = (i) => {
            actual = i;
            pasos.forEach((p, n) => p.classList.toggle('hidden', n !== i));

            const ultimo = i === pasos.length - 1;
            atras.classList.toggle('invisible', i === 0);
            siguiente.classList.toggle('hidden', ultimo);
            terminar.classList.toggle('hidden', !ultimo);

            form.querySelector('[data-paso-actual]').textContent = i + 1;
            form.querySelector('[data-paso-titulo]').textContent = pasos[i].dataset.titulo.replace(':unidad', unidadElegida());
            form.querySelector('[data-barra]').style.width = ((i + 1) / pasos.length * 100) + '%';

            pasos[i].querySelector('input:not([type=radio]), input[type=radio]:checked, input[type=radio]')?.focus();
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
            if (pasoValido()) mostrar(actual + 1);
        });

        atras.addEventListener('click', () => {
            if (actual > 0) mostrar(actual - 1);
        });

        // Enter avanza al siguiente paso en lugar de enviar el formulario.
        form.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && actual < pasos.length - 1) {
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

        // Vista previa del logotipo elegido.
        const logo = form.querySelector('#logo');
        logo?.addEventListener('change', () => {
            const archivo = logo.files?.[0];
            const vista = form.querySelector('[data-logo-vista]');
            const texto = form.querySelector('[data-logo-texto]');

            if (!archivo) {
                vista.classList.add('hidden');
                texto.textContent = 'Elegir imagen…';
                return;
            }

            if (archivo.size > 1024 * 1024) {
                logo.value = '';
                vista.classList.add('hidden');
                texto.textContent = 'La imagen supera 1 MB. Elige otra.';
                return;
            }

            vista.src = URL.createObjectURL(archivo);
            vista.classList.remove('hidden');
            texto.textContent = archivo.name + ' · cambiar';
        });

        // Color: código y botón de ejemplo.
        const color = form.querySelector('#color');
        color?.addEventListener('input', () => {
            form.querySelector('[data-color-hex]').textContent = color.value.toUpperCase();
            form.querySelector('[data-color-muestra]').style.background = color.value;
        });

        actualizarTextos();
        mostrar(actual);
    })();
</script>

@endsection
