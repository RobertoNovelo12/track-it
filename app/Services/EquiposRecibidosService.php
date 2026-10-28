<?php

namespace App\Services;

use App\Support\CodigoBarras;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Equipos recibidos desde el teléfono (bandeja personal)
|--------------------------------------------------------------------------
|
| Flujo: el teléfono escanea el código de barras (solo números del
| código de inventario) → buscarPorCodigo() → «Enviar a la PC» →
| enviar(). En la PC, el mismo usuario los ve en la barra superior
| (Livewire\EquiposRecibidosPanel) y los atiende: al completar la baja
| salen solos (atenderPorBaja) o se marcan a mano (atender).
|
*/

class EquiposRecibidosService
{
    /*
    |--------------------------------------------------------------------------
    | Caché de la bandeja
    |--------------------------------------------------------------------------
    |
    | La bandeja aparece en la barra superior de TODAS las páginas y se
    | revisa cada 15 s: sin caché eran 2 consultas por página (≈300 ms con
    | la base remota). Se guarda el conteo y la lista por usuario con una
    | «versión» global: cualquier envío o atención sube la versión y todas
    | las bandejas se vuelven a consultar. La lista además caduca a los
    | 60 s porque muestra nombre y estado del equipo, que se editan en
    | otras pantallas.
    |
    */

    private const VERSION_CACHE = 'equipos_recibidos:version';

    private function versionCache(): int
    {
        return (int) Cache::get(self::VERSION_CACHE, 1);
    }

    private function invalidarCache(): void
    {
        Cache::forever(self::VERSION_CACHE, $this->versionCache() + 1);
    }

    /*
     * ¿Ya se corrió la migración? Solo se guarda el «sí».
     */
    public function disponible(): bool
    {
        if (Cache::get('trackit.equipos_recibidos.tabla.v1') === true) {
            return true;
        }

        $existe = Schema::hasTable('equipos_recibidos');

        if ($existe) {
            Cache::forever('trackit.equipos_recibidos.tabla.v1', true);
        }

        return $existe;
    }

    /*
     * ¿Ya se corrió la migración de códigos no registrados? Solo se guarda el «sí».
     */
    public function admiteCodigos(): bool
    {
        if (Cache::get('trackit.equipos_recibidos.codigo.v1') === true) {
            return true;
        }

        $existe = $this->disponible() && Schema::hasColumn('equipos_recibidos', 'codigo');

        if ($existe) {
            Cache::forever('trackit.equipos_recibidos.codigo.v1', true);
        }

        return $existe;
    }

    /*
     * ¿Ya se corrió la migración del remitente? Solo se guarda el «sí».
     */
    public function admiteRemitente(): bool
    {
        if (Cache::get('trackit.equipos_recibidos.remitente.v1') === true) {
            return true;
        }

        $existe = $this->disponible() && Schema::hasColumn('equipos_recibidos', 'id_remitente');

        if ($existe) {
            Cache::forever('trackit.equipos_recibidos.remitente.v1', true);
        }

        return $existe;
    }

    /*
    |--------------------------------------------------------------------------
    | A quién se puede enviar
    |--------------------------------------------------------------------------
    |
    | Usuarios activos con permiso de gestionar (la bandeja solo se muestra
    | a ellos), sin incluir a quien envía.
    |
    */

    public function destinatarios(int $idUsuarioActual): array
    {
        $nivelDisponible = Schema::hasColumn('roles', 'nivel');

        return DB::table('usuarios as u')
            ->join('estados_usuario as eu', 'eu.id_estado_usuario', '=', 'u.id_estado_usuario')
            ->leftJoin('roles as r', 'r.id_rol', '=', 'u.id_rol')
            ->leftJoin('unidades_organizativas as uo', 'uo.id_unidad', '=', 'u.id_unidad')
            ->where('eu.clave', 'ACTIVO')
            ->where('u.id_usuario', '<>', $idUsuarioActual)
            ->when($nivelDisponible, fn ($query) => $query
                ->where('r.activo', true)
                ->whereIn('r.nivel', ['admin', 'estandar']))
            ->orderBy('u.nombres')
            ->orderBy('u.apellido_paterno')
            ->get([
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.username',
                'r.nombre as rol',
                'uo.nombre as unidad',
            ])
            ->map(fn ($fila): array => [
                'id' => (int) $fila->id_usuario,
                'nombre' => trim(implode(' ', array_filter([$fila->nombres, $fila->apellido_paterno, $fila->apellido_materno]))) ?: (string) $fila->username,
                'detalle' => implode(' · ', array_filter([$fila->rol, $fila->unidad])),
                'usuario' => (string) $fila->username,
            ])
            ->values()
            ->all();
    }

    public function puedeRecibir(int $idDestinatario, int $idUsuarioActual): bool
    {
        return in_array($idDestinatario, array_column($this->destinatarios($idUsuarioActual), 'id'), true);
    }

    /** Código leído tal como se guardará (sin espacios de más, máx. 100). */
    public function normalizarCodigo(string $leido): string
    {
        return mb_substr(trim(preg_replace('/\s+/', ' ', $leido) ?? ''), 0, 100);
    }

    /*
    |--------------------------------------------------------------------------
    | Buscar el equipo de un código escaneado
    |--------------------------------------------------------------------------
    |
    | Acepta «000061» (código de barras) o «ACT-000061». Compara solo los
    | dígitos del código de inventario.
    |
    */

    public function buscarPorCodigo(string $leido): ?array
    {
        $leido = $this->normalizarCodigo($leido);

        if ($leido === '') {
            return null;
        }

        $consulta = fn () => DB::table('equipos as e')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')
            ->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
            ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
            ->orderBy('e.id_equipo');

        $columnas = [
            'e.id_equipo',
            'e.codigo_inventario',
            'e.nombre_equipo',
            'te.nombre as tipo',
            'ma.nombre as marca',
            'mo.nombre as modelo',
            'ee.nombre as estado',
            'ee.clave as estado_clave',
        ];

        $equipo = null;

        /*
         * 1) Código de inventario: nuestro código de barras (solo dígitos)
         *    o el código escrito completo («ACT-000061»). Un número de serie
         *    con letras y dígitos NO se compara por sus dígitos, para no
         *    confundirlo con otro equipo.
         */
        $numero = CodigoBarras::numero($leido);

        if ($numero !== null && strlen($numero) <= 40 && (ctype_digit($leido) || preg_match('/^[A-Za-z]{1,6}-?\d+$/', $leido))) {
            $equipo = $consulta()
                ->whereRaw("REGEXP_REPLACE(e.codigo_inventario, '[^0-9]', '', 'g') = ?", [$numero])
                ->when(! ctype_digit($leido), fn ($query) => $query->whereRaw('UPPER(e.codigo_inventario) = ?', [mb_strtoupper($leido)]))
                ->first($columnas);
        }

        // 2) Número de serie exacto (sin distinguir mayúsculas).
        if (! $equipo) {
            $equipo = $consulta()
                ->whereRaw('UPPER(TRIM(e.numero_serie)) = ?', [mb_strtoupper($leido)])
                ->first($columnas);
        }

        if (! $equipo) {
            return null;
        }

        $dadoDeBaja = strtoupper((string) $equipo->estado_clave) === 'BAJA'
            || DB::table('bajas')->where('id_equipo', $equipo->id_equipo)->exists();

        return [
            'id' => (int) $equipo->id_equipo,
            'codigo' => (string) $equipo->codigo_inventario,
            'nombre' => (string) $equipo->nombre_equipo,
            'detalle' => trim(implode(' ', array_filter([$equipo->tipo, $equipo->marca, $equipo->modelo]))),
            'estado' => (string) ($equipo->estado ?? ''),
            'dado_de_baja' => $dadoDeBaja,
            'url' => route('equipos.show', (int) $equipo->id_equipo),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Enviar a la bandeja del usuario
    |--------------------------------------------------------------------------
    |
    | Devuelve 'enviado' o 'duplicado' (ya estaba pendiente).
    |
    */

    public function enviar(int $idUsuario, int $idEquipo, ?int $idRemitente = null): string
    {
        $pendiente = DB::table('equipos_recibidos')
            ->where('id_usuario', $idUsuario)
            ->where('id_equipo', $idEquipo)
            ->whereNull('atendido_en')
            ->exists();

        if ($pendiente) {
            return 'duplicado';
        }

        try {
            DB::table('equipos_recibidos')->insert([
                'id_equipo' => $idEquipo,
                'id_usuario' => $idUsuario,
                'recibido_en' => now(),
            ] + $this->columnaRemitente($idUsuario, $idRemitente));
        } catch (QueryException $e) {
            // Dos envíos simultáneos del mismo equipo: el índice único lo evita.
            if (($e->errorInfo[0] ?? null) === '23505') {
                return 'duplicado';
            }

            throw $e;
        }

        $this->invalidarCache();

        return 'enviado';
    }

    /*
     * Código que no está en el inventario: se guarda el texto leído para
     * darlo de alta desde la PC (se usará como número de serie).
     */
    public function enviarCodigo(int $idUsuario, string $codigo, ?int $idRemitente = null): string
    {
        $codigo = $this->normalizarCodigo($codigo);

        $pendiente = DB::table('equipos_recibidos')
            ->where('id_usuario', $idUsuario)
            ->whereNull('id_equipo')
            ->where('codigo', $codigo)
            ->whereNull('atendido_en')
            ->exists();

        if ($pendiente) {
            return 'duplicado';
        }

        try {
            DB::table('equipos_recibidos')->insert([
                'id_equipo' => null,
                'codigo' => $codigo,
                'id_usuario' => $idUsuario,
                'recibido_en' => now(),
            ] + $this->columnaRemitente($idUsuario, $idRemitente));
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23505') {
                return 'duplicado';
            }

            throw $e;
        }

        $this->invalidarCache();

        return 'enviado';
    }

    /*
     * Solo se guarda cuando lo envía otra persona (y existe la columna).
     */
    private function columnaRemitente(int $idUsuario, ?int $idRemitente): array
    {
        return $idRemitente !== null && $idRemitente !== $idUsuario && $this->admiteRemitente()
            ? ['id_remitente' => $idRemitente]
            : [];
    }

    /*
     * Al dar de alta un equipo con ese número de serie, el código
     * pendiente sale de todas las bandejas.
     */
    public function atenderPorSerie(?string $numeroSerie): void
    {
        $numeroSerie = $this->normalizarCodigo((string) $numeroSerie);

        if ($numeroSerie === '' || ! $this->admiteCodigos()) {
            return;
        }

        $atendidos = DB::table('equipos_recibidos')
            ->whereNull('id_equipo')
            ->whereRaw('UPPER(codigo) = ?', [mb_strtoupper($numeroSerie)])
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'alta']);

        if ($atendidos > 0) {
            $this->invalidarCache();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bandeja
    |--------------------------------------------------------------------------
    */

    public function pendientes(int $idUsuario, int $limite = 100): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return Cache::remember(
            'equipos_recibidos:'.$this->versionCache().':lista:'.$idUsuario.':'.$limite,
            60,
            fn (): array => $this->consultarPendientes($idUsuario, $limite)
        );
    }

    private function consultarPendientes(int $idUsuario, int $limite): array
    {
        $codigos = $this->admiteCodigos();
        $remitente = $this->admiteRemitente();

        return DB::table('equipos_recibidos as r')
            ->when($remitente, fn ($query) => $query->leftJoin('usuarios as ur', 'ur.id_usuario', '=', 'r.id_remitente'))
            ->leftJoin('equipos as e', 'e.id_equipo', '=', 'r.id_equipo')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
            ->where('r.id_usuario', $idUsuario)
            ->whereNull('r.atendido_en')
            ->orderByDesc('r.recibido_en')
            ->limit($limite)
            ->get([
                'r.id_recibido',
                'r.recibido_en',
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'te.nombre as tipo',
                'ee.nombre as estado',
                'ee.clave as estado_clave',
                $codigos ? 'r.codigo as codigo_leido' : DB::raw('NULL as codigo_leido'),
                $remitente
                    ? DB::raw("TRIM(CONCAT(COALESCE(ur.nombres, ''), ' ', COALESCE(ur.apellido_paterno, ''))) as remitente")
                    : DB::raw('NULL as remitente'),
            ])
            ->map(fn ($fila): array => [
                'id' => (int) $fila->id_recibido,
                'id_equipo' => $fila->id_equipo !== null ? (int) $fila->id_equipo : null,
                // Sin equipo: código leído que no está en el inventario (número de serie).
                'nuevo' => $fila->id_equipo === null,
                'codigo_leido' => (string) ($fila->codigo_leido ?? ''),
                'codigo' => (string) ($fila->codigo_inventario ?? ''),
                'nombre' => (string) $fila->nombre_equipo,
                'tipo' => (string) ($fila->tipo ?? ''),
                'estado' => (string) ($fila->estado ?? ''),
                'dado_de_baja' => strtoupper((string) $fila->estado_clave) === 'BAJA',
                'recibido_en' => $fila->recibido_en,
                // Quién lo envió (vacío si fue el mismo usuario).
                'remitente' => trim((string) ($fila->remitente ?? '')),
            ])
            ->all();
    }

    public function contarPendientes(int $idUsuario): int
    {
        if (! $this->disponible()) {
            return 0;
        }

        return (int) Cache::remember(
            'equipos_recibidos:'.$this->versionCache().':conteo:'.$idUsuario,
            600,
            fn (): int => DB::table('equipos_recibidos')
                ->where('id_usuario', $idUsuario)
                ->whereNull('atendido_en')
                ->count()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Atender
    |--------------------------------------------------------------------------
    */

    public function atender(int $idUsuario, int $idRecibido): bool
    {
        $atendido = DB::table('equipos_recibidos')
            ->where('id_recibido', $idRecibido)
            ->where('id_usuario', $idUsuario)
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'manual']) > 0;

        if ($atendido) {
            $this->invalidarCache();
        }

        return $atendido;
    }

    public function idsPendientes(int $idUsuario): array
    {
        return DB::table('equipos_recibidos')
            ->where('id_usuario', $idUsuario)
            ->whereNull('atendido_en')
            ->pluck('id_recibido')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /*
     * «Deshacer» del aviso: vuelven a la bandeja los que el usuario marcó
     * a mano en los últimos 10 minutos. Si mientras tanto llegó otra vez
     * el mismo equipo (ya está pendiente), ese se omite.
     */
    public function restaurar(int $idUsuario, array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn (int $id) => $id > 0));

        if ($ids === []) {
            return 0;
        }

        $restaurados = 0;

        foreach (array_slice($ids, 0, 500) as $id) {
            try {
                $restaurados += DB::table('equipos_recibidos')
                    ->where('id_recibido', $id)
                    ->where('id_usuario', $idUsuario)
                    ->where('motivo_atencion', 'manual')
                    ->where('atendido_en', '>=', now()->subMinutes(10))
                    ->update(['atendido_en' => null, 'motivo_atencion' => null]);
            } catch (QueryException $e) {
                if (($e->errorInfo[0] ?? null) !== '23505') {
                    throw $e;
                }
            }
        }

        if ($restaurados > 0) {
            $this->invalidarCache();
        }

        return $restaurados;
    }

    public function atenderTodos(int $idUsuario): int
    {
        $atendidos = DB::table('equipos_recibidos')
            ->where('id_usuario', $idUsuario)
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'manual']);

        if ($atendidos > 0) {
            $this->invalidarCache();
        }

        return $atendidos;
    }

    /*
     * Al completar la baja de un equipo sale de todas las bandejas.
     */
    public function atenderPorBaja(int $idEquipo): void
    {
        if (! $this->disponible()) {
            return;
        }

        $atendidos = DB::table('equipos_recibidos')
            ->where('id_equipo', $idEquipo)
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'baja']);

        if ($atendidos > 0) {
            $this->invalidarCache();
        }
    }
}
