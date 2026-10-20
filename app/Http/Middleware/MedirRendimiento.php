<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Medición de rendimiento (SOLO EN LOCAL, TEMPORAL)
|--------------------------------------------------------------------------
|
| Agrega la cabecera Server-Timing (visible en Chrome › Network › Timing)
| y escribe en storage/logs/rendimiento.log el tiempo total, el número de
| consultas y las más lentas de cada petición. No hace nada fuera de
| APP_ENV=local. Se puede quitar al terminar el diagnóstico.
|
*/

class MedirRendimiento
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isLocal()) {
            return $next($request);
        }

        $inicio = hrtime(true);
        $consultas = [];

        DB::listen(function ($query) use (&$consultas): void {
            $consultas[] = [
                'ms' => (float) $query->time,
                'sql' => preg_replace('/\s+/', ' ', (string) $query->sql),
            ];
        });

        $response = $next($request);

        $totalMs = (hrtime(true) - $inicio) / 1e6;
        $dbMs = array_sum(array_column($consultas, 'ms'));

        $response->headers->set(
            'Server-Timing',
            sprintf(
                'app;dur=%.1f;desc="Servidor", db;dur=%.1f;desc="%d consultas"',
                $totalMs,
                $dbMs,
                count($consultas)
            )
        );

        // Componentes Livewire de la petición (para saber qué se cargó).
        $componentes = collect((array) $request->input('components', []))
            ->map(fn ($c) => data_get(json_decode((string) data_get($c, 'snapshot', '{}'), true), 'memo.name'))
            ->filter()
            ->implode(', ');

        usort($consultas, fn ($a, $b) => $b['ms'] <=> $a['ms']);

        $lineas = [sprintf(
            '[%s] %s /%s%s | total %.0f ms | %d consultas, %.0f ms en BD',
            now()->format('H:i:s'),
            $request->method(),
            ltrim($request->path(), '/'),
            $componentes !== '' ? ' (' . $componentes . ')' : '',
            $totalMs,
            count($consultas),
            $dbMs
        )];

        foreach (array_slice($consultas, 0, 8) as $consulta) {
            $lineas[] = sprintf('    %7.1f ms  %s', $consulta['ms'], mb_substr($consulta['sql'], 0, 220));
        }

        @file_put_contents(
            storage_path('logs/rendimiento.log'),
            implode(PHP_EOL, $lineas) . PHP_EOL,
            FILE_APPEND
        );

        return $response;
    }
}
