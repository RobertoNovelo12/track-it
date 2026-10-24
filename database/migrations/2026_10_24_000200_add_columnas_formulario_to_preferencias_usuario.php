<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Ajustes › Preferencias › Columnas de los formularios (3 o 2)
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preferencias_usuario') && ! Schema::hasColumn('preferencias_usuario', 'columnas_formulario')) {
            Schema::table('preferencias_usuario', function (Blueprint $table) {
                $table->string('columnas_formulario', 1)->default('3');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('preferencias_usuario') && Schema::hasColumn('preferencias_usuario', 'columnas_formulario')) {
            Schema::table('preferencias_usuario', function (Blueprint $table) {
                $table->dropColumn('columnas_formulario');
            });
        }
    }
};
