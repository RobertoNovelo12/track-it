@extends('layouts.app')

@section('title', 'Centro de ayuda')

@php
    $soporte = app(\App\Services\OrganizacionService::class)->soporte();
    $esMac = str_contains(strtolower((string) request()->userAgent()), 'mac');
    $mod = $esMac ? '⌘' : 'Ctrl';

    /*
     * Guías rápidas por módulo: [título, módulo, pasos].
     */
    $guias = [
        ['Dar de alta un equipo', 'Inventario', [
            'Entra a Inventario y pulsa «Agregar equipo».',
            'Elige el tipo de equipo: el formulario muestra los campos de ese tipo.',
            'Llena los campos marcados con * (nombre, estado, marca, serie, factura y hotel).',
            'Guarda con el botón o con '.$mod.'+G. El código de inventario y el código de barras se generan solos.',
        ]],
        ['Asignar un equipo', 'Movimientos', [
            'Entra a Movimientos y abre «Nuevo movimiento» › Asignar.',
            'Busca el equipo por código, nombre o serie.',
            'Elige a la persona o el departamento que lo recibe.',
            'Guarda. El equipo queda como asignado y su historial registra el movimiento.',
        ]],
        ['Asignar varios equipos a la vez', 'Movimientos', [
            'En Inventario, marca los equipos con la casilla de cada fila.',
            'En la barra de selección pulsa «Asignar».',
            'Elige el destino (persona o departamento) y confirma.',
        ]],
        ['Reasignar o devolver', 'Movimientos', [
            'Abre «Nuevo movimiento» › Reasignar o Devolver.',
            'Busca el equipo asignado.',
            'Para reasignar, elige el nuevo responsable; para devolver, indica el motivo.',
            'Guarda: el cambio queda en el historial del equipo.',
        ]],
        ['Registrar un mantenimiento', 'Mantenimientos', [
            'Entra a Mantenimientos y pulsa «Nuevo mantenimiento».',
            'Elige el equipo y el tipo (preventivo o correctivo).',
            'Escribe la fecha, lo que se hizo y quién lo realizó, y guarda.',
        ]],
        ['Dar de baja y generar el acta', 'Bajas', [
            'Abre la ficha del equipo o selecciona varios en Inventario.',
            'Elige «Dar de baja» (uno, conjunta o por lote).',
            'Captura el motivo, el número de tiket y quién autoriza.',
            'Genera el acta en PDF para firmarla.',
        ]],
        ['Generar un reporte', 'Reportes', [
            'Entra a Reportes y elige el tipo de reporte en las pestañas.',
            'Aplica los filtros; la vista previa se actualiza sola.',
            'Pulsa «Generar reporte» y elige PDF, Excel o CSV.',
        ]],
        ['Usar el escáner desde el celular', 'Escáner', [
            'Abre Track IT en el celular y pulsa «Escanear» en el menú inferior.',
            'Permite el uso de la cámara la primera vez.',
            'Apunta al código de barras de la etiqueta: se abre la ficha del equipo.',
        ]],
        ['Restaurar desde la papelera', 'Papelera', [
            'Entra a Papelera (solo administradores).',
            'Busca el registro eliminado y pulsa «Restaurar».',
            'Para borrarlo para siempre, usa «Eliminar definitivamente»; esto no se puede deshacer.',
        ]],
    ];

    /*
     * Preguntas frecuentes: [pregunta, respuesta].
     */
    $preguntas = [
        ['¿Por qué no veo un equipo que acabo de registrar?', 'Las tablas se actualizan al guardar. Si no aparece, pulsa el botón de actualizar (↻) de la tabla y revisa que no haya filtros activos, como un estado o un hotel distinto.'],
        ['¿Cómo agrego un campo a un tipo de equipo?', 'En Administración › Equipos, edita el tipo y arrastra el campo desde «Campos disponibles» o crea uno nuevo. Márcalo como obligatorio u opcional y guarda la configuración.'],
        ['¿Qué pasa si elimino algo por error?', 'Los registros eliminados van a la Papelera, desde donde un administrador puede restaurarlos. Solo «Eliminar definitivamente» los borra para siempre.'],
        ['¿Por qué no puedo eliminar un proveedor, una marca o un usuario?', 'Porque tiene registros ligados (por ejemplo, equipos con ese proveedor o movimientos de ese usuario). El sistema te indica cuáles son; desactívalo en lugar de eliminarlo para conservar el historial.'],
        ['¿Cómo instalo la aplicación en el celular o la computadora?', 'Pulsa «Instalar aplicación» en el menú lateral. En iPhone, abre Track IT en Safari, pulsa Compartir y luego «Agregar a inicio».'],
        ['¿Cómo activo las notificaciones?', 'En Mi cuenta › Notificaciones activa «Notificaciones en este dispositivo» y acepta el permiso del navegador. Puedes elegir qué avisos recibir.'],
        ['¿Por qué la fecha o la hora se ven diferentes?', 'Las fechas se muestran en la zona horaria que elegiste en Mi cuenta › Preferencias. Revisa que sea la correcta.'],
        ['El sistema dice que el número de serie ya existe', 'Ya hay un equipo registrado con esa serie. Búscalo en Inventario antes de volver a darlo de alta; puede estar en otro hotel o dado de baja.'],
        ['Salió «Error del servidor al guardar»', 'No es por los datos que capturaste. Revisa si el equipo sí se guardó buscándolo por su serie y, si el problema sigue, envía un reporte desde esta página.'],
        ['¿Cómo cambio mi contraseña o activo la verificación en dos pasos?', 'En Mi cuenta › Seguridad puedes cambiar tu contraseña y activar la verificación en dos pasos.'],
    ];

    $atajos = [
        [$mod.'+K', 'Buscar equipos desde cualquier pantalla'],
        [$mod.'+B', 'Mostrar u ocultar el menú lateral'],
        [$mod.'+G', 'Guardar el formulario abierto (también '.$mod.'+S)'],
        ['Enter', 'En una lista con buscador, elegir la primera coincidencia'],
        ['Esc', 'Cerrar una lista o una ventana'],
    ];

    $tarjeta = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
@endphp

@section('content')
<div
    class="mx-auto w-full max-w-[1200px] space-y-5"
    x-data="{
        q: '',
        coincide(texto) {
            const q = this.q.trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            if (q === '') return true;
            return String(texto).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').includes(q);
        },
    }"
>
    {{-- ENCABEZADO + BUSCADOR --}}
    <section class="{{ $tarjeta }}">
        <h1 class="text-xl font-semibold text-[var(--theme-text-strong)]">Centro de ayuda</h1>
        <p class="mt-1 text-sm text-[var(--theme-text-muted)]">Guías rápidas, preguntas frecuentes y contacto con el equipo de soporte.</p>

        <div class="relative mt-4">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--theme-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input
                type="search"
                x-model="q"
                placeholder="Buscar en la ayuda… (por ejemplo: baja, escáner, notificaciones)"
                aria-label="Buscar en la ayuda"
                class="h-11 w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] pl-9 pr-3 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
            >
        </div>
    </section>

    {{-- GUÍAS RÁPIDAS --}}
    <section class="{{ $tarjeta }}" aria-labelledby="ayuda-guias">
        <h2 id="ayuda-guias" class="text-sm font-semibold text-[var(--theme-text-strong)]">Guías rápidas</h2>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($guias as [$titulo, $modulo, $pasos])
                <article
                    x-show="coincide(@js($titulo.' '.$modulo.' '.implode(' ', $pasos)))"
                    class="rounded-lg border border-[var(--theme-border)] p-4"
                >
                    <p class="text-[11px] font-medium uppercase tracking-wide text-[var(--theme-primary)]">{{ $modulo }}</p>
                    <h3 class="mt-1 text-sm font-semibold text-[var(--theme-text-strong)]">{{ $titulo }}</h3>
                    <ol class="mt-3 space-y-2">
                        @foreach ($pasos as $n => $paso)
                            <li class="flex gap-2 text-xs leading-relaxed text-[var(--theme-text)]">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[var(--theme-primary-soft)] text-[10px] font-semibold text-[var(--theme-primary)]">{{ $n + 1 }}</span>
                                <span>{{ $paso }}</span>
                            </li>
                        @endforeach
                    </ol>
                </article>
            @endforeach
        </div>
    </section>

    {{-- PREGUNTAS FRECUENTES --}}
    <section class="{{ $tarjeta }}" aria-labelledby="ayuda-faq">
        <h2 id="ayuda-faq" class="text-sm font-semibold text-[var(--theme-text-strong)]">Preguntas frecuentes</h2>

        <div class="mt-3 divide-y divide-[var(--theme-border)]">
            @foreach ($preguntas as [$pregunta, $respuesta])
                <details
                    x-show="coincide(@js($pregunta.' '.$respuesta))"
                    class="group py-1"
                >
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-md px-1 py-3 text-sm font-medium text-[var(--theme-text)] hover:text-[var(--theme-text-strong)]">
                        <span>{{ $pregunta }}</span>
                        <svg class="h-4 w-4 shrink-0 text-[var(--theme-text-muted)] transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <p class="px-1 pb-3 text-sm leading-relaxed text-[var(--theme-text-muted)]">{{ $respuesta }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        {{-- ATAJOS --}}
        <section class="{{ $tarjeta }}" aria-labelledby="ayuda-atajos" x-show="coincide('atajos teclado {{ implode(' ', array_column($atajos, 1)) }}')">
            <h2 id="ayuda-atajos" class="text-sm font-semibold text-[var(--theme-text-strong)]">Atajos de teclado</h2>
            <dl class="mt-3 space-y-2">
                @foreach ($atajos as [$tecla, $accion])
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <dt class="text-[var(--theme-text)]">{{ $accion }}</dt>
                        <dd><kbd class="whitespace-nowrap rounded border border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] px-2 py-0.5 text-xs font-medium text-[var(--theme-text-strong)]">{{ $tecla }}</kbd></dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- CONTACTO --}}
        <section class="{{ $tarjeta }}" aria-labelledby="ayuda-contacto">
            <h2 id="ayuda-contacto" class="text-sm font-semibold text-[var(--theme-text-strong)]">Contacto de soporte</h2>
            <p class="mt-3 text-base font-semibold text-[var(--theme-text-strong)]">{{ $soporte['equipo'] }}</p>
            @if (filled($soporte['integrantes']))
                <p class="text-sm text-[var(--theme-text-muted)]">{{ $soporte['integrantes'] }}</p>
            @endif

            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <dt class="text-[var(--theme-text-muted)]">Correo</dt>
                    <dd class="flex items-center gap-2" x-data="{ copiado: false }">
                        <a href="mailto:{{ $soporte['correo'] }}?subject={{ rawurlencode('Soporte Track IT') }}" class="font-medium text-[var(--theme-primary)] hover:underline">{{ $soporte['correo'] }}</a>
                        <button
                            type="button"
                            x-on:click="navigator.clipboard?.writeText(@js($soporte['correo'])); copiado = true; setTimeout(() => copiado = false, 1500)"
                            class="rounded px-1.5 py-0.5 text-xs text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]"
                            x-text="copiado ? 'Copiado' : 'Copiar'"
                        >Copiar</button>
                    </dd>
                </div>
                @if (filled($soporte['telefono']))
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <dt class="text-[var(--theme-text-muted)]">Teléfono</dt>
                        <dd class="font-medium text-[var(--theme-text)]">{{ $soporte['telefono'] }}</dd>
                    </div>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <dt class="text-[var(--theme-text-muted)]">Horario de atención</dt>
                    <dd class="font-medium text-[var(--theme-text)]">{{ $soporte['horario'] }}</dd>
                </div>
            </dl>

            @can('administrar')
                <p class="mt-4 text-xs text-[var(--theme-text-muted)]">
                    Puedes cambiar estos datos en
                    <a href="{{ route('catalogos.index', ['section' => 'organizacion']) }}" wire:navigate class="text-[var(--theme-primary)] hover:underline">Administración › Organización › Soporte</a>.
                </p>
            @endcan
        </section>
    </div>

    {{-- REPORTAR UN PROBLEMA --}}
    <section class="{{ $tarjeta }}" aria-labelledby="ayuda-reporte">
        <h2 id="ayuda-reporte" class="text-sm font-semibold text-[var(--theme-text-strong)]">Reportar un problema</h2>
        <p class="mb-4 mt-1 text-sm text-[var(--theme-text-muted)]">El reporte llega a los administradores como notificación. Si es urgente, escribe también al correo de soporte.</p>

        <livewire:ayuda-reporte />
    </section>

    <p class="pb-2 text-center text-xs text-[var(--theme-text-muted)]">
        Track IT · {{ $soporte['equipo'] }} · {{ now()->year }}
    </p>
</div>
@endsection
