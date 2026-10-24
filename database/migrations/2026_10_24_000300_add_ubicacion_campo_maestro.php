<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Campo maestro «Ubicación» para tipos de equipo
|--------------------------------------------------------------------------
|
| Registra id_ubicacion como campo de sistema en campos_equipo.
| Cada tipo de equipo puede agregarlo voluntariamente desde «Campos
| disponibles» en el constructor de formularios; no es obligatorio.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        DB::table('campos_equipo')->insertOrIgnore([
            'clave'        => 'id_ubicacion',
            'nombre'       => 'Ubicación',
            'tipo_control' => 'select',
            'es_sistema'   => true,
            'activo'       => true,
            'descripcion'  => null,
            'placeholder'  => null,
        ]);

        // Asegurarse de que esté activo si ya existía con activo = false.
        DB::table('campos_equipo')
            ->where('clave', 'id_ubicacion')
            ->update([
                'es_sistema' => true,
                'activo'     => true,
            ]);
    }

    public function down(): void
    {
        // Solo lo desactivamos; no lo borramos para no romper relaciones.
        DB::table('campos_equipo')
            ->where('clave', 'id_ubicacion')
            ->update(['activo' => false]);
    }
};
