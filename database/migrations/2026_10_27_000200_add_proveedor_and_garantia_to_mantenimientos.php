<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo agrega la relación con el proveedor externo; no modifica datos existentes.
        if (Schema::hasTable('mantenimientos') && ! Schema::hasColumn('mantenimientos', 'id_proveedor')) {
            Schema::table('mantenimientos', function (Blueprint $table) {
                $table->integer('id_proveedor')->nullable();
            });
        }

        if (! Schema::hasTable('catalogos') || ! Schema::hasTable('catalogo_valores')) {
            throw new \RuntimeException('Faltan los catálogos para registrar el estado En garantía.');
        }

        $idCatalogo = DB::table('catalogos')
            ->whereRaw('LOWER(clave) = ?', ['estado_mantenimiento'])
            ->value('id_catalogo');

        if (! $idCatalogo) {
            throw new \RuntimeException('No existe el catálogo estado_mantenimiento.');
        }

        $existente = DB::table('catalogo_valores')
            ->where('id_catalogo', $idCatalogo)
            ->where(function ($query) {
                $query->whereRaw('LOWER(clave) = ?', ['en_garantia'])
                    ->orWhereRaw('LOWER(nombre) = ?', ['en garantía']);
            })
            ->first(['id_valor', 'clave', 'activo']);

        if ($existente) {
            // Si ya existía el estado, respetamos su identificador.
            DB::table('catalogo_valores')
                ->where('id_valor', $existente->id_valor)
                ->update(['clave' => 'EN_GARANTIA', 'activo' => true]);
        } else {
            DB::table('catalogo_valores')->insert([
                'id_catalogo' => $idCatalogo,
                'clave' => 'EN_GARANTIA',
                'nombre' => 'En garantía',
                'descripcion' => 'Mantenimiento atendido por un proveedor bajo garantía.',
                'orden' => ((int) DB::table('catalogo_valores')
                    ->where('id_catalogo', $idCatalogo)
                    ->max('orden')) + 1,
                'activo' => true,
            ]);
        }
    }

    public function down(): void
    {
        // No se elimina el catálogo si ya está asociado a algún mantenimiento.
        if (Schema::hasTable('catalogos') && Schema::hasTable('catalogo_valores')) {
            $idCatalogo = DB::table('catalogos')
                ->whereRaw('LOWER(clave) = ?', ['estado_mantenimiento'])
                ->value('id_catalogo');

            if ($idCatalogo) {
                $idValor = DB::table('catalogo_valores')
                    ->where('id_catalogo', $idCatalogo)
                    ->whereRaw('LOWER(clave) = ?', ['en_garantia'])
                    ->value('id_valor');

                if ($idValor && ! DB::table('mantenimientos')
                    ->where('id_estado_mantenimiento', $idValor)->exists()) {
                    DB::table('catalogo_valores')->where('id_valor', $idValor)->delete();
                }
            }
        }

        if (Schema::hasTable('mantenimientos') && Schema::hasColumn('mantenimientos', 'id_proveedor')) {
            Schema::table('mantenimientos', function (Blueprint $table) {
                $table->dropColumn('id_proveedor');
            });
        }
    }
};