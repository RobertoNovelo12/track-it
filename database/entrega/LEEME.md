# Base de datos de entrega · Track IT

Genera `salida/track-it-entrega.sql`, un script que crea la base de datos completa
para entregar el proyecto. Supabase **no se modifica**.

## Qué conserva y qué quita

| Se conserva | Se elimina |
|---|---|
| Migraciones, roles y permisos, catálogos y estados | Usuarios (queda solo `admin`) |
| Tipos de equipo, secciones, campos y opciones | Equipos del inventario |
| Marcas, modelos y programas (con los asignados a cada tipo de equipo) | Asignaciones, reasignaciones, mantenimientos, bajas |
| Sedes, hoteles, departamentos, ubicaciones y tipos de ubicación | Proveedores y los programas instalados en cada equipo |
| | Actividad reciente, bitácora, notificaciones, sesiones, papelera |
| | Identidad visual (logo y colores vuelven a los predeterminados) |

## Generar el script

Solo necesita PHP (Herd) y la conexión a Supabase del `.env`. Solo **lee** la base;
no modifica nada. Desde la raíz del proyecto:

```powershell
php artisan trackit:exportar-entrega
# con otra contraseña temporal para el admin:
php artisan trackit:exportar-entrega --contrasena="OtraClave#2026"
```

El archivo queda en `database/entrega/salida/track-it-entrega.sql` (esa carpeta
está en `.gitignore`). Incluye la estructura completa (tablas, secuencias,
índices, llaves foráneas, funciones, disparadores y vistas) y los datos.

## Restaurar en el servidor del cliente

1. Crea una base PostgreSQL vacía (por ejemplo, un proyecto nuevo de Supabase).
2. Pon su conexión en el `.env` del proyecto.
3. Ejecuta `php artisan trackit:importar-entrega` (se niega si la base ya tiene tablas).
   Si tienes `psql`, también sirve: `psql "<cadena de conexión>" -f track-it-entrega.sql`
4. Ejecuta `php artisan migrate` (no debería haber pendientes) y `php artisan optimize:clear`.

## Primer acceso

- Usuario: `admin`
- Contraseña temporal: `TrackIT-Entrega2026!` (o la que se haya indicado al exportar)
- Al entrar, el sistema pide cambiar la contraseña y activar la verificación en dos pasos.
- Después hay que editar el perfil del administrador (nombre, correo) y cargar el logo
  de la organización en Identidad visual.
