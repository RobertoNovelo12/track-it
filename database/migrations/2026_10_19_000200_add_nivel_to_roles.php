<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Niveles de acceso de los roles
|--------------------------------------------------------------------------
|
| roles.nivel define qué puede hacer cada rol:
|
|   admin     Todo el sistema (incluye organización y seguridad).
|   estandar  Operación diaria: equipos, asignaciones, mantenimientos,
|             reportes y catálogos de equipo. Sin organización ni
|             seguridad.
|   lectura   Solo consulta de equipos.
|
| Los roles existentes se clasifican por nombre. Se crean "Estándar" y
| "Lectura" si no existen. Un rol sin nivel se trata como lectura.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'nivel')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('nivel', 20)->nullable();
            });
        }

        DB::table('roles')
            ->whereNull('nivel')
            ->whereRaw("LOWER(nombre) LIKE '%admin%'")
            ->update(['nivel' => 'admin']);

        DB::table('roles')
            ->whereNull('nivel')
            ->where(function ($query) {
                $query->whereRaw("LOWER(nombre) LIKE '%est_ndar%'")
                    ->orWhereRaw("LOWER(nombre) LIKE '%estandar%'");
            })
            ->update(['nivel' => 'estandar']);

        DB::table('roles')
            ->whereNull('nivel')
            ->where(function ($query) {
                $query->whereRaw("LOWER(nombre) LIKE '%lectura%'")
                    ->orWhereRaw("LOWER(nombre) LIKE '%consulta%'");
            })
            ->update(['nivel' => 'lectura']);

        $roles = [
            'estandar' => [
                'nombre' => 'Estándar',
                'descripcion' => 'Registra y edita equipos, asignaciones, mantenimientos y reportes. No modifica la organización.',
            ],
            'lectura' => [
                'nombre' => 'Lectura',
                'descripcion' => 'Solo consulta los equipos, sin poder crear ni modificar información.',
            ],
        ];

        foreach ($roles as $nivel => $datos) {
            if (DB::table('roles')->where('nivel', $nivel)->exists()) {
                continue;
            }

            DB::table('roles')->insert([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
                'activo' => true,
                'nivel' => $nivel,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'nivel')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('nivel');
            });
        }
    }
};
