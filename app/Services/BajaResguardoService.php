<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BajaResguardoService
{
    public function guardar(int $equipoId, string $fecha, string $motivo, ?string $observaciones, int $usuarioId): void
    {
        DB::transaction(function () use ($equipoId, $fecha, $motivo, $observaciones, $usuarioId): void {
            $equipo = DB::table('equipos')->where('id_equipo', $equipoId)->lockForUpdate()->first();
            if (!$equipo) { throw ValidationException::withMessages(['motivoBaja' => 'El equipo ya no existe.']); }
            $baja = DB::table('estados_equipo')->where('id_estado_equipo', $equipo->id_estado_activo)->where('clave', 'BAJA')->exists();
            if ($baja || DB::table('bajas')->where('id_equipo', $equipoId)->exists()) {
                throw ValidationException::withMessages(['motivoBaja' => 'La baja ya está completada; el equipo no puede volver a resguardo.']);
            }
            if (DB::table('bajas_resguardo')->where('id_equipo', $equipoId)->exists()) {
                throw ValidationException::withMessages(['motivoBaja' => 'Este equipo ya está en resguardo. Revisa y completa su baja cuando corresponda.']);
            }
            DB::table('bajas_resguardo')->insert(['id_equipo' => $equipoId, 'fecha_resguardo' => $fecha,
                'motivo' => trim($motivo), 'observaciones' => trim((string) $observaciones) ?: null, 'registrado_por' => $usuarioId]);
            DB::table('bitacora_auditoria')->insert(['id_usuario'=>$usuarioId,'accion'=>'EQUIPO_EN_RESGUARDO','modulo'=>'EQUIPOS',
                'descripcion'=>'Equipo '.$equipo->codigo_inventario.' pendiente de revisión para baja: '.trim($motivo),'fecha_hora'=>now()]);
        });
    }

    /** Dentro de la transacción que registra la baja definitiva. */
    public function completar(int $equipoId): void
    {
        // Compatibilidad con instalaciones que aún no aplican la migración.
        if (!Schema::hasTable('bajas_resguardo')) { return; }
        DB::table('bajas_resguardo')->where('id_equipo', $equipoId)->whereNull('fecha_completada')->update(['fecha_completada'=>now()]);
    }

    public function pendiente(int $equipoId): ?object
    {
        if (!Schema::hasTable('bajas_resguardo')) { return null; }
        return DB::table('bajas_resguardo')->where('id_equipo', $equipoId)->whereNull('fecha_completada')->first();
    }
}
