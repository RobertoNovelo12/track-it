<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id('id_reporte');

            $table->foreignId('id_usuario');

            $table->string('tipo_reporte', 40);
            $table->string('formato', 10)->default('PDF');

            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();

            $table->json('filtros')->nullable();

            $table->unsignedInteger('total_registros')->default(0);

            $table->string('nombre_archivo')->nullable();
            $table->string('ruta_archivo')->nullable();

            $table->timestamp('fecha_generacion')
                ->useCurrent();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->index('id_usuario');

            $table->index([
                'tipo_reporte',
                'fecha_generacion',
            ]);
        });

        /*
         * Protege la tabla frente al acceso directo
         * mediante la API pública de Supabase.
         * Laravel continuará accediendo mediante su
         * conexión PostgreSQL configurada.
         */
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'ALTER TABLE reportes_generados ENABLE ROW LEVEL SECURITY'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};