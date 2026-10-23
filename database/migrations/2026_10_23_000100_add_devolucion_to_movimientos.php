<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Devolución de equipos (desasignación)
|--------------------------------------------------------------------------
|
| Una devolución cierra la asignación activa sin abrir otra:
| - movimientos.id_asignacion_nueva admite NULL.
| - movimientos.recibido_por: quién recibió el equipo de vuelta.
| - Tipo de movimiento DEVOLUCION en el catálogo tipo_movimiento.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('movimientos')) {
            if (Schema::hasColumn('movimientos', 'id_asignacion_nueva')) {
                DB::statement('ALTER TABLE movimientos ALTER COLUMN id_asignacion_nueva DROP NOT NULL');
            }

            if (! Schema::hasColumn('movimientos', 'recibido_por')) {
                Schema::table('movimientos', function (Blueprint $table) {
                    $table->string('recibido_por', 255)->nullable();
                });
            }
        }

        $idCatalogo = DB::table('catalogos')
            ->whereRaw('LOWER(clave) = ?', ['tipo_movimiento'])
            ->value('id_catalogo');

        if (! $idCatalogo) {
            return;
        }

        $existe = DB::table('catalogo_valores')
            ->where('id_catalogo', $idCatalogo)
            ->whereRaw('LOWER(clave) = ?', ['devolucion'])
            ->exists();

        if (! $existe) {
            DB::table('catalogo_valores')->insert([
                'id_catalogo' => $idCatalogo,
                'clave' => 'DEVOLUCION',
                'nombre' => 'Devolución',
                'descripcion' => 'El responsable devuelve el equipo; queda disponible para asignarse.',
                'orden' => (int) DB::table('catalogo_valores')->where('id_catalogo', $idCatalogo)->max('orden') + 1,
                'activo' => true,
            ]);
        }
    }

    public function down(): void
    {
        $idCatalogo = DB::table('catalogos')
            ->whereRaw('LOWER(clave) = ?', ['tipo_movimiento'])
            ->value('id_catalogo');

        if ($idCatalogo) {
            $idValor = DB::table('catalogo_valores')
                ->where('id_catalogo', $idCatalogo)
                ->whereRaw('LOWER(clave) = ?', ['devolucion'])
                ->value('id_valor');

            // Solo se quita si no se ha usado.
            if ($idValor && ! DB::table('movimientos')->where('id_tipo_movimiento', $idValor)->exists()) {
                DB::table('catalogo_valores')->where('id_valor', $idValor)->delete();
            }
        }

        if (Schema::hasTable('movimientos') && Schema::hasColumn('movimientos', 'recibido_por')) {
            Schema::table('movimientos', function (Blueprint $table) {
                $table->dropColumn('recibido_por');
            });
        }
    }
};
