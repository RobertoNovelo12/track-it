<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Antes: 3 consultas separadas = 3 viajes de red a Supabase.
        // Ahora: 1 sola consulta con subconsultas = 1 solo viaje de red.
        $row = DB::selectOne('
            SELECT
                (SELECT COUNT(*) FROM equipos) AS total_activos,
                (SELECT COUNT(DISTINCT id_equipo) FROM asignaciones WHERE fecha_fin IS NULL) AS equipos_asignados,
                (SELECT COUNT(DISTINCT id_equipo) FROM mantenimientos WHERE hora_fin IS NULL) AS en_mantenimiento
        ');

        $totalActivos = (int) $row->total_activos;
        $equiposAsignados = (int) $row->equipos_asignados;
        $enMantenimiento = (int) $row->en_mantenimiento;
        $stockDisponible = max($totalActivos - $equiposAsignados - $enMantenimiento, 0);

        return view('dashboard', [
            'totalActivos' => $totalActivos,
            'equiposAsignados' => $equiposAsignados,
            'enMantenimiento' => $enMantenimiento,
            'stockDisponible' => $stockDisponible,
            'stats' => [
                'total_activos' => $totalActivos,
                'equipos_asignados' => $equiposAsignados,
                'en_mantenimiento' => $enMantenimiento,
                'stock_disponible' => $stockDisponible,
            ],
        ]);
    }
}