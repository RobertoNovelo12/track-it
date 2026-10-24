<?php

namespace App\Http\Controllers;

class EquipoController extends Controller
{
    /**
     * Toda la lÃ³gica de filtros, consultas y paginaciÃ³n vive ahora en
     * el componente Livewire App\Livewire\EquiposIndex, que la vista
     * equipos/index.blade.php incluye con <livewire:equipos-index />.
     *
     * Este mÃ©todo solo entrega el "cascarÃ³n" de la pÃ¡gina (encabezado,
     * botones de Exportar/AÃ±adir) â€” no necesita tocar la base de datos.
     */
    public function exportar(int $equipo)
    {
        $fila = \Illuminate\Support\Facades\DB::table('equipos as e')
            ->leftJoin('estados_equipo as estado', 'estado.id_estado_equipo', '=', 'e.id_estado_activo')
            ->where('e.id_equipo', $equipo)->first(['e.id_equipo', 'estado.clave as estado_clave']);
        abort_unless($fila, 404);
        $baja = \Illuminate\Support\Facades\DB::table('bajas')
            ->where('id_equipo', $equipo)->orderByDesc('id_baja')->first();
        if ($baja || strtoupper((string) $fila->estado_clave) === 'BAJA') {
            return app(\App\Services\DescargaActaBajaService::class)->descargar($equipo, $baja);
        }
        $service = app(\App\Services\InventarioDocumentoService::class);
        return $service->descargar($service->preparar(collect([$fila])));
    }

    public function index()
    {
        return view('equipos.index');
    }
}