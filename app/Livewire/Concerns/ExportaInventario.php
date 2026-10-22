<?php

namespace App\Livewire\Concerns;

use App\Exports\InventarioEquiposExport;
use App\Services\InventarioDocumentoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Exportación independiente del listado. Reutiliza exactamente las consultas
 * privadas y los filtros que ya usa EquiposIndex; no altera el inventario.
 */
trait ExportaInventario
{
    #[On('inventario-exportar')]
    public function exportarInventario(string $formato = 'pdf', array $seleccionados = [])
    {
        abort_unless(auth()->check(), 403);
        abort_unless(in_array($formato, ['pdf', 'xlsx'], true), 422);

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

        if ($filas->isEmpty()) {
            $this->dispatch('inventario-exportacion-vacia');
            return null;
        }

        $equipos = app(InventarioDocumentoService::class)->preparar($filas);
        return app(InventarioDocumentoService::class)->descargar($equipos, $formato);
    }
}
