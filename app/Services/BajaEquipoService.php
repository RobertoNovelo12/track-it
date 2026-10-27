<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Baja de un equipo (sin fotografías)
|--------------------------------------------------------------------------
|
| Mismos pasos que EquipoBaja::confirmar (registro en «bajas», estado BAJA,
| cierre de asignaciones y bitácora). Lo usa la baja múltiple «todas en un
| documento»: las fotografías y el acta van en el documento conjunto.
|
| darDeBaja() debe llamarse dentro de una transacción; despuesDeBajas()
| se llama una vez confirmada.
|
*/
class BajaEquipoService
{
    /**
     * Devuelve el folio de la baja.
     */
    public function darDeBaja(
        int $idEquipo,
        string $motivo,
        string $fecha,
        ?string $comentarios,
        string $autoriza,
        int $usuarioId,
        ?string $referencia = null,
        array $firmas = [],
        ?string $numeroTicket = null
    ): string {
        $equipo = DB::table('equipos')
            ->where('id_equipo', $idEquipo)
            ->lockForUpdate()
            ->first(['id_equipo', 'codigo_inventario', 'id_estado_activo']);

        if (! $equipo) {
            throw ValidationException::withMessages(['seleccionados' => 'Uno de los equipos seleccionados ya no existe.']);
        }

        $codigo = $equipo->codigo_inventario ?: 'Sin código';

        if (DB::table('bajas')->where('id_equipo', $idEquipo)->exists()) {
            throw ValidationException::withMessages(['seleccionados' => "El equipo {$codigo} ya se encuentra dado de baja."]);
        }

        $idMotivoOtro = $this->valorCatalogo('motivo_baja', 'OTRO');
        $idEstadoCompletada = $this->valorCatalogo('estado_baja', 'COMPLETADA');

        $idEstadoEquipoBaja = DB::table('estados_equipo')
            ->where('clave', 'BAJA')
            ->where('activo', true)
            ->value('id_estado_equipo');

        if ($idMotivoOtro === null || $idEstadoCompletada === null || $idEstadoEquipoBaja === null) {
            throw ValidationException::withMessages(['seleccionados' => 'Faltan valores de catálogo para registrar la baja (motivo OTRO, estado COMPLETADA o estado BAJA).']);
        }

        if ((int) $equipo->id_estado_activo === (int) $idEstadoEquipoBaja) {
            throw ValidationException::withMessages(['seleccionados' => "El equipo {$codigo} ya tiene estado BAJA."]);
        }

        $ultimoId = DB::table('bajas')->orderByDesc('id_baja')->lockForUpdate()->value('id_baja');

        $folio = sprintf('BAJA-%s-%02d', now()->format('YmdHisv'), ((int) ($ultimoId ?? 0)) + 1);

        $comentarios = trim((string) $comentarios);

        DB::table('bajas')->insert([
            'folio' => $folio,
            'numero_ticket' => $numeroTicket,
            'id_equipo' => $idEquipo,
            'id_motivo_baja' => (int) $idMotivoOtro,
            'motivo_baja' => trim($motivo),
            'id_estado_baja' => (int) $idEstadoCompletada,
            'fecha_baja' => $fecha,
            'comentarios' => $comentarios !== '' ? $comentarios : null,
            'realizado_por' => $usuarioId,
            'autoriza' => trim($autoriza),
            'firmas' => json_encode($firmas, JSON_UNESCAPED_UNICODE),
            'ruta_pdf' => null,
            'fecha_registro' => now(),
        ]);

        DB::table('equipos')
            ->where('id_equipo', $idEquipo)
            ->update(['id_estado_activo' => (int) $idEstadoEquipoBaja]);

        DB::table('asignaciones')
            ->where('id_equipo', $idEquipo)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => now()]);

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => $usuarioId,
            'accion' => 'EQUIPO_DADO_DE_BAJA',
            'modulo' => 'EQUIPOS',
            'descripcion' => sprintf(
                'Se dio de baja el equipo %s. Folio: %s.%s Motivo: %s',
                $codigo,
                $folio,
                $referencia ? ' Acta: ' . $referencia . '.' : '',
                trim($motivo)
            ),
            'fecha_hora' => now(),
        ]);

        return $folio;
    }

    /**
     * Cachés, bandejas de recibidos y notificación (después del commit).
     */
    public function despuesDeBajas(array $idsEquipos): void
    {
        if ($idsEquipos === []) {
            return;
        }

        $cache = app(SystemCacheService::class);
        $cache->refreshModule('dashboard');
        $cache->refreshModule('equipos');

        foreach ($idsEquipos as $id) {
            app(EquiposRecibidosService::class)->atenderPorBaja((int) $id);
            Cache::forget('equipos.edit.base.' . $id);
        }

        Cache::forever('equipos.index.version', (int) Cache::get('equipos.index.version', 1) + 1);

        $notificaciones = app(NotificacionService::class);

        $notificaciones->notificar(
            'equipos',
            count($idsEquipos) === 1 ? 'Equipo dado de baja' : 'Equipos dados de baja',
            $notificaciones->nombreActor()
                . (count($idsEquipos) === 1
                    ? ' dio de baja el equipo ' . $notificaciones->etiquetaEquipo((int) $idsEquipos[0]) . '.'
                    : ' dio de baja ' . count($idsEquipos) . ' equipos en un solo documento.'),
            count($idsEquipos) === 1
                ? route('equipos.show', (int) $idsEquipos[0], false)
                : route('equipos.index', [], false)
        );
    }

    private function valorCatalogo(string $catalogo, string $clave): mixed
    {
        return DB::table('catalogo_valores as cv')
            ->join('catalogos as c', 'c.id_catalogo', '=', 'cv.id_catalogo')
            ->where('c.clave', $catalogo)
            ->where('cv.clave', $clave)
            ->where('cv.activo', true)
            ->value('cv.id_valor');
    }
}
