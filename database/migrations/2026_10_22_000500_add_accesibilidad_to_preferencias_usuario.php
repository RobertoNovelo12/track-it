<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Preferencias de accesibilidad (Ajustes › Preferencias › Accesibilidad)
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('preferencias_usuario')) {
            return;
        }

        Schema::table('preferencias_usuario', function (Blueprint $table) {
            // normal | grande | muy_grande
            if (! Schema::hasColumn('preferencias_usuario', 'tamano_texto')) {
                $table->string('tamano_texto', 12)->default('normal');
            }

            if (! Schema::hasColumn('preferencias_usuario', 'reducir_movimiento')) {
                $table->boolean('reducir_movimiento')->default(false);
            }

            if (! Schema::hasColumn('preferencias_usuario', 'alto_contraste')) {
                $table->boolean('alto_contraste')->default(false);
            }

            if (! Schema::hasColumn('preferencias_usuario', 'resaltar_foco')) {
                $table->boolean('resaltar_foco')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('preferencias_usuario')) {
            return;
        }

        Schema::table('preferencias_usuario', function (Blueprint $table) {
            foreach (['tamano_texto', 'reducir_movimiento', 'alto_contraste', 'resaltar_foco'] as $columna) {
                if (Schema::hasColumn('preferencias_usuario', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
