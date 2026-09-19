<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsPanel extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Estado
    |--------------------------------------------------------------------------
    */

    public array $notifications = [];

    public bool $notificationsLoaded = false;

    public int $unreadCount = 0;


    /*
    |--------------------------------------------------------------------------
    | Montaje
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        /*
         * Al cargar la página solamente necesitamos
         * conocer cuántas notificaciones pendientes existen.
         *
         * El listado completo se cargará únicamente
         * cuando el usuario abra el panel.
         */
        $this->refreshUnreadCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Primera carga del listado
    |--------------------------------------------------------------------------
    */

    public function loadNotifications(): void
    {
        /*
         * Si ya fueron cargadas durante la vida
         * actual del componente, no hacemos otra consulta.
         */
        if ($this->notificationsLoaded) {
            return;
        }


        $this->refreshNotifications();
    }


    /*
    |--------------------------------------------------------------------------
    | Sincronizar cambios de preferencias
    |--------------------------------------------------------------------------
    |
    | Si el panel ya fue cargado y el usuario cambia sus preferencias
    | desde Ajustes > Notificaciones, actualizamos solamente este
    | componente para reflejar la nueva configuración.
    |
    */

    #[On('notification-preferences-updated')]
    public function syncNotificationPreferences(): void
    {
        if (! $this->notificationsLoaded) {
            return;
        }


        $this->refreshNotifications();
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar listado
    |--------------------------------------------------------------------------
    */

    public function refreshNotifications(): void
    {
        $userId = auth()->id();


        if (! $userId) {
            $this->notifications = [];

            $this->unreadCount = 0;

            $this->notificationsLoaded = true;

            $this->dispatchUnreadCount();

            return;
        }


        /*
         * Consultar las preferencias actuales.
         *
         * No guardamos estos valores como estado permanente del componente
         * porque pueden cambiar desde Ajustes mientras el panel permanece
         * montado en el layout global.
         */
        $preferences =
            $this->notificationPreferences(
                (int) $userId
            );


        /*
         * Si el usuario decidió no conservar historial,
         * cualquier notificación ya leída deja de tener utilidad
         * y se elimina antes de construir el listado.
         */
        if (! $preferences['mantener_historial']) {
            DB::table('notificaciones')
                ->where(
                    'id_usuario',
                    $userId
                )
                ->where(
                    'leida',
                    true
                )
                ->delete();
        }


        /*
         * Consulta base de las notificaciones del usuario.
         */
        $query =
            DB::table('notificaciones')
                ->where(
                    'id_usuario',
                    $userId
                );


        /*
         * Si el usuario eligió ver únicamente pendientes,
         * no traemos las notificaciones ya revisadas.
         */
        if ($preferences['solo_no_leidas']) {
            $query->where(
                'leida',
                false
            );
        }


        /*
         * Obtener únicamente las 20 notificaciones
         * más recientes que cumplen la configuración.
         */
        $notifications =
            $query
                ->orderByDesc(
                    'fecha_creacion'
                )
                ->limit(20)
                ->get([
                    'id_notificacion',
                    'tipo',
                    'titulo',
                    'mensaje',
                    'url_destino',
                    'leida',
                    'fecha_creacion',
                ]);


        /*
         * Preparar datos para la vista.
         */
        $this->notifications =
            $notifications
                ->map(
                    function ($notification): array {
                        return [
                            'id' =>
                                (int) $notification->id_notificacion,

                            'tipo' =>
                                (string) $notification->tipo,

                            'titulo' =>
                                (string) $notification->titulo,

                            'mensaje' =>
                                (string) $notification->mensaje,

                            'url_destino' =>
                                filled($notification->url_destino)
                                    ? (string) $notification->url_destino
                                    : null,

                            'leida' =>
                                (bool) $notification->leida,

                            'fecha' =>
                                Carbon::parse(
                                    $notification->fecha_creacion
                                )
                                    ->locale('es')
                                    ->diffForHumans(),
                        ];
                    }
                )
                ->values()
                ->all();


        /*
         * El contador se calcula sobre TODAS
         * las notificaciones pendientes.
         */
        $this->refreshUnreadCount();


        $this->notificationsLoaded = true;


        /*
         * Sincronizar la campana global.
         */
        $this->dispatchUnreadCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Abrir una notificación
    |--------------------------------------------------------------------------
    |
    | Si tiene destino:
    |
    | 1. Verificamos que pertenezca al usuario.
    | 2. La consumimos según la preferencia de historial.
    | 3. Actualizamos el contador.
    | 4. Redirigimos al destino.
    |
    */

    public function openNotification(
        int $notificationId
    ) {
        $userId = auth()->id();


        if (! $userId) {
            return;
        }


        $notification = DB::table('notificaciones')
            ->where(
                'id_notificacion',
                $notificationId
            )
            ->where(
                'id_usuario',
                $userId
            )
            ->first([
                'id_notificacion',
                'url_destino',
                'leida',
            ]);


        /*
         * La notificación no existe
         * o no pertenece al usuario.
         */
        if (! $notification) {
            return;
        }


        $preferences =
            $this->notificationPreferences(
                (int) $userId
            );


        /*
         * Sin historial:
         *
         * Al abrir una notificación se considera revisada
         * y se elimina en lugar de conservarla como leída.
         */
        if (! $preferences['mantener_historial']) {
            DB::table('notificaciones')
                ->where(
                    'id_notificacion',
                    $notificationId
                )
                ->where(
                    'id_usuario',
                    $userId
                )
                ->delete();


            $this->removeNotificationFromMemory(
                $notificationId
            );


            $this->refreshUnreadCount();

            $this->dispatchUnreadCount();
        }

        /*
         * Con historial:
         *
         * La conservamos y solamente la marcamos como leída.
         */
        elseif (! (bool) $notification->leida) {
            DB::table('notificaciones')
                ->where(
                    'id_notificacion',
                    $notificationId
                )
                ->where(
                    'id_usuario',
                    $userId
                )
                ->update([
                    'leida' =>
                        true,

                    'fecha_lectura' =>
                        now(),
                ]);


            /*
             * Si la vista está configurada para mostrar solamente
             * pendientes, la notificación debe desaparecer del panel.
             *
             * En caso contrario permanece visible como "Leída".
             */
            if ($preferences['solo_no_leidas']) {
                $this->removeNotificationFromMemory(
                    $notificationId
                );
            } else {
                $this->markNotificationAsReadInMemory(
                    $notificationId
                );
            }


            $this->refreshUnreadCount();

            $this->dispatchUnreadCount();
        }


        /*
         * Si no tiene destino, simplemente queda consumida
         * según la preferencia configurada.
         */
        if (! filled($notification->url_destino)) {
            return;
        }


        $destination =
            (string) $notification->url_destino;


        /*
         * Solo permitimos rutas internas relativas.
         *
         * Ejemplo permitido:
         *
         * /ajustes?section=seguridad#sesiones
         *
         * Esto evita utilizar accidentalmente una URL
         * externa almacenada en la base de datos.
         */
        if (
            ! str_starts_with(
                $destination,
                '/'
            )
            ||
            str_starts_with(
                $destination,
                '//'
            )
        ) {
            return;
        }


        return redirect()->to(
            $destination
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar una notificación como leída
    |--------------------------------------------------------------------------
    */

    public function markAsRead(
        int $notificationId
    ): void {
        $userId = auth()->id();


        if (! $userId) {
            return;
        }


        $preferences =
            $this->notificationPreferences(
                (int) $userId
            );


        /*
         * Si no se conservará historial,
         * "marcar como leída" equivale a consumirla
         * y eliminarla definitivamente.
         */
        if (! $preferences['mantener_historial']) {
            $deleted = DB::table('notificaciones')
                ->where(
                    'id_notificacion',
                    $notificationId
                )
                ->where(
                    'id_usuario',
                    $userId
                )
                ->delete();


            if ($deleted === 0) {
                return;
            }


            $this->removeNotificationFromMemory(
                $notificationId
            );


            $this->refreshUnreadCount();

            $this->dispatchUnreadCount();

            return;
        }


        /*
         * Con historial, conservamos la fila.
         */
        $updated = DB::table('notificaciones')
            ->where(
                'id_notificacion',
                $notificationId
            )
            ->where(
                'id_usuario',
                $userId
            )
            ->where(
                'leida',
                false
            )
            ->update([
                'leida' =>
                    true,

                'fecha_lectura' =>
                    now(),
            ]);


        /*
         * Si ya estaba leída o no pertenece
         * al usuario, no hacemos nada.
         */
        if ($updated === 0) {
            return;
        }


        /*
         * Cuando el panel está en modo "solo no leídas",
         * la notificación recién revisada debe desaparecer.
         */
        if ($preferences['solo_no_leidas']) {
            $this->removeNotificationFromMemory(
                $notificationId
            );
        } else {
            $this->markNotificationAsReadInMemory(
                $notificationId
            );
        }


        $this->refreshUnreadCount();

        $this->dispatchUnreadCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar todas como leídas
    |--------------------------------------------------------------------------
    */

    public function markAllAsRead(): void
    {
        $userId = auth()->id();


        if (! $userId) {
            return;
        }


        $preferences =
            $this->notificationPreferences(
                (int) $userId
            );


        /*
         * Sin historial:
         *
         * Al consumir todas las notificaciones,
         * ninguna debe permanecer almacenada.
         */
        if (! $preferences['mantener_historial']) {
            DB::table('notificaciones')
                ->where(
                    'id_usuario',
                    $userId
                )
                ->delete();


            $this->notifications = [];

            $this->unreadCount = 0;

            $this->notificationsLoaded = true;

            $this->dispatchUnreadCount();

            return;
        }


        /*
         * Con historial:
         *
         * Conservamos todas las filas y solamente
         * actualizamos las que aún estaban pendientes.
         */
        DB::table('notificaciones')
            ->where(
                'id_usuario',
                $userId
            )
            ->where(
                'leida',
                false
            )
            ->update([
                'leida' =>
                    true,

                'fecha_lectura' =>
                    now(),
            ]);


        /*
         * Si estamos mostrando únicamente pendientes,
         * después de marcarlas todas no queda nada visible.
         */
        if ($preferences['solo_no_leidas']) {
            $this->notifications = [];
        } else {
            foreach (
                $this->notifications as $index => $notification
            ) {
                $this->notifications[$index]['leida'] =
                    true;
            }
        }


        $this->unreadCount = 0;

        $this->dispatchUnreadCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar una notificación
    |--------------------------------------------------------------------------
    */

    public function deleteNotification(
        int $notificationId
    ): void {
        $userId = auth()->id();


        if (! $userId) {
            return;
        }


        /*
         * El WHERE por id_usuario evita eliminar
         * notificaciones de cualquier otro usuario.
         */
        $deleted = DB::table('notificaciones')
            ->where(
                'id_notificacion',
                $notificationId
            )
            ->where(
                'id_usuario',
                $userId
            )
            ->delete();


        if ($deleted === 0) {
            return;
        }


        /*
         * Quitamos la notificación de memoria.
         *
         * No recargamos las otras 19.
         */
        $this->removeNotificationFromMemory(
            $notificationId
        );


        /*
         * Recalcular contador real porque la
         * eliminada pudo haber estado sin leer.
         */
        $this->refreshUnreadCount();

        $this->dispatchUnreadCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar todas las notificaciones
    |--------------------------------------------------------------------------
    */

    public function deleteAllNotifications(): void
    {
        $userId = auth()->id();


        if (! $userId) {
            return;
        }


        /*
         * Eliminar exclusivamente las
         * notificaciones del usuario autenticado.
         */
        DB::table('notificaciones')
            ->where(
                'id_usuario',
                $userId
            )
            ->delete();


        /*
         * Como ya sabemos que no queda ninguna,
         * no necesitamos volver a consultar.
         */
        $this->notifications = [];

        $this->unreadCount = 0;

        $this->notificationsLoaded = true;


        /*
         * Ocultar inmediatamente el punto rojo
         * de la campana global.
         */
        $this->dispatchUnreadCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Preferencias del centro de notificaciones
    |--------------------------------------------------------------------------
    */

    private function notificationPreferences(
        int $userId
    ): array {
        $preferences = DB::table(
            'preferencias_notificaciones'
        )
            ->where(
                'id_usuario',
                $userId
            )
            ->first([
                'solo_no_leidas',
                'mantener_historial',
            ]);


        /*
         * Si el usuario todavía no guardó preferencias,
         * utilizamos los mismos valores predeterminados
         * definidos en la migración.
         */
        return [
            'solo_no_leidas' =>
                $preferences === null
                    ? false
                    : (bool) $preferences->solo_no_leidas,

            'mantener_historial' =>
                $preferences === null
                    ? true
                    : (bool) $preferences->mantener_historial,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar una notificación del estado en memoria
    |--------------------------------------------------------------------------
    */

    private function removeNotificationFromMemory(
        int $notificationId
    ): void {
        $this->notifications =
            collect(
                $this->notifications
            )
                ->reject(
                    fn (array $notification): bool =>
                        (int) $notification['id'] ===
                        $notificationId
                )
                ->values()
                ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar una notificación como leída en memoria
    |--------------------------------------------------------------------------
    */

    private function markNotificationAsReadInMemory(
        int $notificationId
    ): void {
        foreach (
            $this->notifications as $index => $notification
        ) {
            if (
                (int) $notification['id'] !==
                $notificationId
            ) {
                continue;
            }


            $this->notifications[$index]['leida'] =
                true;

            break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Contar no leídas
    |--------------------------------------------------------------------------
    */

    private function refreshUnreadCount(): void
    {
        $userId = auth()->id();


        if (! $userId) {
            $this->unreadCount = 0;

            return;
        }


        $this->unreadCount =
            DB::table('notificaciones')
                ->where(
                    'id_usuario',
                    $userId
                )
                ->where(
                    'leida',
                    false
                )
                ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Sincronizar contador con la campana global
    |--------------------------------------------------------------------------
    */

    private function dispatchUnreadCount(): void
    {
        $this->dispatch(
            'notifications-unread-updated',
            count: $this->unreadCount
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        return view(
            'livewire.notifications-panel'
        );
    }
}