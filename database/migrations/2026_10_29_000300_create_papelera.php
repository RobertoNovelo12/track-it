<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Papelera de reciclaje
|--------------------------------------------------------------------------
|
| Al mover algo a la papelera se guarda aquí una copia completa (el
| registro y lo que le pertenece: especificaciones, historial, opciones…)
| y se quita de su tabla. Restaurar vuelve a insertar la copia con sus
| mismos identificadores. A los 30 días se elimina definitivamente.
| Ver App\Services\PapeleraService.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('papelera')) {
            return;
        }

        Schema::create('papelera', function (Blueprint $tabla): void {
            $tabla->bigIncrements('id_papelera');
            $tabla->string('tipo', 40);
            $tabla->unsignedBigInteger('id_registro');
            $tabla->string('etiqueta', 255);
            $tabla->string('detalle', 255)->nullable();
            $tabla->jsonb('datos');
            $tabla->jsonb('archivos')->default('[]');
            $tabla->unsignedBigInteger('eliminado_por')->nullable();
            $tabla->string('eliminado_por_nombre', 255)->nullable();
            $tabla->timestamp('eliminado_en');
            $tabla->timestamp('expira_en');

            $tabla->index('expira_en');
            $tabla->index(['tipo', 'id_registro']);
        });

        // Igual que el resto de tablas (migración de RLS): fuera de la API de Supabase.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.papelera ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('papelera');
    }
};
