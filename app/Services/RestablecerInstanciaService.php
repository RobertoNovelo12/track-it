<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/*
|--------------------------------------------------------------------------
| Restablecer la instancia
|--------------------------------------------------------------------------
|
| Deja el sistema como recién instalado conservando los catálogos:
| marcas, modelos, tipos de equipo y sus formularios, programas,
| proveedores, sedes, tipos de ubicación, catálogos, roles y permisos.
|
| Se vacía todo lo demás: usuarios, edificios (unidades), departamentos,
| ubicaciones, inventario, movimientos, mantenimientos, bajas, bitácora,
| notificaciones y sesiones. Después se crea el usuario temporal
| «instalacion» con una contraseña aleatoria; al entrar, el asistente
| (InstanciaController) pide crear el primer edificio, su departamento
| y la cuenta definitiva, y elimina el usuario temporal.
|
*/

class RestablecerInstanciaService
{
    public const USUARIO_TEMPORAL = 'instalacion';

    /** Tablas que conservan sus datos. Todas las demás del esquema public se vacían. */
    private const CONSERVAR = [
        // Sistema
        'migrations', 'roles', 'permisos', 'roles_permisos',
        'catalogos', 'catalogo_valores', 'estados_equipo', 'estados_usuario',
        'configuracion_instalacion', 'configuracion_organizacion',
        'cache', 'cache_locks',
        // Equipos: tipos, formularios, marcas, modelos, programas y proveedores
        'tipos_equipo', 'tipo_equipo_secciones', 'tipo_equipo_campos', 'tipo_equipo_campo_opciones',
        'campos_equipo', 'marcas', 'modelos', 'programas', 'tipo_equipo_programa', 'proveedores',
        // Organización que no depende de los edificios
        'sedes', 'tipos_ubicacion',
        // Tablas ajenas al sistema actual (se dejan intactas)
        'users', 'assets', 'respaldo_transicion_hoteles',
    ];

    public static function esUsuarioTemporal($usuario): bool
    {
        return $usuario !== null
            && ($usuario->username ?? null) === self::USUARIO_TEMPORAL;
    }

    /**
     * @return array{usuario: string, contrasena: string}
     */
    public function restablecer(?int $idQuienRestablece = null): array
    {
        $contrasena = Str::password(16, symbols: false);

        DB::transaction(function () use ($contrasena, $idQuienRestablece) {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))
                ->pluck('tablename')
                ->all();

            $vaciar = array_values(array_diff($tablas, self::CONSERVAR));

            if (! in_array('usuarios', $vaciar, true)) {
                throw new RuntimeException('No se encontró la tabla usuarios.');
            }

            $llaves = DB::select("
                SELECT co.relname AS tabla, a.attname AS columna, a.attnotnull AS obligatoria, cd.relname AS destino
                FROM pg_constraint c
                JOIN pg_class co ON co.oid = c.conrelid
                JOIN pg_class cd ON cd.oid = c.confrelid
                JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY (c.conkey)
                WHERE c.contype = 'f' AND co.relnamespace = 'public'::regnamespace");

            $seVacia = fn (string $tabla) => in_array($tabla, $vaciar, true);

            // 1. Lo que se conserva y apunta a lo que se vacía queda en NULL
            //    (p. ej. modelos.id_unidad_uso_comun). Si es obligatorio, se detiene.
            // 2. Dentro de lo que se vacía, se rompen las referencias opcionales
            //    para que el orden de borrado no se trabe (p. ej. departamento principal).
            foreach ($llaves as $llave) {
                if (! $seVacia($llave->destino)) {
                    continue;
                }

                if (! $seVacia($llave->tabla) && $llave->obligatoria) {
                    throw new RuntimeException("{$llave->tabla}.{$llave->columna} es obligatoria y apunta a {$llave->destino}.");
                }

                if (! $llave->obligatoria) {
                    DB::statement(sprintf(
                        'UPDATE public.%1$s SET %2$s = NULL WHERE %2$s IS NOT NULL',
                        $this->id($llave->tabla),
                        $this->id($llave->columna)
                    ));
                }
            }

            // 3. Orden de borrado: primero las tablas que dependen de otras.
            foreach ($this->ordenDeBorrado($vaciar, $llaves) as $tabla) {
                DB::statement('DELETE FROM public.'.$this->id($tabla));
            }

            // 4. Contadores de lo vaciado desde 1.
            foreach (DB::select("
                SELECT t.relname AS tabla, pg_get_serial_sequence(format('public.%I', t.relname), a.attname) AS secuencia
                FROM pg_class t
                JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum > 0 AND NOT a.attisdropped
                WHERE t.relnamespace = 'public'::regnamespace AND t.relkind = 'r'") as $fila) {
                if ($fila->secuencia !== null && $seVacia($fila->tabla)) {
                    DB::select('SELECT setval(?, 1, false)', [$fila->secuencia]);
                }
            }

            // 5. Usuario temporal para configurar la instancia.
            $idRol = DB::table('roles')->where('nivel', 'admin')->orderByDesc('activo')->orderBy('id_rol')->value('id_rol');
            $idEstado = DB::table('estados_usuario')->where('clave', 'ACTIVO')->value('id_estado_usuario');

            if (! $idRol || ! $idEstado) {
                throw new RuntimeException('Falta el rol de administrador o el estado ACTIVO.');
            }

            $idUsuario = DB::table('usuarios')->insertGetId([
                'id_rol' => $idRol,
                'id_estado_usuario' => $idEstado,
                'nombres' => 'Instalación',
                'apellido_paterno' => 'Track IT',
                'username' => self::USUARIO_TEMPORAL,
                'password_hash' => Hash::make($contrasena),
                'puesto' => 'Configuración inicial',
                'debe_cambiar_password' => false,
                'acceso_sistema' => true,
                'fecha_registro' => now(),
            ], 'id_usuario');

            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => $idUsuario,
                'accion' => 'INSTANCIA_RESTABLECIDA',
                'modulo' => 'ADMINISTRACION',
                'descripcion' => 'Se restableció la instancia'
                    .($idQuienRestablece ? " (solicitado por el usuario #{$idQuienRestablece})" : '')
                    .'. Se conservaron los catálogos; se eliminaron usuarios, organización e inventario.',
                'fecha_hora' => now(),
            ]);
        });

        Cache::flush();

        return [
            'usuario' => self::USUARIO_TEMPORAL,
            'contrasena' => $contrasena,
        ];
    }

    /**
     * Ordena las tablas para que nunca se borre una tabla mientras otra
     * (que también se vacía) la referencie con una columna obligatoria.
     */
    private function ordenDeBorrado(array $tablas, array $llaves): array
    {
        $referenciadaPor = array_fill_keys($tablas, []);

        foreach ($llaves as $llave) {
            if (
                $llave->obligatoria
                && $llave->tabla !== $llave->destino
                && isset($referenciadaPor[$llave->tabla], $referenciadaPor[$llave->destino])
            ) {
                $referenciadaPor[$llave->destino][$llave->tabla] = true;
            }
        }

        $orden = [];

        while ($referenciadaPor !== []) {
            $listas = array_keys(array_filter($referenciadaPor, fn ($hijas) => $hijas === []));

            if ($listas === []) {
                throw new RuntimeException('Referencias circulares entre: '.implode(', ', array_keys($referenciadaPor)));
            }

            foreach ($listas as $tabla) {
                $orden[] = $tabla;
                unset($referenciadaPor[$tabla]);

                foreach ($referenciadaPor as &$hijas) {
                    unset($hijas[$tabla]);
                }
                unset($hijas);
            }
        }

        return $orden;
    }

    private function id(string $nombre): string
    {
        return '"'.str_replace('"', '""', $nombre).'"';
    }
}
