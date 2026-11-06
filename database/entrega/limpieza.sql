-- =====================================================================
-- Track IT · Limpieza para la base de entrega
-- ---------------------------------------------------------------------
-- Se ejecuta SOLO sobre la copia temporal (contenedor.sh), nunca sobre
-- Supabase. Deja los catálogos y la organización, vacía todo lo demás
-- (usuarios, inventario, movimientos, bitácora, notificaciones…), crea
-- un administrador inicial y comprueba que no quede nada huérfano.
-- Variable de psql: admin_password.
-- =====================================================================
\set ON_ERROR_STOP on

BEGIN;

-- Sin revisar llaves foráneas mientras se vacía (se revalidan al final).
SET session_replication_role = replica;

-- ---------------------------------------------------------------------
-- Tablas que se conservan con sus datos (las que no existan se ignoran)
-- ---------------------------------------------------------------------
CREATE TEMP TABLE conservar (tabla text PRIMARY KEY);
INSERT INTO conservar VALUES
    -- Sistema
    ('migrations'), ('roles'), ('permisos'), ('rol_permiso'), ('roles_permisos'), ('permiso_rol'), ('permisos_roles'),
    ('catalogos'), ('catalogo_valores'), ('estados_equipo'), ('estados_usuario'),
    ('configuracion_instalacion'), ('configuracion_organizacion'),
    -- Equipos: tipos, formularios, marcas y modelos
    ('tipos_equipo'), ('tipo_equipo_secciones'), ('tipo_equipo_campos'), ('tipo_equipo_campo_opciones'), ('campos_equipo'),
    ('marcas'), ('modelos'),
    -- Organización
    ('sedes'), ('unidades_organizativas'), ('hoteles'), ('departamentos'), ('departamento_unidad'), ('departamento_hotel'),
    ('ubicaciones'), ('tipos_ubicacion'), ('departamento_ubicacion');

-- Base del administrador inicial: el primer usuario con rol de nivel «admin».
CREATE TEMP TABLE admin_plantilla AS
SELECT u.*
FROM public.usuarios u
JOIN public.roles r ON r.id_rol = u.id_rol
WHERE r.nivel = 'admin'
ORDER BY u.id_usuario
LIMIT 1;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM admin_plantilla) THEN
        RAISE EXCEPTION 'No se encontró ningún usuario con rol de administrador para usarlo como base.';
    END IF;
END $$;

-- ---------------------------------------------------------------------
-- 1. Lo que se conserva y apunta a tablas que se vacían: se deja en NULL
--    (p. ej. marcas.id_proveedor_sugerido, «creado por» un usuario…).
--    Las columnas obligatorias que apuntan a usuarios pasan al admin.
-- ---------------------------------------------------------------------
CREATE TEMP TABLE refs_obligatorias (tabla regclass, columna name, destino regclass);

DO $$
DECLARE
    r record;
    pendientes text;
BEGIN
    FOR r IN
        SELECT c.conrelid::regclass AS tabla, a.attname AS columna, a.attnotnull AS obligatoria, c.confrelid::regclass AS destino
        FROM pg_constraint c
        JOIN pg_class co ON co.oid = c.conrelid
        JOIN pg_class cd ON cd.oid = c.confrelid
        JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY (c.conkey)
        WHERE c.contype = 'f'
          AND co.relnamespace = 'public'::regnamespace
          AND co.relname IN (SELECT tabla FROM conservar)
          AND cd.relname NOT IN (SELECT tabla FROM conservar)
    LOOP
        IF r.obligatoria THEN
            INSERT INTO refs_obligatorias VALUES (r.tabla, r.columna, r.destino);
        ELSE
            EXECUTE format('UPDATE %s SET %I = NULL WHERE %I IS NOT NULL', r.tabla, r.columna, r.columna);
        END IF;
    END LOOP;

    SELECT string_agg(format('%s.%s → %s', tabla, columna, destino), ', ')
      INTO pendientes
      FROM refs_obligatorias
     WHERE destino <> 'public.usuarios'::regclass;

    IF pendientes IS NOT NULL THEN
        RAISE EXCEPTION 'Columnas obligatorias que apuntan a tablas que se vacían: %', pendientes;
    END IF;
END $$;

-- ---------------------------------------------------------------------
-- 2. Vaciar todas las demás tablas y reiniciar sus contadores
-- ---------------------------------------------------------------------
DO $$
DECLARE
    t record;
    s text;
BEGIN
    FOR t IN
        SELECT tablename FROM pg_tables
        WHERE schemaname = 'public' AND tablename NOT IN (SELECT tabla FROM conservar)
    LOOP
        EXECUTE format('DELETE FROM public.%I', t.tablename);
    END LOOP;

    FOR s IN
        SELECT pg_get_serial_sequence(format('public.%I', c.table_name), c.column_name)
        FROM information_schema.columns c
        WHERE c.table_schema = 'public' AND c.table_name NOT IN (SELECT tabla FROM conservar)
    LOOP
        IF s IS NOT NULL THEN
            EXECUTE format('ALTER SEQUENCE %s RESTART WITH 1', s);
        END IF;
    END LOOP;
END $$;

-- ---------------------------------------------------------------------
-- 3. Administrador inicial (id 1). Contraseña temporal: debe cambiarla
--    al entrar y activar la verificación en dos pasos.
-- ---------------------------------------------------------------------
UPDATE admin_plantilla SET
    id_usuario = 1,
    nombres = 'Administrador',
    apellido_paterno = 'Track IT',
    apellido_materno = NULL,
    username = 'admin',
    correo = 'admin@trackit.local',
    telefono = NULL,
    numero_colaborador = '000001',
    puesto = 'Administrador del sistema',
    -- bcrypt; Laravel espera el prefijo $2y$ (equivalente a $2a$).
    password_hash = '$2y$' || substr(extensions.crypt(:'admin_password', extensions.gen_salt('bf', 12)), 5);

DO $$
DECLARE
    cambio record;
BEGIN
    FOR cambio IN
        SELECT * FROM (VALUES
            ('debe_cambiar_password', 'true'),
            ('acceso_sistema', 'true'),
            ('two_factor_secret', 'NULL'),
            ('two_factor_recovery_codes', 'NULL'),
            ('two_factor_confirmed_at', 'NULL'),
            ('fecha_nacimiento', 'NULL'),
            ('remember_token', 'NULL'),
            ('ultimo_acceso', 'NULL'),
            ('fecha_ultimo_acceso', 'NULL'),
            ('fecha_registro', 'now()'),
            ('fecha_creacion', 'now()'),
            ('fecha_actualizacion', 'now()')
        ) AS v(columna, valor)
    LOOP
        IF EXISTS (
            SELECT 1 FROM pg_attribute
            WHERE attrelid = 'admin_plantilla'::regclass AND attname = cambio.columna AND NOT attisdropped
        ) THEN
            EXECUTE format('UPDATE admin_plantilla SET %I = %s', cambio.columna, cambio.valor);
        END IF;
    END LOOP;
END $$;

INSERT INTO public.usuarios OVERRIDING SYSTEM VALUE SELECT * FROM admin_plantilla;

SELECT setval(pg_get_serial_sequence('public.usuarios', 'id_usuario'), 1, true);

DO $$
DECLARE
    r record;
BEGIN
    FOR r IN SELECT * FROM refs_obligatorias WHERE destino = 'public.usuarios'::regclass LOOP
        EXECUTE format('UPDATE %s SET %I = 1', r.tabla, r.columna);
    END LOOP;
END $$;

-- ---------------------------------------------------------------------
-- 4. Identidad visual a los valores de fábrica (se conservan dominios
--    de correo, datos de registro y contacto de soporte).
-- ---------------------------------------------------------------------
DO $$
DECLARE
    cambio record;
BEGIN
    IF to_regclass('public.configuracion_organizacion') IS NULL THEN
        RETURN;
    END IF;

    FOR cambio IN
        SELECT * FROM (VALUES
            ('nombre_organizacion', quote_literal('Grand Palladium Hotels & Resorts')),
            ('logo_base64', 'NULL'),
            ('logo_mime', 'NULL'),
            ('logo_version', 'NULL'),
            ('logo_nombre_original', 'NULL'),
            ('logo_escala', '100'),
            ('logo_modo_oscuro', quote_literal('blanco')),
            ('logo_color_fondo', 'NULL'),
            ('color_primario', 'NULL')
        ) AS v(columna, valor)
    LOOP
        IF EXISTS (
            SELECT 1 FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'configuracion_organizacion' AND column_name = cambio.columna
        ) THEN
            EXECUTE format('UPDATE public.configuracion_organizacion SET %I = %s', cambio.columna, cambio.valor);
        END IF;
    END LOOP;
END $$;

-- ---------------------------------------------------------------------
-- 5. Revalidar TODAS las llaves foráneas: si algo quedó huérfano, se
--    detiene aquí con el nombre de la restricción.
-- ---------------------------------------------------------------------
SET session_replication_role = origin;

DO $$
DECLARE
    r record;
BEGIN
    FOR r IN
        SELECT conrelid::regclass AS tabla, conname, pg_get_constraintdef(oid) AS definicion
        FROM pg_constraint
        WHERE contype = 'f' AND connamespace = 'public'::regnamespace
    LOOP
        EXECUTE format('ALTER TABLE %s DROP CONSTRAINT %I, ADD CONSTRAINT %I %s', r.tabla, r.conname, r.conname, r.definicion);
    END LOOP;
END $$;

COMMIT;

-- Resumen: filas que se entregan por tabla.
SELECT t.tabla AS "Tabla que se entrega", (xpath('/row/n/text()', query_to_xml(format('SELECT COUNT(*) AS n FROM public.%I', t.tabla), false, true, '')))[1]::text::int AS "Filas"
FROM conservar t
WHERE to_regclass('public.' || quote_ident(t.tabla)) IS NOT NULL
ORDER BY 1;
