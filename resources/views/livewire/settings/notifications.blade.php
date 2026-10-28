<div class="space-y-5">

    {{-- ============================================================
        EN ESTE DISPOSITIVO (notificaciones push)
        ------------------------------------------------------------
        Activa los avisos del sistema operativo en este navegador o
        teléfono (App\Services\PushService, public/sw.js). Las
        categorías de abajo deciden qué avisos llegan.
    ============================================================ --}}
    <section
        wire:ignore
        x-data="{
            llave: document.querySelector('meta[name=vapid-public-key]')?.content ?? '',
            soportado: 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window,
            iosSinInstalar: (window.trackitEsIos?.() ?? false) && ! (window.trackitAppInstalada?.() ?? false),
            permiso: ('Notification' in window) ? Notification.permission : 'default',
            activo: false,
            trabajando: false,
            aviso: '',
            avisoError: false,

            async init() {
                if (! this.soportado || ! this.llave) {
                    return;
                }

                try {
                    const registro = await navigator.serviceWorker.ready;
                    this.activo = Boolean(await registro.pushManager.getSubscription());
                } catch (error) {
                    this.activo = false;
                }
            },

            llaveBinaria() {
                const relleno = '='.repeat((4 - (this.llave.length % 4)) % 4);
                const base64 = (this.llave + relleno).replace(/-/g, '+').replace(/_/g, '/');
                const texto = atob(base64);

                return Uint8Array.from([...texto].map(caracter => caracter.charCodeAt(0)));
            },

            async peticion(metodo, url, cuerpo = null) {
                const respuesta = await fetch(url, {
                    method: metodo,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    credentials: 'same-origin',
                    body: cuerpo ? JSON.stringify(cuerpo) : null,
                });

                if (! respuesta.ok) {
                    throw { status: respuesta.status };
                }

                return respuesta.json();
            },

            mostrar(mensaje, error = false) {
                this.aviso = mensaje;
                this.avisoError = error;
            },

            async alternar() {
                if (this.trabajando) {
                    return;
                }

                this.trabajando = true;
                this.mostrar('');

                try {
                    this.activo ? await this.desactivar() : await this.activar();
                } finally {
                    this.trabajando = false;
                }
            },

            async activar() {
                this.permiso = await Notification.requestPermission();

                if (this.permiso !== 'granted') {
                    this.mostrar(
                        this.permiso === 'denied'
                            ? 'El navegador tiene bloqueadas las notificaciones de Track IT. Permítelas desde el candado de la barra de direcciones (o en los ajustes del teléfono) y vuelve a intentarlo.'
                            : 'No se dio permiso para mostrar notificaciones.',
                        true
                    );

                    return;
                }

                try {
                    const registro = await navigator.serviceWorker.ready;
                    const suscripcion = await registro.pushManager.getSubscription()
                        ?? await registro.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: this.llaveBinaria(),
                        });

                    await this.peticion('POST', @js(route('push.suscribir')), suscripcion.toJSON());

                    this.activo = true;
                    this.mostrar('Listo: este dispositivo recibirá las notificaciones de Track IT.');
                } catch (error) {
                    this.activo = false;
                    this.mostrar(window.trackitMensajeError?.(error, 'activar las notificaciones') ?? 'No se pudieron activar las notificaciones.', true);
                }
            },

            async desactivar() {
                try {
                    const registro = await navigator.serviceWorker.ready;
                    const suscripcion = await registro.pushManager.getSubscription();

                    if (suscripcion) {
                        await this.peticion('DELETE', @js(route('push.cancelar')), { endpoint: suscripcion.endpoint });
                        await suscripcion.unsubscribe();
                    }

                    this.activo = false;
                    this.mostrar('Este dispositivo ya no recibirá notificaciones.');
                } catch (error) {
                    this.mostrar(window.trackitMensajeError?.(error, 'desactivar las notificaciones') ?? 'No se pudieron desactivar las notificaciones.', true);
                }
            },

            async probar() {
                if (this.trabajando) {
                    return;
                }

                this.trabajando = true;

                try {
                    const resultado = await this.peticion('POST', @js(route('push.prueba')));

                    this.mostrar(
                        resultado.enviados > 0
                            ? 'Notificación de prueba enviada. Debería aparecer en unos segundos.'
                            : 'No se pudo entregar la prueba. Desactiva y vuelve a activar las notificaciones.',
                        resultado.enviados === 0
                    );
                } catch (error) {
                    this.mostrar(window.trackitMensajeError?.(error, 'enviar la prueba') ?? 'No se pudo enviar la prueba.', true);
                } finally {
                    this.trabajando = false;
                }
            },
        }"
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-b5fb2ca') }}"></use></svg>
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    En este dispositivo
                </h2>

                <p class="mt-0.5 text-xs leading-relaxed text-[var(--theme-text-muted)]">
                    Recibe los avisos como notificaciones del teléfono o de la computadora, aunque Track IT esté cerrado. Se activa por separado en cada dispositivo.
                </p>
            </div>
        </div>

        <div class="px-4 sm:px-5 py-4 space-y-3">

            {{-- Sin soporte o sin configurar --}}
            <p x-show="! llave" x-cloak class="text-xs leading-relaxed text-[var(--theme-text-muted)]">
                Las notificaciones en el dispositivo todavía no están configuradas en el servidor. Pide a un administrador que agregue las llaves VAPID.
            </p>

            <p x-show="llave && iosSinInstalar" x-cloak class="text-xs leading-relaxed text-[var(--theme-text-muted)]">
                En iPhone primero instala Track IT: en Safari toca <strong class="font-semibold text-[var(--theme-text-strong)]">Compartir › Agregar a pantalla de inicio</strong>, ábrelo desde ese ícono y vuelve aquí.
            </p>

            <p x-show="llave && ! iosSinInstalar && ! soportado" x-cloak class="text-xs leading-relaxed text-[var(--theme-text-muted)]">
                Este navegador no permite notificaciones. Prueba con Chrome, Edge o Firefox.
            </p>

            {{-- Interruptor --}}
            <div
                x-show="llave && soportado && ! iosSinInstalar"
                x-cloak
                class="flex items-center justify-between gap-4"
            >
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-[var(--theme-text-strong)]">
                        Notificaciones en este dispositivo
                    </p>

                    <p
                        class="mt-1 text-xs leading-relaxed text-[var(--theme-text-muted)]"
                        x-text="permiso === 'denied'
                            ? 'Bloqueadas por el navegador: permítelas desde el candado de la barra de direcciones.'
                            : (activo ? 'Activadas.' : 'Desactivadas.')"
                    ></p>
                </div>

                <label class="relative inline-flex items-center shrink-0 cursor-pointer">
                    <input
                        type="checkbox"
                        class="peer sr-only"
                        x-bind:checked="activo"
                        x-bind:disabled="trabajando"
                        x-on:change.prevent="$event.target.checked = activo; alternar()"
                        aria-label="Notificaciones en este dispositivo"
                    >

                    <span
                        class="
                            relative

                            block
                            w-11
                            h-6

                            rounded-full

                            bg-[var(--theme-border)]

                            transition-colors
                            duration-200

                            peer-checked:bg-[var(--theme-primary)]

                            after:content-['']
                            after:absolute
                            after:top-1/2
                            after:left-0.5

                            after:w-5
                            after:h-5

                            after:-translate-y-1/2

                            after:rounded-full
                            after:bg-white

                            after:shadow-sm

                            after:transition-transform
                            after:duration-200

                            peer-checked:after:translate-x-5
                        "
                    ></span>
                </label>
            </div>

            <div x-show="llave && soportado && activo" x-cloak>
                <button
                    type="button"
                    x-on:click="probar()"
                    x-bind:disabled="trabajando"
                    class="text-xs font-medium text-[var(--theme-primary)] hover:underline disabled:opacity-60"
                >
                    Enviar notificación de prueba
                </button>
            </div>

            <p
                x-show="aviso !== ''"
                x-cloak
                x-text="aviso"
                role="status"
                aria-live="polite"
                class="text-xs leading-relaxed"
                x-bind:class="avisoError ? 'text-[var(--theme-danger)]' : 'text-[var(--theme-text-muted)]'"
            ></p>
        </div>
    </section>


    {{-- ============================================================
        ACTIVIDAD OPERATIVA
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4.5 h-4.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>
            </div>


            <div class="min-w-0 flex-1">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Actividad operativa
                </h2>


                <p
                    class="
                        mt-0.5

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Elige qué cambios relacionados con la operación quieres recibir en tu centro de notificaciones.
                </p>

            </div>
        </div>


        {{-- ========================================================
            ASIGNACIONES Y MOVIMIENTOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Asignaciones y movimientos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Recibe avisos cuando un equipo sea asignado, transferido, devuelto o cambie de responsable.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarAsignacionesMovimientos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            MANTENIMIENTOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mantenimientos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Recibe avisos sobre mantenimientos programados, próximos, vencidos o completados.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarMantenimientos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            CAMBIOS EN EQUIPOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Cambios en equipos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Avísame cuando cambie el estado de un equipo o se registre una modificación importante.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarCambiosEquipos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>
    </section>


    {{-- ============================================================
        ADMINISTRACIÓN
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4.5 h-4.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                ><use href="{{ \App\Support\Icono::url('i-ba5884d') }}"></use></svg>
            </div>


            <div class="min-w-0 flex-1">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Administración
                </h2>


                <p
                    class="
                        mt-0.5

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Controla las notificaciones relacionadas con procesos administrativos del sistema.
                </p>

            </div>
        </div>


        {{-- ========================================================
            USUARIOS Y ACCESOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Usuarios y accesos
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Recibe avisos sobre altas de usuarios, solicitudes, permisos y otros cambios administrativos.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarUsuariosAccesos"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            REPORTES
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Reportes
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Avísame cuando un reporte o una exportación solicitada esté disponible.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="notificarReportes"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>
    </section>


    {{-- ============================================================
        CENTRO DE NOTIFICACIONES
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-4.5 h-4.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                ><use href="{{ \App\Support\Icono::url('i-992e6c4') }}"></use></svg>
            </div>


            <div class="min-w-0 flex-1">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Centro de notificaciones
                </h2>


                <p
                    class="
                        mt-0.5

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Configura cómo quieres visualizar y conservar tus notificaciones dentro de Track-It.
                </p>

            </div>
        </div>


        {{-- ========================================================
            SOLO NO LEÍDAS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mostrar solo notificaciones no leídas
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Oculta del panel las notificaciones que ya hayas revisado.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="soloNoLeidas"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            MANTENER HISTORIAL
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                items-center
                justify-between
                gap-4
            "
        >
            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Mantener historial
                </p>


                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Conserva las notificaciones leídas hasta que decidas eliminarlas manualmente.
                </p>

            </div>


            <label
                class="
                    relative
                    inline-flex
                    items-center
                    shrink-0
                    cursor-pointer
                "
            >
                <input
                    type="checkbox"
                    wire:model="mantenerHistorial"
                    class="peer sr-only"
                >

                <span
                    class="
                        relative

                        block
                        w-11
                        h-6

                        rounded-full

                        bg-[var(--theme-border)]

                        transition-colors
                        duration-200

                        peer-checked:bg-[var(--theme-primary)]

                        after:content-['']
                        after:absolute
                        after:top-1/2
                        after:left-0.5

                        after:w-5
                        after:h-5

                        after:-translate-y-1/2

                        after:rounded-full
                        after:bg-white

                        after:shadow-sm

                        after:transition-transform
                        after:duration-200

                        peer-checked:after:translate-x-5
                    "
                ></span>
            </label>
        </div>


        {{-- ========================================================
            PIE / GUARDAR
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-t
                border-[var(--theme-border)]

                bg-[var(--theme-surface-soft)]

                flex
                justify-end
            "
        >
            <x-ui.button
                wire:click="saveNotificationPreferences"
                target="saveNotificationPreferences"
                size="compact"
                loading-text="Guardando…"
                class="w-full sm:w-auto sm:min-w-36"
            >
                Guardar cambios
            </x-ui.button>
        </div>
    </section>

</div>