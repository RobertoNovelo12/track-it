<?php

namespace App\Livewire\Concerns;

use App\Exports\InventarioEquiposExport;
use App\Services\InventarioDocumentoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Exportación independiente del listado. Reutiliza exactamente las consultas
 * privadas y los filtros que ya usa EquiposIndex; no altera el inventario.
 */
trait ExportaInventario
{
    /*
     * Renderless: generar el archivo no cambia la tabla, así que no se
     * vuelve a dibujar el listado (antes parecía que se recargaba).
     */
    #[On('inventario-exportar')]
    #[Renderless]
    public function exportarInventario(string $formato = 'pdf', array $seleccionados = [])
    {
        abort_unless(auth()->check(), 403);
        abort_unless(in_array($formato, ['pdf', 'xlsx'], true), 422);

        // Diagnóstico temporal (solo con APP_DEBUG): tiempos por fase en storage/logs/exportar.log.
        $medir = (bool) config('app.debug');
        $t0 = microtime(true);
        $consultas = [];
        if ($medir) {
            \Illuminate\Support\Facades\DB::listen(function ($q) use (&$consultas): void {
                $consultas[] = [round($q->time), mb_substr(preg_replace('/\s+/', ' ', $q->sql), 0, 140)];
            });
        }
        $marcas = [];
        $marcar = function (string $fase) use (&$marcas, &$consultas, $t0): void {
            $marcas[] = [$fase, round((microtime(true) - $t0) * 1000), count($consultas)];
        };

        $ids = collect($seleccionados)
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $query = $this->equiposQuery();
        $this->applySearch($query);
        $this->applyFilters($query);
        $this->applySorting($query);

        if ($ids !== []) {
            $query->whereIn('e.id_equipo', $ids);
        }

        // Sin paginate(): incluye también todas las páginas filtradas.
        $filas = $query->get();
        $marcar('consulta del listado');

        if ($filas->isEmpty()) {
            $this->dispatch('inventario-exportacion-vacia');
            return null;
        }

        $equipos = app(InventarioDocumentoService::class)->preparar($filas);
        $marcar('preparar datos');

        $respuesta = app(InventarioDocumentoService::class)->descargar($equipos, $formato);

        if ($medir) {
            // El PDF se genera dentro de la respuesta: se mide aquí forzándolo.
            if ($respuesta instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                ob_start();
                $respuesta->sendContent();
                $contenido = ob_get_clean();
                $marcar('generar '.$formato.' ('.round(strlen($contenido) / 1024).' KB)');
                // Livewire solo descarga StreamedResponse: se vuelve a envolver.
                $cabeceras = $respuesta->headers->all();
                $respuesta = new \Symfony\Component\HttpFoundation\StreamedResponse(
                    static function () use ($contenido): void { echo $contenido; },
                    200,
                    $cabeceras
                );
            }

            usort($consultas, fn ($a, $b) => $b[0] <=> $a[0]);
            $texto = '['.now()->format('Y-m-d H:i:s').'] '.count($filas).' equipo(s), '.$formato."\n";
            foreach ($marcas as [$fase, $ms, $n]) {
                $texto .= sprintf("   %6d ms  %-28s %d consultas acumuladas\n", $ms, $fase, $n);
            }
            foreach (array_slice($consultas, 0, 10) as [$ms, $sql]) {
                $texto .= sprintf("      %5d ms  %s\n", $ms, $sql);
            }
            @file_put_contents(storage_path('logs/exportar.log'), $texto, FILE_APPEND);
        }

        return $respuesta;
    }
}
