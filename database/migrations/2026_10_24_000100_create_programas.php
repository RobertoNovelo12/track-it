<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Programas (software)
|--------------------------------------------------------------------------
|
| - programas: catálogo (Administración › Programas).
| - tipos_equipo.incluye_programas: el tipo (Laptop, PC, Móvil…) lleva
|   la sección de programas al dar de alta / editar sus equipos.
| - tipo_equipo_programa: programas predeterminados del tipo (vienen
|   marcados al dar de alta un equipo de ese tipo).
| - equipo_programa: programas instalados en cada equipo.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('programas')) {
            Schema::create('programas', function (Blueprint $table) {
                $table->id('id_programa');
                $table->string('nombre', 150);
                $table->string('empresa', 150)->nullable();
                $table->string('version', 50)->nullable();
                // Windows, macOS, Linux, Android, iOS, Web, Multiplataforma.
                $table->string('plataforma', 30)->nullable();
                $table->string('categoria', 60)->nullable();
                // gratuita | pago | suscripcion
                $table->string('licencia', 20)->default('gratuita');
                $table->boolean('requiere_clave')->default(false);
                $table->string('enlace_descarga', 500)->nullable();
                // Peso del instalador en MB (se captura en MB o GB).
                $table->decimal('peso_mb', 12, 2)->nullable();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_registro')->useCurrent();
                $table->timestamp('fecha_actualizacion')->nullable();

                $table->index(['activo', 'nombre']);
            });
        }

        if (Schema::hasTable('tipos_equipo') && ! Schema::hasColumn('tipos_equipo', 'incluye_programas')) {
            Schema::table('tipos_equipo', function (Blueprint $table) {
                $table->boolean('incluye_programas')->default(false);
            });
        }

        if (! Schema::hasTable('tipo_equipo_programa')) {
            Schema::create('tipo_equipo_programa', function (Blueprint $table) {
                $table->smallInteger('id_tipo_equipo');
                $table->unsignedBigInteger('id_programa');

                $table->primary(['id_tipo_equipo', 'id_programa']);

                $table->foreign('id_tipo_equipo', 'fk_tipo_equipo_programa_tipo')
                    ->references('id_tipo_equipo')->on('tipos_equipo')->cascadeOnDelete();
                $table->foreign('id_programa', 'fk_tipo_equipo_programa_programa')
                    ->references('id_programa')->on('programas')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('equipo_programa')) {
            Schema::create('equipo_programa', function (Blueprint $table) {
                $table->bigInteger('id_equipo');
                $table->unsignedBigInteger('id_programa');
                $table->timestamp('fecha_registro')->useCurrent();

                $table->primary(['id_equipo', 'id_programa']);
                $table->index('id_programa');

                $table->foreign('id_equipo', 'fk_equipo_programa_equipo')
                    ->references('id_equipo')->on('equipos')->cascadeOnDelete();
                $table->foreign('id_programa', 'fk_equipo_programa_programa')
                    ->references('id_programa')->on('programas')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_programa');
        Schema::dropIfExists('tipo_equipo_programa');

        if (Schema::hasColumn('tipos_equipo', 'incluye_programas')) {
            Schema::table('tipos_equipo', function (Blueprint $table) {
                $table->dropColumn('incluye_programas');
            });
        }

        Schema::dropIfExists('programas');
    }
};
