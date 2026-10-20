{{-- ============================================================
    PUENTE DE EVENTOS → AVISO GLOBAL
    ------------------------------------------------------------
    Eventos que ya emiten los componentes de Livewire y que se
    muestran con el aviso global (components/ui/toast-region).
    Cada evento puede traer "message"; si no, se usa el texto
    predeterminado.

    Para agregar uno: una entrada en "eventos" con
    [tipo, título, mensaje predeterminado].
============================================================ --}}
<div
    x-data="{
        eventos: {
            'perfil-actualizado': ['success', 'Perfil actualizado', 'Tu información se actualizó correctamente.'],
            'password-updated': ['success', 'Contraseña actualizada', 'Tu contraseña se actualizó correctamente.'],
            'two-factor-enabled': ['success', 'Verificación en dos pasos activada', 'Tu cuenta ahora pide un código al iniciar sesión.'],
            'two-factor-disabled': ['success', 'Verificación en dos pasos desactivada', 'La autenticación en dos pasos se desactivó.'],
            'two-factor-recovery-codes-regenerated': ['success', 'Códigos regenerados', 'Se generaron nuevos códigos de recuperación.'],
            'preferencias-actualizadas': ['success', 'Preferencias guardadas', 'Tus preferencias se guardaron correctamente.'],
            'notification-preferences-updated': ['success', 'Notificaciones actualizadas', 'Tus preferencias de notificaciones se guardaron.'],
            'security-preferences-updated': ['success', 'Seguridad actualizada', 'Tus preferencias de seguridad se guardaron.'],
            'other-sessions-closed': ['success', 'Sesiones cerradas', 'Se cerraron tus sesiones en otros dispositivos.'],
            'solicitud-organizacion-enviada': ['success', 'Solicitud enviada', 'Tu solicitud quedó pendiente de revisión.'],
            'usuario-aprobado': ['success', 'Usuario aprobado', 'El usuario ya puede iniciar sesión.'],
            'solicitud-organizacion-aprobada': ['success', 'Solicitud aprobada', 'La información del usuario se actualizó.'],
            'solicitud-organizacion-rechazada': ['info', 'Solicitud rechazada', 'Se notificó el motivo al usuario.'],
        },

        init() {
            Object.entries(this.eventos).forEach(([evento, [tipo, titulo, mensaje]]) => {
                window.addEventListener(evento, (event) => {
                    const detalle = Array.isArray(event.detail)
                        ? (event.detail[0] ?? {})
                        : (event.detail ?? {});

                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: tipo,
                            title: titulo,
                            message: detalle.message ?? mensaje,
                        },
                    }));
                });
            });
        },
    }"
    hidden
></div>


{{-- ============================================================
    ERRORES DE CONEXIÓN Y PERMISOS EN PETICIONES DE LIVEWIRE
    ------------------------------------------------------------
    En lugar de dejar la pantalla sin respuesta:
    - Sin conexión o servidor caído: aviso con indicación de reintentar.
    - 403: aviso de permiso (roles).
    - 500 en producción: aviso genérico. Con APP_DEBUG activo se deja
      la ventana de error de Livewire para poder depurar.
============================================================ --}}
<script>
    document.addEventListener('livewire:init', () => {
        if (window.__trackitErroresLivewire || ! window.Livewire?.hook) {
            return;
        }

        window.__trackitErroresLivewire = true;

        const depuracion = @js((bool) config('app.debug'));

        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                const avisar = (titulo, mensaje) => window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: { type: 'error', title: titulo, message: mensaje },
                    })
                );

                if (! navigator.onLine || status === 0 || status === 502 || status === 503 || status === 504) {
                    preventDefault();
                    avisar('Sin conexión con el servidor', 'Revisa tu conexión e inténtalo de nuevo. Tus datos en pantalla no se perdieron.');

                    return;
                }

                if (status === 403) {
                    preventDefault();
                    avisar('Acción no permitida', 'Tu rol no tiene permiso para realizar esta acción.');

                    return;
                }

                if (status >= 500 && ! depuracion) {
                    preventDefault();
                    avisar('No se pudo completar la acción', 'Ocurrió un error en el servidor. Inténtalo de nuevo en unos momentos.');
                }
            });
        });
    });
</script>
