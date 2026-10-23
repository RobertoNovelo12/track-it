{{-- ============================================================
    MENSAJES DE ERROR CONCRETOS
    ------------------------------------------------------------
    window.trackitMensajeError(error, 'guardar el equipo') explica
    qué pasó según la causa, en lugar de «Inténtalo nuevamente»:
    sin conexión, sesión vencida, sin permiso, registro eliminado,
    demasiados intentos o falla del servidor. Acepta el error de
    una acción de Livewire ($wire.metodo) o { status } de un fetch.
============================================================ --}}
<script>
    window.trackitMensajeError = (error, accion = 'completar la acción') => {
        const status = Number(error?.status ?? 0) || 0;

        if (! navigator.onLine || error instanceof TypeError) {
            return 'Sin conexión a internet. Revisa tu red y vuelve a intentarlo; lo que escribiste sigue aquí.';
        }

        if (status === 422) {
            const primero = Object.values(error?.errors ?? {})[0];
            const mensaje = Array.isArray(primero) ? primero[0] : primero;

            return mensaje || 'Revisa los campos marcados en rojo.';
        }

        if (status === 419) {
            return 'Tu sesión expiró por inactividad. Copia lo que escribiste y recarga la página para continuar.';
        }

        if (status === 401) {
            return 'Tu sesión terminó. Vuelve a iniciar sesión para ' + accion + '.';
        }

        if (status === 403) {
            return 'Tu rol no tiene permiso para ' + accion + '. Pide acceso a un administrador.';
        }

        if (status === 404) {
            return 'El registro ya no existe; quizá otra persona lo eliminó. Recarga la página.';
        }

        if (status === 429) {
            return 'Demasiados intentos seguidos. Espera un minuto y vuelve a intentarlo.';
        }

        if (status >= 500) {
            return 'El servidor tuvo un problema al ' + accion + '. Tus datos no se perdieron; vuelve a intentarlo en unos segundos.';
        }

        return 'No se pudo ' + accion + '. Revisa tu conexión y vuelve a intentarlo.';
    };
</script>
