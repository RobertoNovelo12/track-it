<?php

namespace App\Http\Controllers;

class EquipoController extends Controller
{
    /**
     * Toda la lógica de filtros, consultas y paginación vive ahora en
     * el componente Livewire App\Livewire\EquiposIndex, que la vista
     * equipos/index.blade.php incluye con <livewire:equipos-index />.
     *
     * Este método solo entrega el "cascarón" de la página (encabezado,
     * botones de Exportar/Añadir) — no necesita tocar la base de datos.
     */
    public function exportar(int $equipo)
    {
        $fila = \Illuminate\Support\Facades\DB::table('equipos')->where('id_equipo', $equipo)->first(['id_equipo']);
        abort_unless($fila, 404);
        $service = app(\App\Services\InventarioDocumentoService::class);
        return $service->descargar($service->preparar(collect([$fila])));
    }

    public function index()
    {
        return view('equipos.index');
    }
}