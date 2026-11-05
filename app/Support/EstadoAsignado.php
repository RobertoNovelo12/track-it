<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Estado «Asignado» según las asignaciones
|--------------------------------------------------------------------------
|
| Un equipo con una asignación abierta (sin fecha_fin) no puede quedar
| como «Disponible»: pasa a «Asignado». Los demás estados (En
| mantenimiento, Baja…) se respetan. Lo usan el alta (asignación
| inicial por «Responsable», también al importar), la edición y la
| migración que corrige los equipos que ya estaban así.
|
*/

class EstadoAsignado
{
    private static array $ids = [];

    private static function id(string $clave): ?int
    {
        if (! array_key_exists($clave, self::$ids)) {
            $id = DB::table('estados_equipo')->whereRaw('UPPER(clave) = ?', [$clave])->value('id_estado_equipo');
            self::$ids[$clave] = $id !== null ? (int) $id : null;
        }

        return self::$ids[$clave];
    }

    // Uno o varios equipos; devuelve cuántos se corrigieron.
    public static function sincronizar(int|array $idsEquipo): int
    {
        $asignado = self::id('ASIGNADO');
        $disponible = self::id('DISPONIBLE');
        $ids = array_values(array_filter(array_map('intval', (array) $idsEquipo)));

        if ($asignado === null || $disponible === null || $ids === []) {
            return 0;
        }

        return DB::table('equipos as e')
            ->whereIn('e.id_equipo', $ids)
            ->where('e.id_estado_activo', $disponible)
            ->whereExists(function ($q) {
                $q->selectRaw('1')->from('asignaciones as a')
                    ->whereColumn('a.id_equipo', 'e.id_equipo')
                    ->whereNull('a.fecha_fin');
            })
            ->update(['id_estado_activo' => $asignado]);
    }
}
