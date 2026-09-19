<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
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
         * Obtener únicamente las 20 notificaciones
         * más recientes del usuario.
         */
        $notifications = DB::table('notificaciones')
            ->where(
                'id_usuario',
                $userId
            )
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
    | 2. La marcamos como leída.
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


        /*
         * Si todavía no estaba leída,
         * la marcamos antes de navegar.
         */
        if (! (bool) $notification->leida) {
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
             * Reflejar el cambio en memoria
             * sin recargar todo el listado.
             */
            foreach (
                $this->notifications as $index => $item
            ) {
                if (
                    (int) $item['id'] !==
                    $notificationId
                ) {
                    continue;
                }


                $this->notifications[$index]['leida'] =
                    true;

                break;
            }


            $this->refreshUnreadCount();

            $this->dispatchUnreadCount();
        }


        /*
         * Si no tiene destino, simplemente queda leída.
         */
        if (! filled($notification->url_destino)) {
            return;
        }


        $destination =
            (string) $notification->url_destino;


        /*
         * Solo permitimos rutas internas relativas.
         *
         * Ejemplos permitidos:
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
         * Actualizar únicamente ese elemento
         * del listado que ya está en memoria.
         */
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
         * Actualizar las que ya tenemos
         * cargadas en memoria.
         */
        foreach (
            $this->notifications as $index => $notification
        ) {
            $this->notifications[$index]['leida'] =
                true;
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