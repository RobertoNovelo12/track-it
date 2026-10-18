<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Esta migración de transición requiere PostgreSQL.');
        }
        if (DB::table('configuracion_instalacion')->where('id', 1)->value('plantilla') !== 'hoteles') {
            throw new RuntimeException('Esta transición corresponde a la instalación hotelera existente.');
        }
        foreach (['unidades_organizativas', 'departamento_unidad'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException("Ya existe {$table}; revisa su contenido antes de migrar.");
            }
        }
        if (DB::table('campos_equipo')->where('clave', 'id_unidad')->exists()) {
            throw new RuntimeException('Ya existe un campo id_unidad. Debe revisarse antes de convertir id_hotel.');
        }

        DB::statement('ALTER TABLE hoteles RENAME TO unidades_organizativas');
        DB::statement('ALTER TABLE unidades_organizativas RENAME COLUMN id_hotel TO id_unidad');
        DB::statement('ALTER TABLE departamento_hotel RENAME TO departamento_unidad');
        DB::statement('ALTER TABLE departamento_unidad RENAME COLUMN id_hotel TO id_unidad');
        DB::statement('ALTER TABLE equipos RENAME COLUMN id_hotel TO id_unidad');
        DB::statement('ALTER TABLE asignaciones RENAME COLUMN id_hotel TO id_unidad');
        DB::statement('ALTER TABLE departamentos RENAME COLUMN atiende_todos_hoteles TO atiende_todas_unidades');

        // Los renombramientos mantienen filas, identificadores y claves foráneas.
        DB::table('campos_equipo')->where('clave', 'id_hotel')->update(['clave' => 'id_unidad']);
        DB::table('tipo_equipo_campos')->where('tabla_destino', 'hoteles')
            ->update(['tabla_destino' => 'unidades_organizativas']);
        DB::table('tipo_equipo_campos')->where('fuente_opciones', 'hoteles')
            ->update(['fuente_opciones' => 'unidades_organizativas']);
        DB::table('tipo_equipo_campos')->where('columna_destino', 'id_hotel')
            ->whereIn('tabla_destino', ['equipos', 'asignaciones', 'unidades_organizativas'])
            ->update(['columna_destino' => 'id_unidad']);
    }

    public function down(): void
    {
        // Restaurar también el código anterior al revertir esta migración.
        DB::table('campos_equipo')->where('clave', 'id_unidad')->update(['clave' => 'id_hotel']);
        DB::table('tipo_equipo_campos')->where('columna_destino', 'id_unidad')
            ->whereIn('tabla_destino', ['equipos', 'asignaciones', 'unidades_organizativas'])
            ->update(['columna_destino' => 'id_hotel']);
        DB::table('tipo_equipo_campos')->where('tabla_destino', 'unidades_organizativas')
            ->update(['tabla_destino' => 'hoteles']);
        DB::table('tipo_equipo_campos')->where('fuente_opciones', 'unidades_organizativas')
            ->update(['fuente_opciones' => 'hoteles']);

        DB::statement('ALTER TABLE departamentos RENAME COLUMN atiende_todas_unidades TO atiende_todos_hoteles');
        DB::statement('ALTER TABLE asignaciones RENAME COLUMN id_unidad TO id_hotel');
        DB::statement('ALTER TABLE equipos RENAME COLUMN id_unidad TO id_hotel');
        DB::statement('ALTER TABLE departamento_unidad RENAME COLUMN id_unidad TO id_hotel');
        DB::statement('ALTER TABLE departamento_unidad RENAME TO departamento_hotel');
        DB::statement('ALTER TABLE unidades_organizativas RENAME COLUMN id_unidad TO id_hotel');
        DB::statement('ALTER TABLE unidades_organizativas RENAME TO hoteles');
    }
};
