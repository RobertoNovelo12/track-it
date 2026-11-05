<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Equipos asignados que quedaron como «Disponible»
|--------------------------------------------------------------------------
|
| Los equipos dados de alta con «Responsable» (formulario o importación)
| creaban su asignación inicial sin cambiar el estado. Con una asignación
| abierta pasan a «Asignado». Solo cambia los que están «Disponible».
|
*/

return new class extends Migration
{
    public function up(): void
    {
        $asignado = DB::table('estados_equipo')->whereRaw('UPPER(clave) = ?', ['ASIGNADO'])->value('id_estado_equipo');
        $disponible = DB::table('estados_equipo')->whereRaw('UPPER(clave) = ?', ['DISPONIBLE'])->value('id_estado_equipo');

        if (! $asignado || ! $disponible) {
            return;
        }

        DB::table('equipos as e')
            ->where('e.id_estado_activo', $disponible)
            ->whereExists(function ($q) {
                $q->selectRaw('1')->from('asignaciones as a')
                    ->whereColumn('a.id_equipo', 'e.id_equipo')
                    ->whereNull('a.fecha_fin');
            })
            ->update(['id_estado_activo' => $asignado]);

        // Inventario, dashboard y movimientos con los estados corregidos.
        try {
            foreach (['equipos', 'dashboard', 'asignaciones'] as $modulo) {
                app(\App\Services\SystemCacheService::class)->refreshModule($modulo);
            }
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        // No se revierte: no se sabe cuáles estaban «Disponible» a propósito.
    }
};
