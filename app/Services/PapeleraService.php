<?php

namespace App\Services;

use App\Exceptions\PapeleraException;
use App\Support\Esquema;
use App\Support\PapeleraEntidades;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Papelera de reciclaje
|--------------------------------------------------------------------------
|
| Mover:     revisa que nada vigente dependa del registro (si algo
|            depende, se explica qué cambiar primero), copia el registro
|            y lo que le pertenece a la tabla «papelera» y lo quita de
|            sus tablas. Todo en una transacción.
| Restaurar: vuelve a insertar las filas copiadas con sus mismos ids.
| Eliminar:  borra la copia y, entonces sí, sus archivos.
| 30 días:   purgarVencidos() (comando trackit:vaciar-papelera, programado
|            a diario; también corre al abrir la papelera).
|
| Las definiciones de cada tipo están en App\Support\PapeleraEntidades.
|
*/

class PapeleraService
{
    /*
    |--------------------------------------------------------------------------
    | Consultas previas
    |--------------------------------------------------------------------------
    */

    public function etiqueta(string $tipo, int $id): ?string
    {
        $def = PapeleraEntidades::de($tipo);

        $fila = DB::selectOne(
            'SELECT '.$def['etiqueta'].' AS etiqueta FROM public."'.$def['tabla'].'" t WHERE t."'.$def['llave'].'" = ?',
            [$id]
        );

        return $fila ? trim((string) $fila->etiqueta) : null;
    }

    /*
     * Lo que impide borrar: [['que' => 'equipo(s)', 'cantidad' => 12], …]
     */
    public function bloqueos(string $tipo, int $id): array
    {
        $def = PapeleraEntidades::de($tipo);
        $bloqueos = [];

        if ($tipo === 'usuario') {
            if ((int) auth()->id() === $id) {
                $bloqueos[] = ['que' => 'Es tu propia cuenta', 'cantidad' => 0];
            }
        }

        foreach ($def['bloqueos'] as [$tabla, $condicion, $que]) {
            if (! Esquema::tieneTabla($tabla)) {
                continue;
            }

            $cantidad = (int) DB::selectOne(
                'SELECT COUNT(*) AS n FROM public."'.$tabla.'" WHERE '.$condicion,
                $this->parametros($condicion, $id)
            )->n;

            if ($cantidad > 0) {
                $bloqueos[] = ['que' => $que, 'cantidad' => $cantidad];
            }
        }

        return $bloqueos;
    }

    /*
    |--------------------------------------------------------------------------
    | Mover a la papelera
    |--------------------------------------------------------------------------
    */

    public function mover(string $tipo, int $id): int
    {
        $def = PapeleraEntidades::de($tipo);
        $etiqueta = $this->etiqueta($tipo, $id);

        if ($etiqueta === null) {
            throw new PapeleraException('Ese registro ya no existe. Actualiza la página.');
        }

        $bloqueos = $this->bloqueos($tipo, $id);

        if ($bloqueos !== []) {
            throw PapeleraException::bloqueado($bloqueos, $def['ayuda']);
        }

        $principal = [$def['tabla'], '"'.$def['llave'].'" = ?'];
        $hijos = $this->hijos($tipo, $def);

        try {
            return DB::transaction(function () use ($tipo, $id, $def, $etiqueta, $principal, $hijos): int {
                // 1. Copia: primero el registro principal y luego lo que le pertenece.
                $bloques = [];

                foreach (array_merge([$principal], $hijos) as [$tabla, $condicion]) {
                    $filas = DB::select(
                        'SELECT to_jsonb(t) AS fila FROM public."'.$tabla.'" t WHERE '.$condicion,
                        $this->parametros($condicion, $id)
                    );

                    if ($filas !== []) {
                        $bloques[] = [
                            'tabla' => $tabla,
                            'filas' => array_map(fn ($f) => json_decode($f->fila, true), $filas),
                        ];
                    }
                }

                // 2. Quitar de sus tablas: al revés (primero lo que depende).
                foreach (array_reverse($hijos) as [$tabla, $condicion]) {
                    DB::delete('DELETE FROM public."'.$tabla.'" WHERE '.$condicion, $this->parametros($condicion, $id));
                }

                DB::delete('DELETE FROM public."'.$def['tabla'].'" WHERE "'.$def['llave'].'" = ?', [$id]);

                // 3. Guardar en la papelera.
                $usuario = auth()->user();

                $idPapelera = (int) DB::table('papelera')->insertGetId([
                    'tipo' => $tipo,
                    'id_registro' => $id,
                    'etiqueta' => mb_substr($etiqueta !== '' ? $etiqueta : $def['nombre'].' #'.$id, 0, 255),
                    'detalle' => $this->detalle($bloques, $def),
                    'datos' => json_encode($bloques, JSON_UNESCAPED_UNICODE),
                    'archivos' => json_encode($this->archivos($bloques, $def), JSON_UNESCAPED_UNICODE),
                    'eliminado_por' => $usuario?->getAuthIdentifier(),
                    'eliminado_por_nombre' => $usuario ? trim(($usuario->nombres ?? '').' '.($usuario->apellido_paterno ?? '')) : null,
                    'eliminado_en' => now(),
                    'expira_en' => now()->addDays(PapeleraEntidades::DIAS),
                ], 'id_papelera');

                $this->bitacora('ELIMINAR', $def['nombre'].' «'.$etiqueta.'» se movió a la papelera.');

                return $idPapelera;
            });
        } catch (QueryException $e) {
            // Alguna relación que no está en las definiciones (llave foránea).
            if (($e->errorInfo[0] ?? null) === '23503') {
                throw new PapeleraException(
                    'Todavía hay registros que dependen de «'.$etiqueta.'». Cámbialos o bórralos primero.'
                );
            }

            throw $e;
        } finally {
            app(SystemCacheService::class)->refreshAll();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Varios a la vez (inventario: «Mover a la papelera» de la selección)
    |--------------------------------------------------------------------------
    |
    | Todo por lotes: unas cuantas consultas por tabla para todos los ids,
    | en lugar de repetir mover() por cada uno (con la base remota serían
    | cientos de consultas). Cada registro queda como un elemento propio
    | en la papelera, así se restauran o eliminan por separado.
    |
    */

    public const MAXIMO_LOTE = 100;

    /*
     * ['etiquetas' => [id => texto], 'bloqueos' => [id => [['que'=>…, 'cantidad'=>n], …]]]
     * Un id que no aparece en «etiquetas» ya no existe.
     */
    public function revisarVarios(string $tipo, array $ids): array
    {
        $def = PapeleraEntidades::de($tipo);
        $ids = $this->idsLote($ids);

        if ($ids === []) {
            return ['etiquetas' => [], 'bloqueos' => []];
        }

        $lista = $this->listaPg($ids);
        $etiquetas = [];

        foreach (DB::select(
            'SELECT t."'.$def['llave'].'" AS __id, '.$def['etiqueta'].' AS etiqueta FROM public."'.$def['tabla'].'" t '
            .'WHERE t."'.$def['llave'].'" = ANY(?::bigint[])',
            [$lista]
        ) as $fila) {
            $etiqueta = trim((string) $fila->etiqueta);
            $etiquetas[(int) $fila->__id] = $etiqueta !== '' ? $etiqueta : $def['nombre'].' #'.$fila->__id;
        }

        $bloqueos = [];

        if ($tipo === 'usuario' && in_array((int) auth()->id(), $ids, true)) {
            $bloqueos[(int) auth()->id()][] = ['que' => 'Es tu propia cuenta', 'cantidad' => 0];
        }

        foreach ($def['bloqueos'] as [$tabla, $condicion, $que]) {
            if (! Esquema::tieneTabla($tabla)) {
                continue;
            }

            foreach (DB::select(
                'SELECT __g.__id AS __id, COUNT(*) AS n FROM unnest(?::bigint[]) AS __g(__id) '
                .'JOIN public."'.$tabla.'" ON ('.$this->condicionLote($condicion).') GROUP BY __g.__id',
                [$lista]
            ) as $fila) {
                $bloqueos[(int) $fila->__id][] = ['que' => $que, 'cantidad' => (int) $fila->n];
            }
        }

        return ['etiquetas' => $etiquetas, 'bloqueos' => $bloqueos];
    }

    /*
     * Mueve los que se pueden y devuelve cuántos fueron. Los que tienen
     * algo que depende de ellos (o ya no existen) se quedan igual.
     */
    public function moverVarios(string $tipo, array $ids): int
    {
        $def = PapeleraEntidades::de($tipo);
        $revision = $this->revisarVarios($tipo, $ids);

        $movibles = array_values(array_filter(
            $this->idsLote($ids),
            fn (int $id): bool => isset($revision['etiquetas'][$id]) && empty($revision['bloqueos'][$id])
        ));

        if ($movibles === []) {
            return 0;
        }

        $lista = $this->listaPg($movibles);
        $principal = [$def['tabla'], '"'.$def['llave'].'" = ?'];
        $hijos = $this->hijos($tipo, $def);

        try {
            DB::transaction(function () use ($tipo, $def, $revision, $movibles, $lista, $principal, $hijos): void {
                // 1. Copia agrupada por registro (primero el principal, luego lo suyo).
                $bloquesPorId = [];
                $vistas = [];

                foreach (array_merge([$principal], $hijos) as [$tabla, $condicion]) {
                    $porId = [];

                    foreach (DB::select(
                        'SELECT __g.__id AS __id, to_jsonb(t) AS fila FROM unnest(?::bigint[]) AS __g(__id) '
                        .'JOIN public."'.$tabla.'" t ON ('.$this->condicionLote($condicion).')',
                        [$lista]
                    ) as $fila) {
                        // Una fila que pertenece a dos registros se guarda solo una vez.
                        $clave = $tabla.'|'.md5((string) $fila->fila);

                        if (isset($vistas[$clave])) {
                            continue;
                        }

                        $vistas[$clave] = true;
                        $porId[(int) $fila->__id][] = json_decode((string) $fila->fila, true);
                    }

                    foreach ($porId as $id => $filas) {
                        $bloquesPorId[$id][] = ['tabla' => $tabla, 'filas' => $filas];
                    }
                }

                // 2. Quitar de sus tablas: al revés (primero lo que depende).
                foreach (array_reverse($hijos) as [$tabla, $condicion]) {
                    DB::delete(
                        'DELETE FROM public."'.$tabla.'" WHERE EXISTS (SELECT 1 FROM unnest(?::bigint[]) AS __g(__id) WHERE '
                        .$this->condicionLote($condicion).')',
                        [$lista]
                    );
                }

                DB::delete(
                    'DELETE FROM public."'.$def['tabla'].'" WHERE "'.$def['llave'].'" = ANY(?::bigint[])',
                    [$lista]
                );

                // 3. Un elemento de la papelera por registro.
                $usuario = auth()->user();
                $nombreUsuario = $usuario ? trim(($usuario->nombres ?? '').' '.($usuario->apellido_paterno ?? '')) : null;
                $ahora = now();
                $filasPapelera = [];

                foreach ($movibles as $id) {
                    $bloques = $bloquesPorId[$id] ?? [];

                    $filasPapelera[] = [
                        'tipo' => $tipo,
                        'id_registro' => $id,
                        'etiqueta' => mb_substr($revision['etiquetas'][$id], 0, 255),
                        'detalle' => $this->detalle($bloques, $def),
                        'datos' => json_encode($bloques, JSON_UNESCAPED_UNICODE),
                        'archivos' => json_encode($this->archivos($bloques, $def), JSON_UNESCAPED_UNICODE),
                        'eliminado_por' => $usuario?->getAuthIdentifier(),
                        'eliminado_por_nombre' => $nombreUsuario,
                        'eliminado_en' => $ahora,
                        'expira_en' => $ahora->copy()->addDays(PapeleraEntidades::DIAS),
                    ];
                }

                foreach (array_chunk($filasPapelera, 25) as $parte) {
                    DB::table('papelera')->insert($parte);
                }

                $this->bitacora(
                    'ELIMINAR',
                    count($movibles) === 1
                        ? $def['nombre'].' «'.$revision['etiquetas'][$movibles[0]].'» se movió a la papelera.'
                        : count($movibles).' registros de tipo '.mb_strtolower($def['nombre']).' se movieron a la papelera.'
                );
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23503') {
                throw new PapeleraException(
                    'Algunos todavía tienen registros que dependen de ellos. No se movió ninguno; intenta moverlos uno por uno para ver cuál es.'
                );
            }

            throw $e;
        } finally {
            app(SystemCacheService::class)->refreshAll();
        }

        return count($movibles);
    }

    /*
    |--------------------------------------------------------------------------
    | Restaurar
    |--------------------------------------------------------------------------
    */

    public function restaurar(int $idPapelera): string
    {
        $elemento = DB::table('papelera')->where('id_papelera', $idPapelera)->first();

        if (! $elemento) {
            throw new PapeleraException('Ese elemento ya no está en la papelera.');
        }

        $bloques = json_decode((string) $elemento->datos, true) ?: [];

        try {
            DB::transaction(function () use ($bloques, $idPapelera, $elemento): void {
                foreach ($bloques as $bloque) {
                    $tabla = (string) $bloque['tabla'];

                    if (! preg_match('/^[a-z_][a-z0-9_]*$/', $tabla) || ! Esquema::tieneTabla($tabla)) {
                        continue;
                    }

                    foreach ($bloque['filas'] as $fila) {
                        // Mismos ids; OVERRIDING SYSTEM VALUE permite escribir las columnas identity.
                        DB::insert(
                            'INSERT INTO public."'.$tabla.'" OVERRIDING SYSTEM VALUE '
                            .'SELECT * FROM jsonb_populate_record(NULL::public."'.$tabla.'", ?::jsonb)',
                            [json_encode($fila, JSON_UNESCAPED_UNICODE)]
                        );
                    }
                }

                DB::table('papelera')->where('id_papelera', $idPapelera)->delete();

                $this->bitacora('RESTAURAR', '«'.$elemento->etiqueta.'» se restauró desde la papelera.');
            });
        } catch (QueryException $e) {
            $codigo = $e->errorInfo[0] ?? null;

            if ($codigo === '23505') {
                throw new PapeleraException(
                    'No se puede restaurar «'.$elemento->etiqueta.'»: ya existe otro registro con el mismo nombre, código o número de serie. Cámbialo o bórralo primero.'
                );
            }

            if ($codigo === '23503') {
                throw new PapeleraException(
                    'No se puede restaurar «'.$elemento->etiqueta.'»: depende de algo que ya no existe (por ejemplo, su marca, tipo u hotel). Restaura eso primero.'
                );
            }

            throw $e;
        } finally {
            app(SystemCacheService::class)->refreshAll();
        }

        return (string) $elemento->etiqueta;
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar definitivamente
    |--------------------------------------------------------------------------
    */

    public function eliminarDefinitivo(int $idPapelera, bool $automatico = false): void
    {
        $elemento = DB::table('papelera')->where('id_papelera', $idPapelera)->first();

        if (! $elemento) {
            return;
        }

        DB::table('papelera')->where('id_papelera', $idPapelera)->delete();

        foreach ((array) (json_decode((string) $elemento->archivos, true) ?: []) as $ruta) {
            try {
                if (is_string($ruta) && $ruta !== '') {
                    Storage::disk('local')->delete($ruta);
                }
            } catch (\Throwable) {
                // Un archivo que ya no existe no impide terminar.
            }
        }

        $this->bitacora(
            'ELIMINAR_DEFINITIVO',
            '«'.$elemento->etiqueta.'» se eliminó definitivamente'.($automatico ? ' (30 días en la papelera).' : '.')
        );
    }

    public function purgarVencidos(): int
    {
        if (! Esquema::tieneTabla('papelera')) {
            return 0;
        }

        $ids = DB::table('papelera')->where('expira_en', '<=', now())->pluck('id_papelera');

        foreach ($ids as $id) {
            $this->eliminarDefinitivo((int) $id, true);
        }

        return $ids->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Auxiliares
    |--------------------------------------------------------------------------
    */

    /*
     * Hijos que existen en la base. Para equipos se suman sus tablas de
     * especificaciones (cualquier tabla con id_equipo que no esté listada:
     * laptops, monitores, móviles…), que van justo después del equipo.
     */
    private function hijos(string $tipo, array $def): array
    {
        $hijos = array_values(array_filter(
            $def['hijos'],
            fn (array $h): bool => Esquema::tieneTabla($h[0])
        ));

        if ($tipo !== 'equipo') {
            return $hijos;
        }

        $listadas = array_merge(['equipos'], array_column($hijos, 0));

        $especificaciones = Cache::remember('papelera:tablas-equipo', now()->addHours(6), fn (): array => collect(DB::select(
            "SELECT table_name FROM information_schema.columns
             WHERE table_schema = 'public' AND column_name = 'id_equipo'
             ORDER BY table_name"
        ))->pluck('table_name')->map(fn ($t) => (string) $t)->all());

        $extra = [];

        foreach ($especificaciones as $tabla) {
            if (! in_array($tabla, $listadas, true) && preg_match('/^[a-z_][a-z0-9_]*$/', $tabla)) {
                $extra[] = [$tabla, 'id_equipo = ?'];
            }
        }

        return array_merge($extra, $hijos);
    }

    /*
     * Condición de un registro («id_equipo = ?») convertida para un lote:
     * cada ? apunta al id de la lista (unnest … AS __g(__id)). Las
     * condiciones vienen de PapeleraEntidades, nunca del usuario.
     */
    private function condicionLote(string $condicion): string
    {
        return str_replace('?', '__g.__id', $condicion);
    }

    private function idsLote(array $ids): array
    {
        return array_slice(
            array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id): bool => $id > 0))),
            0,
            self::MAXIMO_LOTE
        );
    }

    private function listaPg(array $ids): string
    {
        return '{'.implode(',', array_map('intval', $ids)).'}';
    }

    private function parametros(string $condicion, int $id): array
    {
        return array_fill(0, substr_count($condicion, '?'), $id);
    }

    private function archivos(array $bloques, array $def): array
    {
        $rutas = [];

        foreach ($def['archivos'] as [$tabla, $columna]) {
            foreach ($bloques as $bloque) {
                if ($bloque['tabla'] !== $tabla) {
                    continue;
                }

                foreach ($bloque['filas'] as $fila) {
                    if (filled($fila[$columna] ?? null)) {
                        $rutas[] = (string) $fila[$columna];
                    }
                }
            }
        }

        return array_values(array_unique($rutas));
    }

    /*
     * Texto corto de lo que se fue junto con el registro.
     */
    private function detalle(array $bloques, array $def): ?string
    {
        $total = 0;

        foreach (array_slice($bloques, 1) as $bloque) {
            $total += count($bloque['filas']);
        }

        return $total > 0 ? 'Incluye '.$total.' registro(s) relacionados' : null;
    }

    private function bitacora(string $accion, string $descripcion): void
    {
        try {
            // Transacción anidada (savepoint): si falla, no anula la operación principal.
            DB::transaction(fn () => DB::table('bitacora_auditoria')->insert([
                'id_usuario' => auth()->id(),
                'accion' => $accion,
                'modulo' => 'Papelera',
                'descripcion' => mb_substr($descripcion, 0, 250),
                'fecha_hora' => now(),
            ]));
        } catch (\Throwable) {
            // La bitácora no debe impedir la operación.
        }
    }
}
