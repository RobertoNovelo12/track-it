<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Estos IDs corresponden a los cinco hoteles que verificamos.
         * Se utilizan únicamente para trasladar los datos actuales.
         * Los hoteles nuevos se administrarán desde la aplicación.
         */
        $hotelesIniciales = DB::table('areas')
            ->whereIn('id_area', [1, 2, 3, 4, 5])
            ->orderBy('id_area')
            ->get();

        if ($hotelesIniciales->count() !== 5) {
            throw new RuntimeException(
                'Revisa las áreas iniciales antes de migrar los hoteles.'
            );
        }

        Schema::create('hoteles', function (Blueprint $table) {
            $table->bigIncrements('id_hotel');

            $table->bigInteger('id_sede');

            // Relación temporal para trasladar los registros existentes.
            $table->bigInteger('id_area_origen')
                ->nullable()
                ->unique();

            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->string('responsable')->nullable();
            $table->string('correo')->nullable();
            $table->string('extension_telefono')->nullable();
            $table->text('descripcion')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->foreign('id_sede')
                ->references('id_sede')
                ->on('sedes')
                ->restrictOnDelete();

            $table->foreign('id_area_origen')
                ->references('id_area')
                ->on('areas')
                ->restrictOnDelete();

            $table->unique(['id_sede', 'nombre']);
        });

        Schema::table('departamentos', function (Blueprint $table) {
            /*
             * La pantalla y las consultas utilizarán esta opción
             * para incluir también los hoteles que se creen después.
             */
            $table->boolean('atiende_todos_hoteles')
                ->default(false);
        });

        Schema::create('departamento_hotel', function (Blueprint $table) {
            $table->bigInteger('id_departamento');
            $table->bigInteger('id_hotel');

            $table->primary([
                'id_departamento',
                'id_hotel',
            ]);

            $table->index('id_hotel');

            $table->foreign('id_departamento')
                ->references('id_departamento')
                ->on('departamentos')
                ->restrictOnDelete();

            $table->foreign('id_hotel')
                ->references('id_hotel')
                ->on('hoteles')
                ->restrictOnDelete();
        });

        $hotelPorArea = [];
        $now = now();

        foreach ($hotelesIniciales as $area) {
            $hotelPorArea[$area->id_area] = DB::table('hoteles')
                ->insertGetId([
                    'id_sede' => $area->id_sede,
                    'id_area_origen' => $area->id_area,
                    'codigo' => $area->codigo,
                    'nombre' => $area->nombre,
                    'responsable' => $area->responsable,
                    'correo' => $area->correo,
                    'extension_telefono' => $area->extension_telefono,
                    'descripcion' => $area->descripcion,
                    'activo' => $area->activo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id_hotel');
        }

        // Conservar la cobertura que sí conocemos actualmente.
        $relaciones = DB::table('departamentos')
            ->whereIn('id_area', array_keys($hotelPorArea))
            ->get([
                'id_departamento',
                'id_area',
            ])
            ->map(fn ($departamento): array => [
                'id_departamento' => $departamento->id_departamento,
                'id_hotel' => $hotelPorArea[$departamento->id_area],
            ])
            ->all();

        if ($relaciones !== []) {
            DB::table('departamento_hotel')->insert($relaciones);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento_hotel');

        Schema::table('departamentos', function (Blueprint $table) {
            $table->dropColumn('atiende_todos_hoteles');
        });

        Schema::dropIfExists('hoteles');
    }
};