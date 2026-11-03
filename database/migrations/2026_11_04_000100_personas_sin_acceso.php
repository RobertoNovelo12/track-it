<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Personas sin acceso al sistema («solo inventario»)
|--------------------------------------------------------------------------
|
| Colaboradores registrados para asignarles equipos, pero que NO pueden
| iniciar sesión: usuarios.acceso_sistema = false.
|
| - No necesitan correo, contraseña ni rol: esas columnas aceptan NULL
|   (la contraseña se guarda aleatoria y el inicio de sesión se rechaza
|   siempre, aunque alguien llegara a conocerla).
| - Las cuentas existentes quedan con acceso (default true).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios', 'acceso_sistema')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->boolean('acceso_sistema')->default(true);
            });
        }

        foreach (['correo', 'id_rol'] as $columna) {
            DB::statement("ALTER TABLE usuarios ALTER COLUMN {$columna} DROP NOT NULL");
        }

        \App\Support\Esquema::olvidar();
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'acceso_sistema')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('acceso_sistema');
            });
        }
    }
};
