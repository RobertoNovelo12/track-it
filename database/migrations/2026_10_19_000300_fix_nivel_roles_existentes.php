<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Corrige la clasificación de roles existentes
|--------------------------------------------------------------------------
|
| La migración anterior solo reconocía nombres que EMPEZABAN con
| "Estándar", por lo que roles como "Usuario Estándar" quedaron sin
| nivel (y se trataban como Lectura).
|
| 1. Clasifica por nombre en cualquier posición (estándar / lectura /
|    consulta) los roles que sigan sin nivel.
| 2. Si ya existía un rol equivalente, desactiva los roles "Estándar"
|    y "Lectura" que creó la migración anterior, siempre que ningún
|    usuario los tenga asignados.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->whereNull('nivel')
            ->where(function ($query) {
                $query->whereRaw("LOWER(nombre) LIKE '%est_ndar%'")
                    ->orWhereRaw("LOWER(nombre) LIKE '%estandar%'");
            })
            ->update(['nivel' => 'estandar']);

        DB::table('roles')
            ->whereNull('nivel')
            ->where(function ($query) {
                $query->whereRaw("LOWER(nombre) LIKE '%lectura%'")
                    ->orWhereRaw("LOWER(nombre) LIKE '%consulta%'");
            })
            ->update(['nivel' => 'lectura']);

        $creados = [
            'estandar' => 'Estándar',
            'lectura' => 'Lectura',
        ];

        foreach ($creados as $nivel => $nombre) {
            $hayOtro = DB::table('roles')
                ->where('nivel', $nivel)
                ->where('nombre', '<>', $nombre)
                ->where('activo', true)
                ->exists();

            if (! $hayOtro) {
                continue;
            }

            DB::table('roles as r')
                ->where('r.nivel', $nivel)
                ->where('r.nombre', $nombre)
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('usuarios as u')
                        ->whereColumn('u.id_rol', 'r.id_rol');
                })
                ->update(['activo' => false]);
        }
    }

    public function down(): void
    {
        // Solo corrige datos; no hay cambios de estructura que revertir.
    }
};
