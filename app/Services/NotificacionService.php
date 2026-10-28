<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/*
|--------------------------------------------------------------------------
| Notificaciones del sistema
|--------------------------------------------------------------------------
|
| Crea las notificaciones del centro de notificaciones (tabla
| notificaciones) respetando lo que cada usuario eligió en
| Ajustes › Notificaciones (tabla preferencias_notificaciones).
|
| Categorías y su preferencia:
|
|   asignaciones   -> notificar_asignaciones_movimientos (por defecto sí)
|   mantenimientos -> notificar_mantenimientos           (por defecto sí)
|   equipos        -> notificar_cambios_equipos          (por defecto sí)
|   usuarios       -> notificar_usuarios_accesos         (por defecto sí)
|   reportes       -> notificar_reportes                 (por defecto no)
|
| Un usuario sin fila en preferencias_notificaciones usa el valor
| por defecto de la columna.
|
| Nunca interrumpe la operación que la origina: si algo falla se
| registra en el log y la acción principal continúa.
|
*/

class NotificacionService
{
    public const CATEGORIAS = [
        'asignaciones' => [
            'columna' => 'notificar_asignaciones_movimientos',
            'soloGestion' => true,
            'tipo' => 'ASIGNACION',
            'defecto' => true,
        ],
        'mantenimientos' => [
            'columna' => 'notificar_mantenimientos',
            'soloGestion' => true,
            'tipo' => 'MANTENIMIENTO',
            'defecto' => true,
        ],
        'equipos' => [
            'columna' => 'notificar_cambios_equipos',
            'tipo' => 'EQUIPO',
            'defecto' => true,
        ],
        'usuarios' => [
            'columna' => 'notificar_usuarios_accesos',
            'tipo' => 'USUARIOS',
            'defecto' => true,
        ],
        'reportes' => [
            'columna' => 'notificar_reportes',
            'tipo' => 'REPORTE',
            'defecto' => false,
        ],
    ];

    /*
     * Módulos de permisos que identifican a quien administra
     * usuarios (para avisos de altas y solicitudes).
     */
    private const MODULOS_ADMINISTRACION_USUARIOS = [
        'seguridad',
        'usuarios',
    ];

    /*
    |--------------------------------------------------------------------------
    | Enviar
    |--------------------------------------------------------------------------
    |
    | $destinatarios:
    |   null  -> todos los usuarios activos
    |   array -> solo esos id_usuario
    |
    | $incluirActor: por defecto quien hizo la acción también recibe
    | el aviso (sirve como registro de lo que hizo y es la forma de
    | verlo cuando una sola persona administra el sistema). Con false
    | solo lo reciben los demás.
    |
    | Devuelve cuántas notificaciones se crearon.
    |
    */

    public function notificar(
        string $categoria,
        string $titulo,
        string $mensaje,
        ?string $url = null,
        ?array $destinatarios = null,
        bool $incluirActor = true
    ): int {
        $definicion = self::CATEGORIAS[$categoria] ?? null;

        if (! $definicion) {
            return 0;
        }

        try {
            $usuarios = $this->usuariosQueAceptan(
                $definicion,
                $destinatarios,
                $incluirActor ? null : auth()->id()
            );

            if ($usuarios === []) {
                return 0;
            }

            $ahora = now();

            $filas = array_map(fn (int $idUsuario): array => [
                'id_usuario' => $idUsuario,
                'tipo' => $definicion['tipo'],
                'titulo' => mb_substr($titulo, 0, 150),
                'mensaje' => mb_substr($mensaje, 0, 1000),
                'url_destino' => $url !== null ? mb_substr($url, 0, 500) : null,
                'leida' => false,
                'fecha_creacion' => $ahora,
                'fecha_lectura' => null,
            ], $usuarios);

            foreach (array_chunk($filas, 200) as $lote) {
                DB::table('notificaciones')->insert($lote);
            }

            /*
             * El layout guarda el contador de la campana 60 s
             * (NotificationsPanel::dispatchUnreadCount); se descarta
             * para que el nuevo aviso aparezca en la siguiente carga.
             */
            foreach ($usuarios as $idUsuario) {
                Cache::forget('notifications.unread.'.$idUsuario);
            }

            $this->enviarPush($usuarios, $titulo, $mensaje, $url, $definicion['tipo']);

            return count($filas);
        } catch (Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Notificación push (teléfono / computadora)
    |--------------------------------------------------------------------------
    |
    | A los mismos destinatarios, en los dispositivos donde activaron
    | «Notificaciones en este dispositivo». Se envía después de responder
    | al navegador, así guardar no tarda más. Ver App\Services\PushService.
    |
    */

    private function enviarPush(array $usuarios, string $titulo, string $mensaje, ?string $url, string $tipo): void
    {
        try {
            if (! app(\App\Services\PushService::class)->disponible()) {
                return;
            }

            dispatch(function () use ($usuarios, $titulo, $mensaje, $url, $tipo): void {
                try {
                    app(\App\Services\PushService::class)->enviarAUsuarios($usuarios, $titulo, $mensaje, $url, strtolower($tipo));
                } catch (Throwable $exception) {
                    report($exception);
                }
            })->afterResponse();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Destinatarios
    |--------------------------------------------------------------------------
    */

    /*
     * Usuarios activos que administran usuarios: los que tienen un
     * permiso activo de los módulos de seguridad/usuarios. Si ningún
     * rol tiene esos permisos configurados, se devuelve null para
     * avisar a todos los usuarios activos.
     */
    public function administradoresDeUsuarios(): ?array
    {
        try {
            $ids = DB::table('usuarios as u')
                ->join('estados_usuario as eu', 'eu.id_estado_usuario', '=', 'u.id_estado_usuario')
                ->join('roles_permisos as rp', 'rp.id_rol', '=', 'u.id_rol')
                ->join('permisos as p', 'p.id_permiso', '=', 'rp.id_permiso')
                ->where('eu.clave', 'ACTIVO')
                ->where('p.activo', true)
                ->where(function ($query) {
                    foreach (self::MODULOS_ADMINISTRACION_USUARIOS as $modulo) {
                        $query->orWhereRaw('LOWER(p.modulo) LIKE ?', [$modulo.'%']);
                    }
                })
                ->distinct()
                ->pluck('u.id_usuario')
                ->map(fn ($id) => (int) $id)
                ->all();

            return $ids !== [] ? $ids : null;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function usuariosQueAceptan(
        array $definicion,
        ?array $destinatarios,
        ?int $excluir
    ): array {
        $columna = $definicion['columna'];
        $defecto = $definicion['defecto'] ? 'TRUE' : 'FALSE';

        $query = DB::table('usuarios as u')
            ->join('estados_usuario as eu', 'eu.id_estado_usuario', '=', 'u.id_estado_usuario')
            ->leftJoin('preferencias_notificaciones as pn', 'pn.id_usuario', '=', 'u.id_usuario')
            ->where('eu.clave', 'ACTIVO')
            ->whereRaw("COALESCE(pn.{$columna}, {$defecto}) = TRUE");

        /*
         * Asignaciones y mantenimientos solo se notifican a quien puede
         * abrir esos módulos (roles Admin y Estándar).
         */
        if (
            ($definicion['soloGestion'] ?? false)
            && \Illuminate\Support\Facades\Schema::hasColumn('roles', 'nivel')
        ) {
            $query->join('roles as rol_notificacion', 'rol_notificacion.id_rol', '=', 'u.id_rol')
                ->where('rol_notificacion.activo', true)
                ->whereIn('rol_notificacion.nivel', ['admin', 'estandar']);
        }

        if ($destinatarios !== null) {
            $destinatarios = array_values(array_unique(array_filter(
                array_map('intval', $destinatarios)
            )));

            if ($destinatarios === []) {
                return [];
            }

            $query->whereIn('u.id_usuario', $destinatarios);
        }

        if ($excluir) {
            $query->where('u.id_usuario', '<>', $excluir);
        }

        return $query
            ->pluck('u.id_usuario')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Apoyo para los mensajes
    |--------------------------------------------------------------------------
    */

    public function nombreActor(): string
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return 'El sistema';
        }

        $nombre = trim(($usuario->nombres ?? '').' '.($usuario->apellido_paterno ?? ''));

        return $nombre !== '' ? $nombre : (string) ($usuario->username ?? 'Un usuario');
    }

    /*
     * "ACT-000082 · Laptop de Roberto"
     */
    public function etiquetaEquipo(int $equipoId): string
    {
        try {
            $equipo = DB::table('equipos')
                ->where('id_equipo', $equipoId)
                ->first(['codigo_inventario', 'nombre_equipo']);
        } catch (Throwable) {
            $equipo = null;
        }

        if (! $equipo) {
            return 'Equipo #'.$equipoId;
        }

        return trim($equipo->codigo_inventario.(filled($equipo->nombre_equipo) ? ' · '.$equipo->nombre_equipo : ''));
    }

    public function estadoDeEquipo(int $equipoId): ?string
    {
        try {
            return DB::table('equipos as e')
                ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
                ->where('e.id_equipo', $equipoId)
                ->value('ee.nombre');
        } catch (Throwable) {
            return null;
        }
    }

    public function nombreCatalogo(?int $idValor): ?string
    {
        if (! $idValor) {
            return null;
        }

        try {
            return DB::table('catalogo_valores')
                ->where('id_valor', $idValor)
                ->value('nombre');
        } catch (Throwable) {
            return null;
        }
    }
}
