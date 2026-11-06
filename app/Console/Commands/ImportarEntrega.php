<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * Restaura database/entrega/salida/track-it-entrega.sql en la base del .env.
 * Solo funciona sobre una base vacía (sin tablas en public), para no
 * sobrescribir nunca una base en uso.
 */
class ImportarEntrega extends Command
{
    protected $signature = 'trackit:importar-entrega {archivo? : Ruta del .sql (por defecto database/entrega/salida/track-it-entrega.sql)}';

    protected $description = 'Restaura el script de la base de entrega en una base de datos vacía';

    public function handle(): int
    {
        $archivo = $this->argument('archivo') ?: database_path('entrega/salida/track-it-entrega.sql');
        if (! is_file($archivo)) {
            $this->error("No encontré {$archivo}");

            return self::FAILURE;
        }

        $config = DB::connection()->getConfig();
        $destino = ($config['host'] ?? '?').' / '.($config['database'] ?? '?');

        $tablas = DB::scalar("SELECT COUNT(*) FROM pg_tables WHERE schemaname = 'public'");
        if ($tablas > 0) {
            $this->error("La base {$destino} ya tiene {$tablas} tablas. Usa una base vacía.");

            return self::FAILURE;
        }

        if (! $this->confirm("Se creará la base de Track IT en {$destino}. ¿Continuar?", true)) {
            return self::FAILURE;
        }

        DB::unprepared(file_get_contents($archivo));

        $this->info('Base restaurada. Ejecuta: php artisan migrate  y  php artisan optimize:clear');

        return self::SUCCESS;
    }
}
