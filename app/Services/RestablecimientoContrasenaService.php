<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Restablecimiento de contraseña por un administrador
|--------------------------------------------------------------------------
|
| Flujo (sin correos):
|   1. En «¿Olvidaste tu contraseña?» el usuario escribe su usuario o
|      correo y responde DOS preguntas de seguridad con datos de su cuenta.
|   2. Si acierta se registra una solicitud y se avisa a los
|      administradores de usuarios (centro de notificaciones).
|   3. En Seguridad y roles › Ver detalles el administrador genera una
|      contraseña temporal (solo con solicitud vigente) y se la entrega.
|   4. Al entrar, el usuario debe cambiarla (debe_cambiar_password).
|
| Preguntas posibles (se usan las dos primeras que tengan dato):
|   fecha_nacimiento   -> usuarios.fecha_nacimiento
|   numero_colaborador -> usuarios.numero_colaborador
|   marca_equipo       -> marca de un equipo asignado actualmente
|
*/

class RestablecimientoContrasenaService
{
    /** Horas que una solicitud verificada sigue vigente. */
    public const VIGENCIA_HORAS = 24;

    public const PREGUNTAS = [
        'fecha_nacimiento' => [
            'texto' => '¿Cuál es tu fecha de nacimiento?',
            'tipo' => 'date',
        ],
        'numero_colaborador' => [
            'texto' => '¿Cuál es tu número de colaborador?',
            'tipo' => 'text',
        ],
        'marca_equipo' => [
            'texto' => 'Tienes un equipo asignado, ¿de qué marca es?',
            'tipo' => 'marca',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Buscar cuenta y elegir preguntas
    |--------------------------------------------------------------------------
    */

    public function buscarUsuario(string $identificador): ?object
    {
        $identificador = trim($identificador);

        if ($identificador === '') {
            return null;
        }

        return DB::table('usuarios as u')
            ->join('estados_usuario as eu', 'eu.id_estado_usuario', '=', 'u.id_estado_usuario')
            ->whereRaw('UPPER(eu.clave) = ?', ['ACTIVO'])
            ->where(function ($query) use ($identificador) {
                $query->whereRaw('LOWER(u.correo) = ?', [mb_strtolower($identificador)])
                    ->orWhere('u.username', $identificador);
            })
            ->first([
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.username',
                'u.fecha_nacimiento',
                'u.numero_colaborador',
            ]);
    }

    /** Claves de las dos preguntas para el usuario (o [] si no hay datos suficientes). */
    public function preguntasPara(object $usuario): array
    {
        $disponibles = [];

        if (filled($usuario->fecha_nacimiento ?? null)) {
            $disponibles[] = 'fecha_nacimiento';
        }

        if (filled($usuario->numero_colaborador ?? null)) {
            $disponibles[] = 'numero_colaborador';
        }

        if ($this->marcasAsignadas((int) $usuario->id_usuario, $usuario->numero_colaborador ?? null) !== []) {
            $disponibles[] = 'marca_equipo';
        }

        return count($disponibles) >= 2
            ? array_slice($disponibles, 0, 2)
            : [];
    }

    /** Marcas (id_marca) de los equipos asignados actualmente al usuario. */
    public function marcasAsignadas(int $idUsuario, ?string $numeroColaborador = null): array
    {
        $colaboradores = app(ColaboradoresService::class);

        // Sin forma de relacionar asignaciones con la cuenta no hay pregunta.
        if (! $colaboradores->disponible() && blank($numeroColaborador)) {
            return [];
        }

        return DB::table('asignaciones as a')
            ->join('equipos as e', 'e.id_equipo', '=', 'a.id_equipo')
            ->whereNull('a.fecha_fin')
            ->whereNotNull('e.id_marca')
            ->where(function ($query) use ($colaboradores, $idUsuario, $numeroColaborador) {
                if ($colaboradores->disponible()) {
                    $query->where('a.id_usuario', $idUsuario);
                }

                if (filled($numeroColaborador)) {
                    $query->orWhere('a.numero_colaborador', trim((string) $numeroColaborador));
                }
            })
            ->distinct()
            ->pluck('e.id_marca')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Opciones para la pregunta de la marca (todas las marcas del catálogo). */
    public function opcionesMarca(): array
    {
        return DB::table('marcas')
            ->orderBy('nombre')
            ->get(['id_marca', 'nombre'])
            ->map(fn ($marca) => ['value' => (string) $marca->id_marca, 'label' => (string) $marca->nombre])
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar respuestas
    |--------------------------------------------------------------------------
    */

    public function verificar(int $idUsuario, array $preguntas, array $respuestas): bool
    {
        $usuario = DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->first(['id_usuario', 'fecha_nacimiento', 'numero_colaborador']);

        if (! $usuario || count($preguntas) < 2) {
            return false;
        }

        foreach ($preguntas as $clave) {
            $respuesta = trim((string) ($respuestas[$clave] ?? ''));

            $correcta = match ($clave) {
                'fecha_nacimiento' => $this->mismaFecha($respuesta, $usuario->fecha_nacimiento),
                'numero_colaborador' => $respuesta !== ''
                    && $this->soloDigitos($respuesta) === $this->soloDigitos((string) $usuario->numero_colaborador),
                'marca_equipo' => ctype_digit($respuesta)
                    && in_array((int) $respuesta, $this->marcasAsignadas($idUsuario, $usuario->numero_colaborador), true),
                default => false,
            };

            if (! $correcta) {
                return false;
            }
        }

        return true;
    }

    private function mismaFecha(string $respuesta, ?string $guardada): bool
    {
        if ($respuesta === '' || blank($guardada)) {
            return false;
        }

        try {
            return Carbon::parse($respuesta)->toDateString() === Carbon::parse($guardada)->toDateString();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function soloDigitos(string $valor): string
    {
        return ltrim(preg_replace('/\D+/', '', $valor) ?? '', '0');
    }

    /*
    |--------------------------------------------------------------------------
    | Solicitudes
    |--------------------------------------------------------------------------
    */

    /** Registra la solicitud verificada y avisa a los administradores. */
    public function registrarSolicitud(object $usuario, ?string $ip): void
    {
        if (! $this->solicitudVigente((int) $usuario->id_usuario)) {
            DB::table('solicitudes_restablecimiento')->insert([
                'id_usuario' => (int) $usuario->id_usuario,
                'fecha_solicitud' => now(),
                'ip' => $ip,
            ]);
        }

        $nombre = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellido_paterno ?? ''));
        $notificaciones = app(NotificacionService::class);

        $notificaciones->notificar(
            'usuarios',
            'Restablecimiento de contraseña',
            $nombre . ' (' . $usuario->username . ') respondió sus preguntas de seguridad y necesita una contraseña temporal. Ábrelo en Seguridad y roles › Ver detalles.',
            route('usuarios.index', [], false),
            $notificaciones->administradoresDeUsuarios(),
            false
        );

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => (int) $usuario->id_usuario,
            'accion' => 'RESTABLECIMIENTO_SOLICITADO',
            'modulo' => 'SEGURIDAD',
            'descripcion' => 'El usuario respondió sus preguntas de seguridad y solicitó restablecer su contraseña.',
            'fecha_hora' => now(),
        ]);
    }

    /** Fecha de la solicitud vigente (sin atender y dentro de la vigencia), o null. */
    public function solicitudVigente(int $idUsuario): ?string
    {
        $fecha = DB::table('solicitudes_restablecimiento')
            ->where('id_usuario', $idUsuario)
            ->whereNull('fecha_atencion')
            ->where('fecha_solicitud', '>=', now()->subHours(self::VIGENCIA_HORAS))
            ->orderByDesc('fecha_solicitud')
            ->value('fecha_solicitud');

        return $fecha !== null ? (string) $fecha : null;
    }

    /** Solicitudes vigentes con los datos del usuario (para Seguridad y roles). */
    public function pendientes(): array
    {
        try {
            return DB::table('solicitudes_restablecimiento as s')
                ->join('usuarios as u', 'u.id_usuario', '=', 's.id_usuario')
                ->whereNull('s.fecha_atencion')
                ->where('s.fecha_solicitud', '>=', now()->subHours(self::VIGENCIA_HORAS))
                ->orderByDesc('s.fecha_solicitud')
                ->get([
                    's.id_usuario',
                    's.fecha_solicitud',
                    'u.username',
                    'u.correo',
                    'u.nombres',
                    'u.apellido_paterno',
                    'u.apellido_materno',
                ])
                ->unique('id_usuario')
                ->map(fn ($fila) => [
                    'id' => (int) $fila->id_usuario,
                    'nombre' => trim(implode(' ', array_filter([$fila->nombres, $fila->apellido_paterno, $fila->apellido_materno]))),
                    'username' => (string) $fila->username,
                    'correo' => (string) $fila->correo,
                    'fecha' => (string) $fila->fecha_solicitud,
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            // Sin la migración todavía: no hay solicitudes.
            return [];
        }
    }

    /** id_usuario => fecha de su solicitud vigente, para varios usuarios. */
    public function solicitudesVigentes(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('solicitudes_restablecimiento')
            ->whereIn('id_usuario', $ids)
            ->whereNull('fecha_atencion')
            ->where('fecha_solicitud', '>=', now()->subHours(self::VIGENCIA_HORAS))
            ->selectRaw('id_usuario, MAX(fecha_solicitud) as fecha')
            ->groupBy('id_usuario')
            ->pluck('fecha', 'id_usuario')
            ->mapWithKeys(fn ($fecha, $id) => [(int) $id => (string) $fecha])
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Restablecer (administrador)
    |--------------------------------------------------------------------------
    */

    /** Asigna una contraseña temporal y la devuelve (se muestra una sola vez). */
    public function restablecer(int $idUsuario, int $idAdministrador): string
    {
        $temporal = $this->generarTemporal();

        DB::transaction(function () use ($idUsuario, $idAdministrador, $temporal) {
            DB::table('usuarios')
                ->where('id_usuario', $idUsuario)
                ->update([
                    'password_hash' => Hash::make($temporal),
                    'debe_cambiar_password' => true,
                ]);

            DB::table('solicitudes_restablecimiento')
                ->where('id_usuario', $idUsuario)
                ->whereNull('fecha_atencion')
                ->update([
                    'fecha_atencion' => now(),
                    'id_atendio' => $idAdministrador,
                ]);
        });

        return $temporal;
    }

    /**
     * Contraseña temporal legible que cumple la política del sistema
     * (8+ caracteres, mayúscula, número y símbolo, sin espacios).
     * Sin caracteres que se confunden (I, l, O, 0, 1).
     */
    public function generarTemporal(): string
    {
        $mayusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $minusculas = 'abcdefghijkmnpqrstuvwxyz';
        $numeros = '23456789';
        $simbolos = '#$%*+-!?';

        $tomar = fn (string $juego, int $cantidad): array => array_map(
            fn () => $juego[random_int(0, strlen($juego) - 1)],
            range(1, $cantidad)
        );

        $caracteres = array_merge(
            $tomar($mayusculas, 3),
            $tomar($minusculas, 4),
            $tomar($numeros, 3),
            $tomar($simbolos, 1)
        );

        // Mezcla con random_int (criptográficamente segura).
        for ($i = count($caracteres) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        return implode('', $caracteres);
    }
}
