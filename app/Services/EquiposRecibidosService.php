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
        $numero = CodigoBarras::numero($leido);

        if ($numero === null || strlen($numero) > 40) {
            return null;
        }

        $equipo = DB::table('equipos as e')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')
            ->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
            ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
            ->whereRaw("REGEXP_REPLACE(e.codigo_inventario, '[^0-9]', '', 'g') = ?", [$numero])
            ->orderBy('e.id_equipo')
            ->first([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'ee.nombre as estado',
                'ee.clave as estado_clave',
            ]);

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

    public function enviar(int $idUsuario, int $idEquipo): string
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
            ]);
        } catch (QueryException $e) {
            // Dos envíos simultáneos del mismo equipo: el índice único lo evita.
            if (($e->errorInfo[0] ?? null) === '23505') {
                return 'duplicado';
            }

            throw $e;
        }

        return 'enviado';
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

        return DB::table('equipos_recibidos as r')
            ->join('equipos as e', 'e.id_equipo', '=', 'r.id_equipo')
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
            ])
            ->map(fn ($fila): array => [
                'id' => (int) $fila->id_recibido,
                'id_equipo' => (int) $fila->id_equipo,
                'codigo' => (string) $fila->codigo_inventario,
                'nombre' => (string) $fila->nombre_equipo,
                'tipo' => (string) ($fila->tipo ?? ''),
                'estado' => (string) ($fila->estado ?? ''),
                'dado_de_baja' => strtoupper((string) $fila->estado_clave) === 'BAJA',
                'recibido_en' => $fila->recibido_en,
            ])
            ->all();
    }

    public function contarPendientes(int $idUsuario): int
    {
        if (! $this->disponible()) {
            return 0;
        }

        return DB::table('equipos_recibidos')
            ->where('id_usuario', $idUsuario)
            ->whereNull('atendido_en')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Atender
    |--------------------------------------------------------------------------
    */

    public function atender(int $idUsuario, int $idRecibido): bool
    {
        return DB::table('equipos_recibidos')
            ->where('id_recibido', $idRecibido)
            ->where('id_usuario', $idUsuario)
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'manual']) > 0;
    }

    public function atenderTodos(int $idUsuario): int
    {
        return DB::table('equipos_recibidos')
            ->where('id_usuario', $idUsuario)
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'manual']);
    }

    /*
     * Al completar la baja de un equipo sale de todas las bandejas.
     */
    public function atenderPorBaja(int $idEquipo): void
    {
        if (! $this->disponible()) {
            return;
        }

        DB::table('equipos_recibidos')
            ->where('id_equipo', $idEquipo)
            ->whereNull('atendido_en')
            ->update(['atendido_en' => now(), 'motivo_atencion' => 'baja']);
    }
}
