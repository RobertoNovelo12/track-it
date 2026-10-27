<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException('La transición de áreas requiere PostgreSQL.');
        }

        // Laravel ejecuta esta migración dentro de una transacción PostgreSQL.
        // El respaldo no es un catálogo y no se expone en la aplicación.
        Schema::create('respaldo_transicion_hoteles', function (Blueprint $table) {
            $table->string('tabla')->primary();
            $table->jsonb('datos');
            $table->timestamp('creado_en');
        });

        $respaldar = static function (string $tabla, $filas): void {
            DB::table('respaldo_transicion_hoteles')->insert([
                'tabla' => $tabla,
                'datos' => json_encode($filas, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'creado_en' => now(),
            ]);
        };

        $areas = DB::table('areas')->orderBy('id_area')->get();
        $respaldar('areas', $areas);
        foreach (['equipos' => 'id_equipo', 'asignaciones' => 'id_asignacion', 'usuarios' => 'id_usuario', 'ubicaciones' => 'id_ubicacion'] as $tabla => $id) {
            $respaldar($tabla, DB::table($tabla)->get([$id, 'id_area', 'id_unidad']));
        }
        $respaldar('departamentos', DB::table('departamentos')->get(['id_departamento', 'id_area']));
        $respaldar('modelos', DB::table('modelos')->get(['id_modelo', 'id_area_uso_comun']));
        $respaldar('unidades_organizativas', DB::table('unidades_organizativas')->get(['id_unidad', 'id_area_origen']));

        $mapa = [];
        foreach ($areas as $area) {
            $unidad = DB::table('unidades_organizativas')->where('id_area_origen', $area->id_area)->first();
            if (!$unidad) {
                $coincidencias = DB::table('unidades_organizativas')
                    ->where('id_sede', $area->id_sede)
                    ->whereRaw('LOWER(TRIM(nombre)) = LOWER(TRIM(?))', [$area->nombre])->get();
                if ($coincidencias->count() > 1) {
                    throw new RuntimeException('Hay hoteles ambiguos para el área '.$area->id_area.'.');
                }
                $unidad = $coincidencias->first();
            }
            if (!$unidad) {
                $id = DB::table('unidades_organizativas')->insertGetId([
                    'id_sede' => $area->id_sede,
                    'codigo' => $area->codigo,
                    'nombre' => $area->nombre,
                    'responsable' => $area->responsable,
                    'correo' => $area->correo,
                    'extension_telefono' => $area->extension_telefono,
                    'descripcion' => $area->descripcion,
                    'activo' => $area->activo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id_unidad');
            } else {
                $id = $unidad->id_unidad;
            }
            $mapa[(int) $area->id_area] = (int) $id;
        }

        Schema::table('modelos', function (Blueprint $table) {
            $table->bigInteger('id_unidad_uso_comun')->nullable();
            $table->foreign('id_unidad_uso_comun')->references('id_unidad')->on('unidades_organizativas')->nullOnDelete();
        });

        foreach ($mapa as $area => $unidad) {
            foreach (['equipos', 'asignaciones', 'usuarios', 'ubicaciones'] as $tabla) {
                DB::table($tabla)->whereNull('id_unidad')->where('id_area', $area)->update(['id_unidad' => $unidad]);
            }
            // Completar la ubicación histórica solamente cuando no hay hotel explícito.
            foreach (['equipos', 'asignaciones', 'usuarios'] as $tabla) {
                DB::table($tabla)->whereNull('id_unidad')->whereNull('id_area')
                    ->whereIn('id_departamento', DB::table('departamentos')->where('id_area', $area)->select('id_departamento'))
                    ->update(['id_unidad' => $unidad]);
            }
            DB::table('modelos')->where('id_area_uso_comun', $area)->update(['id_unidad_uso_comun' => $unidad]);
            foreach (DB::table('departamentos')->where('id_area', $area)->get() as $departamento) {
                DB::table('departamento_unidad')->insertOrIgnore([
                    'id_departamento' => $departamento->id_departamento_principal ?: $departamento->id_departamento,
                    'id_unidad' => $unidad,
                ]);
            }
        }

        // Convertir definiciones dinámicas sin duplicar el campo Hotel existente.
        $campoArea = DB::table('campos_equipo')->where('clave', 'id_area')->first();
        if ($campoArea) {
            $respaldar('campos_equipo', [$campoArea]);
            $respaldar('tipo_equipo_campos', DB::table('tipo_equipo_campos')->where('id_campo', $campoArea->id_campo)->get());
            $respaldar('atributos_equipo', DB::table('atributos_equipo')->where('id_campo', $campoArea->id_campo)->get());
            $campoHotel = DB::table('campos_equipo')->where('clave', 'id_unidad')->first();
            if (!$campoHotel) {
                DB::table('campos_equipo')->where('id_campo', $campoArea->id_campo)->update(['clave' => 'id_unidad', 'nombre' => 'Hotel', 'placeholder' => 'Seleccionar hotel']);
                $campoHotel = $campoArea;
            } else {
                foreach (DB::table('tipo_equipo_campos')->where('id_campo', $campoArea->id_campo)->get() as $config) {
                    $existe = DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $config->id_tipo_equipo)->where('id_campo', $campoHotel->id_campo)->exists();
                    if ($existe) {
                        DB::table('tipo_equipo_campos')->where('id_tipo_campo', $config->id_tipo_campo)->delete();
                    } else {
                        DB::table('tipo_equipo_campos')->where('id_tipo_campo', $config->id_tipo_campo)->update(['id_campo' => $campoHotel->id_campo, 'etiqueta' => 'Hotel']);
                    }
                }
            }
            foreach (DB::table('atributos_equipo')->where('id_campo', $campoArea->id_campo)->get() as $atributo) {
                $unidad = $mapa[(int) $atributo->valor] ?? null;
                if ($unidad === null) {
                    throw new RuntimeException('Un atributo de área no tiene hotel equivalente; revisa el equipo '.$atributo->id_equipo.'.');
                }
                DB::table('equipos')->where('id_equipo', $atributo->id_equipo)->whereNull('id_unidad')->update(['id_unidad' => $unidad]);
            }
            DB::table('atributos_equipo')->where('id_campo', $campoArea->id_campo)->delete();
            if ($campoHotel->id_campo !== $campoArea->id_campo) {
                DB::table('campos_equipo')->where('id_campo', $campoArea->id_campo)->delete();
            }
        }
        DB::table('tipo_equipo_campos')->where('fuente_opciones', 'areas')->update(['fuente_opciones' => 'unidades_organizativas', 'etiqueta' => 'Hotel']);
        DB::table('tipo_equipo_campos')->where('columna_destino', 'id_area')->update(['columna_destino' => 'id_unidad']);
        DB::table('tipo_equipo_campos')->where('tabla_destino', 'areas')->update(['tabla_destino' => 'unidades_organizativas']);

        DB::statement('ALTER TABLE asignaciones DROP CONSTRAINT IF EXISTS chk_asignacion_area_departamento');
        DB::statement('ALTER TABLE equipos DROP CONSTRAINT IF EXISTS chk_equipo_area_departamento');
        DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS fk_usuarios_departamento_area');
        DB::statement('ALTER TABLE departamentos ALTER COLUMN id_area DROP NOT NULL');

        // Columnas vacías de compatibilidad: permiten desplegar el código nuevo
        // después de la migración sin romper las lecturas de la versión anterior.
        foreach (['equipos', 'asignaciones', 'usuarios', 'ubicaciones', 'departamentos'] as $tabla) {
            DB::table($tabla)->whereNotNull('id_area')->update(['id_area' => null]);
        }
        DB::table('modelos')->whereNotNull('id_area_uso_comun')->update(['id_area_uso_comun' => null]);
        DB::table('unidades_organizativas')->whereNotNull('id_area_origen')->update(['id_area_origen' => null]);
        DB::table('areas')->delete();
        DB::table('solicitudes_cambio_organizacion')->where('campo', 'area')->update(['campo' => 'hotel']);
        DB::statement('ALTER TABLE solicitudes_cambio_organizacion DROP CONSTRAINT IF EXISTS chk_solicitudes_campo');
        DB::statement("ALTER TABLE solicitudes_cambio_organizacion ADD CONSTRAINT chk_solicitudes_campo CHECK (campo IN ('numero_colaborador', 'puesto', 'rol', 'hotel', 'departamento'))");
    }

    public function down(): void
    {
        // Restaurar áreas automáticamente reintroduciría el error original y
        // descartaría hoteles capturados después. El respaldo se conserva.
        throw new RuntimeException('La reversión requiere revisar respaldo_transicion_hoteles; no se restauran reglas obsoletas automáticamente.');
    }
};
