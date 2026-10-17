<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departamentos', function (Blueprint $table) {
            /*
             * null: departamento principal.
             * Con valor: registro anterior que corresponde a otro
             * departamento principal.
             */
            $table->bigInteger('id_departamento_principal')
                ->nullable();

            $table->index('id_departamento_principal');

            $table->foreign('id_departamento_principal')
                ->references('id_departamento')
                ->on('departamentos')
                ->restrictOnDelete();
        });

        /*
         * Preferir un registro activo como principal.
         * Dentro del mismo estado, utilizar el de menor ID.
         */
        $departamentos = DB::table('departamentos')
            ->orderByDesc('activo')
            ->orderBy('id_departamento')
            ->get();

        $grupos = $departamentos->groupBy(function ($departamento) {
            $nombre = mb_strtolower(
                trim((string) $departamento->nombre),
                'UTF-8'
            );

            $nombre = preg_replace('/\s+/u', ' ', $nombre);

            // Los registros sin nombre no se fusionan entre sí.
            return $nombre !== ''
                ? 'nombre:'.$nombre
                : 'id:'.$departamento->id_departamento;
        });

        $vinculos = DB::table('departamento_hotel')
            ->get()
            ->groupBy('id_departamento');

        $nuevosVinculos = [];

        foreach ($grupos as $grupo) {
            $principal = $grupo->first();
            $principalId = (int) $principal->id_departamento;

            $duplicados = $grupo
                ->pluck('id_departamento')
                ->reject(fn ($id) => (int) $id === $principalId)
                ->values()
                ->all();

            if ($duplicados === []) {
                continue;
            }

            /*
             * Conservar los registros anteriores y señalar
             * a qué departamento principal pertenecen.
             */
            DB::table('departamentos')
                ->whereIn('id_departamento', $duplicados)
                ->update([
                    'id_departamento_principal' => $principalId,
                ]);

            /*
             * Conservar la cobertura global si ya estaba
             * habilitada en alguno de los registros.
             */
            if ($grupo->contains(
                fn ($item) => (bool) $item->atiende_todos_hoteles
            )) {
                DB::table('departamentos')
                    ->where('id_departamento', $principalId)
                    ->update([
                        'atiende_todos_hoteles' => true,
                    ]);
            }

            /*
             * Reunir en el principal todos los hoteles
             * que estaban asociados a sus registros anteriores.
             */
            foreach ($grupo as $departamento) {
                $hoteles = $vinculos->get(
                    $departamento->id_departamento,
                    collect()
                );

                foreach ($hoteles as $vinculo) {
                    $clave = $principalId.':'.$vinculo->id_hotel;

                    $nuevosVinculos[$clave] = [
                        'id_departamento' => $principalId,
                        'id_hotel' => $vinculo->id_hotel,
                    ];
                }
            }
        }

        foreach (
            array_chunk(array_values($nuevosVinculos), 500)
            as $lote
        ) {
            DB::table('departamento_hotel')
                ->insertOrIgnore($lote);
        }
    }

    public function down(): void
    {
        throw new RuntimeException(
            'La unificación requiere una reversión de datos específica; '
            .'no puede deshacerse automáticamente.'
        );
    }
};