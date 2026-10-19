<?php

namespace App\Console\Commands;

use App\Services\NotificacionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Avisos de mantenimientos próximos y vencidos
|--------------------------------------------------------------------------
|
| Se ejecuta una vez al día (routes/console.php). Toma, por cada equipo
| vigente, su mantenimiento más reciente con fecha_proximo_mantenimiento
| y avisa (preferencia "Mantenimientos"):
|
|   - Próximo: la fecha cae dentro de los siguientes N días.
|   - Vencido: la fecha ya pasó.
|
| Cada aviso se envía una sola vez por mantenimiento: si ya existe una
| notificación con el mismo título y enlace, no se repite. Al registrar
| un mantenimiento nuevo para el equipo, ese pasa a ser el más reciente
| y los avisos del anterior dejan de generarse.
|
| Uso manual: php artisan notificaciones:mantenimientos [--dias=7]
|
*/

class NotificarMantenimientosProgramados extends Command
{
    protected $signature = 'notificaciones:mantenimientos
                            {--dias=7 : Días de anticipación para el aviso de próximo mantenimiento}';

    protected $description = 'Notifica los mantenimientos próximos y vencidos según fecha_proximo_mantenimiento';

    public function handle(NotificacionService $notificaciones): int
    {
        $dias = max(1, (int) $this->option('dias'));

        $hoy = Carbon::today();
        $limite = $hoy->copy()->addDays($dias);

        $pendientes = $this->ultimosMantenimientosProgramados()
            ->whereDate('m.fecha_proximo_mantenimiento', '<=', $limite->toDateString())
            ->orderBy('m.fecha_proximo_mantenimiento')
            ->get();

        $proximos = 0;
        $vencidos = 0;

        foreach ($pendientes as $mantenimiento) {
            $fecha = Carbon::parse($mantenimiento->fecha_proximo_mantenimiento)->startOfDay();
            $vencido = $fecha->lt($hoy);

            $folio = $mantenimiento->folio ?: '#'.$mantenimiento->id_mantenimiento;
            $titulo = ($vencido ? 'Mantenimiento vencido · ' : 'Mantenimiento próximo · ').$folio;
            $url = route('mantenimientos.edit', (int) $mantenimiento->id_mantenimiento, false);

            if ($this->yaNotificado($titulo, $url)) {
                continue;
            }

            $equipo = trim(
                $mantenimiento->codigo_inventario
                .(filled($mantenimiento->nombre_equipo) ? ' · '.$mantenimiento->nombre_equipo : '')
            );

            $mensaje = $vencido
                ? 'El mantenimiento de '.$equipo.' estaba programado para el '
                    .$fecha->format('d/m/Y').' ('.$this->diasTexto($fecha->diffInDays($hoy)).' de retraso).'
                : 'El equipo '.$equipo.' tiene mantenimiento programado para el '
                    .$fecha->format('d/m/Y')
                    .($fecha->equalTo($hoy) ? ' (hoy).' : ' (en '.$this->diasTexto($hoy->diffInDays($fecha)).').');

            $enviadas = $notificaciones->notificar(
                'mantenimientos',
                $titulo,
                $mensaje,
                $url
            );

            if ($enviadas > 0) {
                $vencido ? $vencidos++ : $proximos++;
            }
        }

        $this->info("Avisos enviados: {$proximos} próximos, {$vencidos} vencidos.");

        return self::SUCCESS;
    }

    /*
     * Mantenimiento más reciente de cada equipo vigente (no dado de
     * baja) que tiene fecha de próximo mantenimiento y no fue cancelado.
     */
    private function ultimosMantenimientosProgramados()
    {
        $ultimos = DB::table('mantenimientos')
            ->selectRaw('MAX(id_mantenimiento) AS id_mantenimiento')
            ->groupBy('id_equipo');

        return DB::table('mantenimientos as m')
            ->joinSub($ultimos, 'ultimo', 'ultimo.id_mantenimiento', '=', 'm.id_mantenimiento')
            ->join('equipos as e', 'e.id_equipo', '=', 'm.id_equipo')
            ->leftJoin('catalogo_valores as cv', 'cv.id_valor', '=', 'm.id_estado_mantenimiento')
            ->whereNotNull('m.fecha_proximo_mantenimiento')
            ->where(function ($query) {
                $query->whereNull('cv.clave')
                    ->orWhere('cv.clave', '<>', 'CANCELADO');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('bajas as b')
                    ->whereColumn('b.id_equipo', 'e.id_equipo');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('estados_equipo as ee')
                    ->whereColumn('ee.id_estado_equipo', 'e.id_estado_activo')
                    ->where('ee.clave', 'BAJA');
            })
            ->select([
                'm.id_mantenimiento',
                'm.folio',
                'm.fecha_proximo_mantenimiento',
                'e.codigo_inventario',
                'e.nombre_equipo',
            ]);
    }

    private function yaNotificado(string $titulo, string $url): bool
    {
        return DB::table('notificaciones')
            ->where('tipo', NotificacionService::CATEGORIAS['mantenimientos']['tipo'])
            ->where('titulo', $titulo)
            ->where('url_destino', $url)
            ->exists();
    }

    private function diasTexto(int|float $dias): string
    {
        $dias = (int) round(abs($dias));

        return $dias === 1 ? '1 día' : $dias.' días';
    }
}
