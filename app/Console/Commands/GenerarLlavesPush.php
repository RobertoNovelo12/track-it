<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/*
|--------------------------------------------------------------------------
| php artisan trackit:llaves-push
|--------------------------------------------------------------------------
|
| Genera el par de llaves VAPID para las notificaciones push. Se ejecuta
| UNA sola vez: si cambian, los dispositivos ya activados dejan de
| recibir avisos y hay que volver a activarlos. Usa las mismas llaves
| en local y en Render.
|
*/

class GenerarLlavesPush extends Command
{
    protected $signature = 'trackit:llaves-push';

    protected $description = 'Genera las llaves VAPID para las notificaciones push (copiar al .env)';

    public function handle(): int
    {
        if (! class_exists(\Minishlink\WebPush\VAPID::class)) {
            $this->error('Falta el paquete: composer require minishlink/web-push');

            return self::FAILURE;
        }

        if (filled(config('webpush.public_key'))) {
            $this->warn('Ya hay llaves en el .env. Si las cambias, los dispositivos activados dejarán de recibir avisos.');

            if (! $this->confirm('¿Generar unas nuevas de todas formas?', false)) {
                return self::SUCCESS;
            }
        }

        try {
            $llaves = \Minishlink\WebPush\VAPID::createVapidKeys();
        } catch (\Throwable) {
            /*
             * En Windows, el OpenSSL de PHP no encuentra openssl.cnf
             * («Unable to create the key»): se generan con una
             * configuración mínima temporal.
             */
            $llaves = $this->generarConConfiguracionTemporal();

            if ($llaves === null) {
                $this->error('OpenSSL no pudo crear las llaves. Configura OPENSSL_CONF (variable de entorno con la ruta de openssl.cnf).');

                return self::FAILURE;
            }
        }

        $this->info('Agrega estas líneas al .env (y como variables de entorno en Render):');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $llaves['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $llaves['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:tu-correo@empresa.com');
        $this->newLine();
        $this->comment('La llave privada no debe compartirse ni subirse al repositorio.');

        return self::SUCCESS;
    }

    /*
     * Llaves VAPID (curva P-256) en base64url:
     * pública = 0x04 || X || Y (65 bytes), privada = D (32 bytes).
     */
    private function generarConConfiguracionTemporal(): ?array
    {
        $configuracion = tempnam(sys_get_temp_dir(), 'vapid');

        file_put_contents($configuracion, "[ req ]\ndistinguished_name = req_dn\n[ req_dn ]\n");

        try {
            $llave = openssl_pkey_new([
                'config' => $configuracion,
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'prime256v1',
                // Algunas versiones de PHP lo exigen aunque las llaves EC no lo usen.
                'private_key_bits' => 2048,
            ]);

            if ($llave === false) {
                return null;
            }

            $detalles = openssl_pkey_get_details($llave)['ec'] ?? null;

            if (! $detalles) {
                return null;
            }

            $relleno = fn (string $valor): string => str_pad($valor, 32, "\0", STR_PAD_LEFT);
            $base64url = fn (string $valor): string => rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');

            return [
                'publicKey' => $base64url("\x04" . $relleno($detalles['x']) . $relleno($detalles['y'])),
                'privateKey' => $base64url($relleno($detalles['d'])),
            ];
        } finally {
            @unlink($configuracion);
        }
    }
}
