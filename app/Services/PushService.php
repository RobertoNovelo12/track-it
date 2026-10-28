<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/*
|--------------------------------------------------------------------------
| Notificaciones push (Web Push)
|--------------------------------------------------------------------------
|
| Envía al teléfono / computadora las mismas notificaciones que se
| guardan en la tabla notificaciones (NotificacionService::notificar).
| Las recibe el service worker (public/sw.js) aunque la página esté
| cerrada.
|
| Requiere: paquete minishlink/web-push, llaves VAPID en el .env
| (config/webpush.php) y la tabla suscripciones_push. Si falta algo,
| disponible() devuelve false y el resto del sistema sigue igual.
|
*/

class PushService
{
    private ?bool $disponible = null;

    public function disponible(): bool
    {
        if ($this->disponible !== null) {
            return $this->disponible;
        }

        try {
            return $this->disponible = class_exists(\Minishlink\WebPush\WebPush::class)
                && filled(config('webpush.public_key'))
                && filled(config('webpush.private_key'))
                && Schema::hasTable('suscripciones_push');
        } catch (Throwable $exception) {
            report($exception);

            return $this->disponible = false;
        }
    }

    public function llavePublica(): ?string
    {
        return filled(config('webpush.public_key'))
            ? (string) config('webpush.public_key')
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Suscripciones
    |--------------------------------------------------------------------------
    */

    public function guardar(int $idUsuario, string $endpoint, string $p256dh, string $auth, ?string $dispositivo): void
    {
        $hash = hash('sha256', $endpoint);

        $datos = [
            'id_usuario' => $idUsuario,
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth,
            'dispositivo' => $dispositivo !== null ? mb_substr($dispositivo, 0, 255) : null,
        ];

        $existe = DB::table('suscripciones_push')->where('endpoint_hash', $hash)->exists();

        if ($existe) {
            // El mismo navegador pudo cambiar de usuario: queda con el actual.
            DB::table('suscripciones_push')->where('endpoint_hash', $hash)->update($datos);

            return;
        }

        DB::table('suscripciones_push')->insert($datos + [
            'endpoint_hash' => $hash,
            'fecha_registro' => now(),
        ]);
    }

    public function eliminar(int $idUsuario, string $endpoint): void
    {
        DB::table('suscripciones_push')
            ->where('id_usuario', $idUsuario)
            ->where('endpoint_hash', hash('sha256', $endpoint))
            ->delete();
    }

    public function cantidadDispositivos(int $idUsuario): int
    {
        if (! $this->disponible()) {
            return 0;
        }

        return DB::table('suscripciones_push')->where('id_usuario', $idUsuario)->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Envío
    |--------------------------------------------------------------------------
    |
    | $url: ruta relativa (/equipos/5) o absoluta; al tocar la
    | notificación se abre esa pantalla.
    | Devuelve cuántos dispositivos la recibieron.
    |
    */

    public function enviarAUsuarios(array $idsUsuarios, string $titulo, string $mensaje, ?string $url = null, ?string $etiqueta = null): int
    {
        if (! $this->disponible() || $idsUsuarios === []) {
            return 0;
        }

        $suscripciones = DB::table('suscripciones_push')
            ->whereIn('id_usuario', array_values(array_unique(array_map('intval', $idsUsuarios))))
            ->get(['id_suscripcion', 'endpoint', 'endpoint_hash', 'p256dh', 'auth']);

        if ($suscripciones->isEmpty()) {
            return 0;
        }

        $webPush = new \Minishlink\WebPush\WebPush(
            [
                'VAPID' => [
                    'subject' => (string) config('webpush.subject'),
                    'publicKey' => (string) config('webpush.public_key'),
                    'privateKey' => (string) config('webpush.private_key'),
                ],
            ],
            [
                'TTL' => 60 * 60 * 24,
                'urgency' => 'normal',
            ],
            // Cliente HTTP (PSR-18) con tiempo límite: un servicio de push lento no frena el envío.
            new \GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5])
        );

        $webPush->setReuseVAPIDHeaders(true);

        $contenido = json_encode([
            'title' => mb_substr($titulo, 0, 120),
            'body' => mb_substr($mensaje, 0, 300),
            'url' => $url ?: '/dashboard',
            'tag' => $etiqueta,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $porEndpoint = [];

        foreach ($suscripciones as $suscripcion) {
            $porEndpoint[$suscripcion->endpoint] = $suscripcion;

            $webPush->queueNotification(
                \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $suscripcion->endpoint,
                    'publicKey' => $suscripcion->p256dh,
                    'authToken' => $suscripcion->auth,
                    'contentEncoding' => 'aes128gcm',
                ]),
                $contenido
            );
        }

        $enviados = [];
        $vencidos = [];

        foreach ($webPush->flush() as $reporte) {
            $endpoint = $reporte->getEndpoint();
            $fila = $porEndpoint[$endpoint] ?? null;

            if (! $fila) {
                continue;
            }

            if ($reporte->isSuccess()) {
                $enviados[] = $fila->id_suscripcion;
            } elseif ($reporte->isSubscriptionExpired()) {
                // El usuario quitó el permiso o desinstaló: se borra.
                $vencidos[] = $fila->id_suscripcion;
            } else {
                report(new \RuntimeException('Push no enviado: ' . $reporte->getReason()));
            }
        }

        if ($enviados !== []) {
            DB::table('suscripciones_push')->whereIn('id_suscripcion', $enviados)->update(['fecha_ultimo_envio' => now()]);
        }

        if ($vencidos !== []) {
            DB::table('suscripciones_push')->whereIn('id_suscripcion', $vencidos)->delete();
        }

        return count($enviados);
    }
}
