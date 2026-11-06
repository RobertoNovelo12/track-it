<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/*
 * Genera database/entrega/salida/track-it-entrega.sql: la estructura completa
 * del esquema public más los datos que se entregan (catálogos, tipos de equipo,
 * marcas, modelos y organización) y un administrador inicial.
 *
 * Solo LEE la base configurada en el .env; no modifica nada. No necesita
 * pg_dump ni Docker. Se restaura con: php artisan trackit:importar-entrega
 */
class ExportarEntrega extends Command
{
    protected $signature = 'trackit:exportar-entrega {--contrasena=TrackIT-Entrega2026! : Contraseña temporal del usuario admin}';

    protected $description = 'Genera el script SQL de la base de datos de entrega (sin usuarios, inventario ni actividad)';

    /** Tablas que se entregan con sus datos (las que no existan se ignoran). */
    private const CONSERVAR = [
        // Sistema
        'migrations', 'roles', 'permisos', 'rol_permiso', 'roles_permisos', 'permiso_rol', 'permisos_roles',
        'catalogos', 'catalogo_valores', 'estados_equipo', 'estados_usuario',
        'configuracion_instalacion', 'configuracion_organizacion',
        // Equipos: tipos, formularios, marcas y modelos
        'tipos_equipo', 'tipo_equipo_secciones', 'tipo_equipo_campos', 'tipo_equipo_campo_opciones', 'campos_equipo',
        'marcas', 'modelos',
        // Programas y los que se sugieren por tipo de equipo
        'programas', 'tipo_equipo_programa',
        // Organización
        'sedes', 'unidades_organizativas', 'hoteles', 'departamentos', 'departamento_unidad', 'departamento_hotel',
        'ubicaciones', 'tipos_ubicacion', 'departamento_ubicacion',
    ];

    /** Identidad visual de fábrica (configuracion_organizacion). */
    private const IDENTIDAD_FABRICA = [
        'nombre_organizacion' => 'Grand Palladium Hotels & Resorts',
        'logo_base64' => null, 'logo_mime' => null, 'logo_version' => null, 'logo_nombre_original' => null,
        'logo_escala' => '100', 'logo_modo_oscuro' => 'blanco', 'logo_color_fondo' => null, 'color_primario' => null,
    ];

    private \PDO $pdo;

    public function handle(): int
    {
        DB::statement('SET search_path TO public');
        $this->pdo = DB::connection()->getPdo();

        $tablas = $this->tablas();
        $conservar = array_values(array_intersect(self::CONSERVAR, array_keys($tablas)));

        if (! isset($tablas['usuarios'], $tablas['roles'])) {
            throw new RuntimeException('No encontré las tablas usuarios y roles en el esquema public.');
        }

        $sql = [];
        $sql[] = $this->encabezado();
        $sql[] = "BEGIN;\nSET check_function_bodies = false;\nSET search_path TO public;\n";
        $sql[] = "CREATE SCHEMA IF NOT EXISTS extensions;\nCREATE EXTENSION IF NOT EXISTS \"uuid-ossp\" SCHEMA extensions;\nCREATE EXTENSION IF NOT EXISTS pgcrypto SCHEMA extensions;\n";
        $sql[] = $this->tiposEnum();
        $sql[] = $this->funciones();
        $sql[] = $this->secuencias();

        foreach ($tablas as $nombre => $oid) {
            $sql[] = $this->crearTabla($nombre, $oid);
        }

        $sql[] = $this->propietariosSecuencias();

        $this->info('Copiando datos…');
        $referencias = $this->referencias();
        foreach ($conservar as $tabla) {
            $sql[] = $this->datos($tabla, $tablas[$tabla], $referencias[$tabla] ?? []);
        }
        $sql[] = $this->administrador($tablas['usuarios'], $referencias['usuarios'] ?? []);
        $sql[] = $this->reiniciarContadores();

        $sql[] = $this->restricciones();
        $sql[] = $this->indices();
        $sql[] = $this->llavesForaneas();
        $sql[] = $this->vistas();
        $sql[] = $this->disparadores();
        $sql[] = "COMMIT;\n";

        $carpeta = database_path('entrega/salida');
        if (! is_dir($carpeta)) {
            mkdir($carpeta, 0775, true);
        }
        $archivo = $carpeta.DIRECTORY_SEPARATOR.'track-it-entrega.sql';
        file_put_contents($archivo, implode("\n", array_filter($sql)));

        $this->newLine();
        $this->info('Listo: database/entrega/salida/track-it-entrega.sql ('.round(filesize($archivo) / 1024).' KB)');
        $this->line('Tablas creadas: '.count($tablas).' · con datos: '.count($conservar).' + usuarios (solo admin)');
        $this->line('Usuario inicial: admin · contraseña temporal: '.$this->option('contrasena'));

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    // Estructura
    // ------------------------------------------------------------------

    /** @return array<string,int> nombre => oid */
    private function tablas(): array
    {
        $filas = DB::select("
            SELECT c.oid, c.relname
            FROM pg_class c
            WHERE c.relnamespace = 'public'::regnamespace AND c.relkind = 'r'
              AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = c.oid AND d.deptype = 'e')
            ORDER BY c.relname");

        return collect($filas)->mapWithKeys(fn ($f) => [$f->relname => (int) $f->oid])->all();
    }

    private function tiposEnum(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT t.typname, string_agg(quote_literal(e.enumlabel), ', ' ORDER BY e.enumsortorder) AS valores
            FROM pg_type t JOIN pg_enum e ON e.enumtypid = t.oid
            WHERE t.typnamespace = 'public'::regnamespace
            GROUP BY t.typname ORDER BY t.typname") as $t) {
            $out .= 'CREATE TYPE public.'.$this->id($t->typname)." AS ENUM ({$t->valores});\n";
        }

        return $out;
    }

    private function funciones(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT pg_get_functiondef(p.oid) AS def
            FROM pg_proc p
            WHERE p.pronamespace = 'public'::regnamespace AND p.prokind IN ('f', 'p')
              AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = p.oid AND d.deptype = 'e')
            ORDER BY p.proname") as $f) {
            $out .= rtrim($f->def).";\n\n";
        }

        return $out;
    }

    private function secuencias(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT c.relname, format_type(s.seqtypid, NULL) AS tipo, s.seqincrement, s.seqmin, s.seqmax, s.seqstart, s.seqcache, s.seqcycle
            FROM pg_sequence s JOIN pg_class c ON c.oid = s.seqrelid
            WHERE c.relnamespace = 'public'::regnamespace
              AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = c.oid AND d.deptype IN ('i', 'e'))
            ORDER BY c.relname") as $s) {
            $out .= 'CREATE SEQUENCE public.'.$this->id($s->relname)." AS {$s->tipo} INCREMENT {$s->seqincrement} MINVALUE {$s->seqmin} MAXVALUE {$s->seqmax} START {$s->seqstart} CACHE {$s->seqcache}".($s->seqcycle ? ' CYCLE' : '').";\n";
        }

        return $out;
    }

    private function columnas(int $oid): array
    {
        return DB::select('
            SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS tipo, a.attnotnull, a.attidentity, a.attgenerated,
                   pg_get_expr(d.adbin, d.adrelid) AS defecto
            FROM pg_attribute a
            LEFT JOIN pg_attrdef d ON d.adrelid = a.attrelid AND d.adnum = a.attnum
            WHERE a.attrelid = ? AND a.attnum > 0 AND NOT a.attisdropped
            ORDER BY a.attnum', [$oid]);
    }

    private function crearTabla(string $nombre, int $oid): string
    {
        $defs = [];
        foreach ($this->columnas($oid) as $c) {
            $d = '    '.$this->id($c->attname).' '.$c->tipo;
            if ($c->attidentity === 'a' || $c->attidentity === 'd') {
                $d .= ' GENERATED '.($c->attidentity === 'a' ? 'ALWAYS' : 'BY DEFAULT').' AS IDENTITY';
            } elseif ($c->attgenerated === 's') {
                $d .= " GENERATED ALWAYS AS ({$c->defecto}) STORED";
            } elseif ($c->defecto !== null) {
                $d .= ' DEFAULT '.$c->defecto;
            }
            if ($c->attnotnull) {
                $d .= ' NOT NULL';
            }
            $defs[] = $d;
        }

        return 'CREATE TABLE public.'.$this->id($nombre)." (\n".implode(",\n", $defs)."\n);\n";
    }

    private function propietariosSecuencias(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT s.relname AS secuencia, t.relname AS tabla, a.attname AS columna
            FROM pg_depend d
            JOIN pg_class s ON s.oid = d.objid AND s.relkind = 'S'
            JOIN pg_class t ON t.oid = d.refobjid
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = d.refobjsubid
            WHERE d.deptype = 'a' AND s.relnamespace = 'public'::regnamespace") as $r) {
            $out .= 'ALTER SEQUENCE public.'.$this->id($r->secuencia).' OWNED BY public.'.$this->id($r->tabla).'.'.$this->id($r->columna).";\n";
        }

        return $out;
    }

    private function restricciones(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT t.relname AS tabla, c.conname, pg_get_constraintdef(c.oid) AS def
            FROM pg_constraint c JOIN pg_class t ON t.oid = c.conrelid
            WHERE t.relnamespace = 'public'::regnamespace AND t.relkind = 'r' AND c.contype IN ('p', 'u', 'c', 'x')
            ORDER BY CASE c.contype WHEN 'p' THEN 0 WHEN 'u' THEN 1 ELSE 2 END, t.relname, c.conname") as $r) {
            $out .= 'ALTER TABLE ONLY public.'.$this->id($r->tabla).' ADD CONSTRAINT '.$this->id($r->conname).' '.$r->def.";\n";
        }

        return $out;
    }

    private function indices(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT pg_get_indexdef(i.indexrelid) AS def
            FROM pg_index i JOIN pg_class t ON t.oid = i.indrelid
            WHERE t.relnamespace = 'public'::regnamespace AND t.relkind = 'r'
              AND NOT EXISTS (SELECT 1 FROM pg_constraint c WHERE c.conindid = i.indexrelid AND c.contype IN ('p', 'u', 'x'))
            ORDER BY t.relname") as $r) {
            $out .= $r->def.";\n";
        }

        return $out;
    }

    private function llavesForaneas(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT t.relname AS tabla, c.conname, pg_get_constraintdef(c.oid) AS def
            FROM pg_constraint c JOIN pg_class t ON t.oid = c.conrelid
            WHERE t.relnamespace = 'public'::regnamespace AND c.contype = 'f'
            ORDER BY t.relname, c.conname") as $r) {
            $out .= 'ALTER TABLE ONLY public.'.$this->id($r->tabla).' ADD CONSTRAINT '.$this->id($r->conname).' '.$r->def.";\n";
        }

        return $out;
    }

    private function vistas(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT c.relname, c.relkind, pg_get_viewdef(c.oid) AS def
            FROM pg_class c
            WHERE c.relnamespace = 'public'::regnamespace AND c.relkind IN ('v', 'm')
              AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.objid = c.oid AND d.deptype = 'e')
            ORDER BY c.oid") as $v) {
            $out .= 'CREATE '.($v->relkind === 'm' ? 'MATERIALIZED VIEW' : 'VIEW').' public.'.$this->id($v->relname)." AS\n".rtrim(trim($v->def), ';').";\n";
        }

        return $out;
    }

    private function disparadores(): string
    {
        $out = '';
        foreach (DB::select("
            SELECT pg_get_triggerdef(tg.oid) AS def
            FROM pg_trigger tg JOIN pg_class t ON t.oid = tg.tgrelid
            WHERE t.relnamespace = 'public'::regnamespace AND NOT tg.tgisinternal
            ORDER BY t.relname, tg.tgname") as $r) {
            $out .= $r->def.";\n";
        }

        return $out;
    }

    private function reiniciarContadores(): string
    {
        $out = "-- Contadores: continúan después del último id entregado (o desde 1).\n";
        foreach (DB::select("
            SELECT t.relname AS tabla, a.attname AS columna,
                   pg_get_serial_sequence(format('public.%I', t.relname), a.attname) AS secuencia
            FROM pg_class t JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum > 0 AND NOT a.attisdropped
            WHERE t.relnamespace = 'public'::regnamespace AND t.relkind = 'r'") as $r) {
            if ($r->secuencia === null) {
                continue;
            }
            $col = $this->id($r->columna);
            $out .= 'SELECT setval('.$this->pdo->quote($r->secuencia).", COALESCE(MAX({$col}), 0) + 1, false) FROM public.".$this->id($r->tabla).";\n";
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // Datos
    // ------------------------------------------------------------------

    /**
     * Llaves foráneas por tabla: columna => [destino, obligatoria].
     *
     * @return array<string, array<string, array{0:string,1:bool}>>
     */
    private function referencias(): array
    {
        $refs = [];
        foreach (DB::select("
            SELECT co.relname AS tabla, a.attname AS columna, a.attnotnull, cd.relname AS destino
            FROM pg_constraint c
            JOIN pg_class co ON co.oid = c.conrelid
            JOIN pg_class cd ON cd.oid = c.confrelid
            JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY (c.conkey)
            WHERE c.contype = 'f' AND co.relnamespace = 'public'::regnamespace") as $r) {
            $refs[$r->tabla][$r->columna] = [$r->destino, (bool) $r->attnotnull];
        }

        return $refs;
    }

    /**
     * Ajusta una fila: lo que apunta a usuarios pasa al admin (id 1) si es
     * obligatorio o queda vacío; lo que apunta a otras tablas que no se
     * entregan queda vacío (si es obligatorio, se detiene).
     */
    private function ajustarReferencias(string $tabla, array $fila, array $refs): array
    {
        foreach ($refs as $columna => [$destino, $obligatoria]) {
            if (! array_key_exists($columna, $fila) || $fila[$columna] === null) {
                continue;
            }
            if ($destino === 'usuarios') {
                $fila[$columna] = $obligatoria ? '1' : null;
            } elseif (! in_array($destino, self::CONSERVAR, true)) {
                if ($obligatoria) {
                    throw new RuntimeException("{$tabla}.{$columna} es obligatoria y apunta a {$destino}, que no se entrega.");
                }
                $fila[$columna] = null;
            }
        }

        return $fila;
    }

    /** Lee las filas como texto (sirve para cualquier tipo de columna). */
    private function filas(int $oid, string $tabla, string $donde = '', array $params = []): array
    {
        $cols = array_values(array_filter($this->columnas($oid), fn ($c) => $c->attgenerated !== 's'));
        $select = implode(', ', array_map(fn ($c) => 'x.'.$this->id($c->attname).'::text AS '.$this->id($c->attname), $cols));
        $filas = DB::select("SELECT {$select} FROM public.".$this->id($tabla)." x {$donde} ORDER BY 1", $params);

        return [$cols, array_map(fn ($f) => (array) $f, $filas)];
    }

    private function insertar(string $tabla, array $cols, array $filas): string
    {
        if (! $filas) {
            return '';
        }
        $identidad = collect($cols)->contains(fn ($c) => $c->attidentity === 'a') ? ' OVERRIDING SYSTEM VALUE' : '';
        $lista = implode(', ', array_map(fn ($c) => $this->id($c->attname), $cols));
        $out = '';
        foreach (array_chunk($filas, 200) as $bloque) {
            $valores = array_map(fn ($f) => '('.implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : $this->pdo->quote($v), array_values($f))).')', $bloque);
            $out .= 'INSERT INTO public.'.$this->id($tabla)." ({$lista}){$identidad} VALUES\n".implode(",\n", $valores).";\n";
        }

        return $out;
    }

    private function datos(string $tabla, int $oid, array $refs): string
    {
        [$cols, $filas] = $this->filas($oid, $tabla);
        $filas = array_map(fn ($f) => $this->ajustarReferencias($tabla, $f, $refs), $filas);

        if ($tabla === 'configuracion_organizacion') {
            $filas = array_map(function ($f) {
                foreach (self::IDENTIDAD_FABRICA as $col => $valor) {
                    if (array_key_exists($col, $f)) {
                        $f[$col] = $valor;
                    }
                }

                return $f;
            }, $filas);
        }

        $this->line(sprintf('  %-32s %d filas', $tabla, count($filas)));

        return "-- {$tabla}\n".$this->insertar($tabla, $cols, $filas);
    }

    private function administrador(int $oid, array $refs): string
    {
        [$cols, $filas] = $this->filas($oid, 'usuarios', "
            WHERE x.id_usuario = (
                SELECT u.id_usuario FROM public.usuarios u JOIN public.roles r ON r.id_rol = u.id_rol
                WHERE r.nivel = 'admin' ORDER BY u.id_usuario LIMIT 1)");

        if (! $filas) {
            throw new RuntimeException('No hay ningún usuario con rol de administrador para usarlo como base.');
        }

        $admin = $this->ajustarReferencias('usuarios', $filas[0], $refs);
        $cambios = [
            'id_usuario' => '1',
            'nombres' => 'Administrador', 'apellido_paterno' => 'Track IT', 'apellido_materno' => null,
            'username' => 'admin', 'correo' => 'admin@trackit.local', 'telefono' => null,
            'numero_colaborador' => '000001', 'puesto' => 'Administrador del sistema',
            'password_hash' => Hash::make((string) $this->option('contrasena')),
            'debe_cambiar_password' => 'true', 'acceso_sistema' => 'true',
            'two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null,
            'fecha_nacimiento' => null, 'remember_token' => null, 'ultimo_acceso' => null, 'fecha_ultimo_acceso' => null,
            'fecha_registro' => 'now', 'fecha_creacion' => 'now', 'fecha_actualizacion' => 'now',
        ];
        foreach ($cambios as $col => $valor) {
            if (array_key_exists($col, $admin)) {
                $admin[$col] = $valor;
            }
        }

        $this->line(sprintf('  %-32s %d fila (admin)', 'usuarios', 1));

        return "-- usuarios: solo el administrador inicial\n".$this->insertar('usuarios', $cols, [$admin]);
    }

    // ------------------------------------------------------------------

    private function id(string $nombre): string
    {
        return '"'.str_replace('"', '""', $nombre).'"';
    }

    private function encabezado(): string
    {
        return <<<'SQL'
-- ======================================================================
-- Track IT · Base de datos de entrega
-- Incluye: estructura completa, roles y permisos, catálogos, tipos de
-- equipo y formularios, marcas, modelos, programas y organización (sedes, hoteles,
-- departamentos, ubicaciones).
-- No incluye: usuarios (solo «admin»), inventario, asignaciones,
-- mantenimientos, bajas, proveedores, actividad ni notificaciones.
--
-- Restaurar en una base vacía:
--   php artisan trackit:importar-entrega
--   o bien:  psql "<cadena de conexión>" -f track-it-entrega.sql
--
-- Primer acceso: usuario «admin» con la contraseña temporal indicada al
-- exportar. Pide cambiarla y activar la verificación en dos pasos.
-- ======================================================================

SQL;
    }
}
