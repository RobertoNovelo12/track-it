<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Contacto de soporte (Centro de ayuda)
|--------------------------------------------------------------------------
|
| Datos que muestra la página de Ayuda: equipo, integrantes, correo,
| teléfono y horario. Se editan en Administración › Organización ›
| Soporte. Vacío = valores predeterminados (OrganizacionService).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuracion_organizacion')
            || Schema::hasColumn('configuracion_organizacion', 'soporte')) {
            return;
        }

        Schema::table('configuracion_organizacion', function (Blueprint $tabla): void {
            $tabla->jsonb('soporte')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('configuracion_organizacion', 'soporte')) {
            Schema::table('configuracion_organizacion', function (Blueprint $tabla): void {
                $tabla->dropColumn('soporte');
            });
        }
    }
};
