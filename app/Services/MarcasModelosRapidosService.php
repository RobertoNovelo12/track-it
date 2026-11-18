<?php

namespace App\Services;

use App\Support\Esquema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Marcas y modelos nuevos desde el alta de un equipo
|--------------------------------------------------------------------------
|
| En el formulario de equipo se puede escribir una marca o un modelo que
| no existe (el selector envía «texto:Nombre»). Al guardar:
|   - si ya existe uno con ese nombre (sin distinguir mayúsculas), se usa;
|   - si no, se crea activo y marcado «Falta información»
|     (pendiente_revision) para completarlo en Administración.
| Así queda disponible para los demás equipos.
|
*/

class MarcasModelosRapidosService
{
    public const PREFIJO = 'texto:';

    public const LARGO_MAXIMO = 100;

    /** Nombres creados en esta petición (para el aviso al guardar). */
    private array $creados = [];

    public static function esNuevo(mixed $valor): bool
    {
        return is_string($valor) && str_starts_with($valor, self::PREFIJO);
    }

    /** Nombre escrito (sin el prefijo), sin espacios repetidos. */
    public static function nombre(string $valor, string $campo, string $etiqueta): string
    {
        $nombre = trim(preg_replace('/\s+/u', ' ', mb_substr($valor, mb_strlen(self::PREFIJO))));

        if ($nombre === '') {
            throw ValidationException::withMessages([$campo => "Escribe el nombre de la {$etiqueta}."]);
        }

        if (mb_strlen($nombre) > self::LARGO_MAXIMO) {
            throw ValidationException::withMessages([$campo => "El nombre de la {$etiqueta} no puede superar ".self::LARGO_MAXIMO.' caracteres.']);
        }

        return $nombre;
    }

    /**
     * id de la marca elegida o escrita (se crea si no existe); null si se dejó
     * vacía (algunos tipos no la exigen). Usar dentro de una transacción.
     */
    public function marca(mixed $valor, string $campo = 'idMarca'): ?int
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        if (! self::esNuevo($valor)) {
            $id = (int) $valor;

            if ($id <= 0 || ! DB::table('marcas')->where('id_marca', $id)->exists()) {
                throw ValidationException::withMessages([$campo => 'Elige una marca válida.']);
            }

            return $id;
        }

        $nombre = self::nombre($valor, $campo, 'marca');

        // Dos altas a la vez con la misma marca nueva: la segunda espera y la encuentra.
        DB::select('select pg_advisory_xact_lock(hashtext(?))', ['marca-nueva:'.mb_strtolower($nombre)]);

        $existente = DB::table('marcas')
            ->whereRaw('LOWER(BTRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->value('id_marca');

        if ($existente) {
            return (int) $existente;
        }

        $this->creados[] = ['tipo' => 'marca', 'nombre' => $nombre];

        return (int) DB::table('marcas')->insertGetId([
            'nombre' => $nombre,
            'activo' => true,
            ...$this->marcaPendiente('marcas'),
        ], 'id_marca');
    }

    /** id del modelo elegido o escrito para esa marca y tipo (se crea si no existe). */
    public function modelo(mixed $valor, ?int $idMarca, int $idTipoEquipo, string $campo = 'idModelo'): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (! self::esNuevo($valor)) {
            $id = (int) $valor;

            return $id > 0 && DB::table('modelos')->where('id_modelo', $id)->exists() ? $id : null;
        }

        // Un modelo nuevo pertenece a una marca.
        if ($idMarca === null) {
            throw ValidationException::withMessages([$campo => 'Elige o escribe la marca del modelo nuevo.']);
        }

        $nombre = self::nombre($valor, $campo, 'modelo');

        DB::select('select pg_advisory_xact_lock(hashtext(?))', ['modelo-nuevo:'.$idMarca.':'.$idTipoEquipo.':'.mb_strtolower($nombre)]);

        $existente = DB::table('modelos')
            ->where('id_marca', $idMarca)
            ->where('id_tipo_equipo', $idTipoEquipo)
            ->whereRaw('LOWER(BTRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->value('id_modelo');

        if ($existente) {
            return (int) $existente;
        }

        $this->creados[] = ['tipo' => 'modelo', 'nombre' => $nombre];

        return (int) DB::table('modelos')->insertGetId([
            'id_marca' => $idMarca,
            'id_tipo_equipo' => $idTipoEquipo,
            'nombre' => $nombre,
            'activo' => true,
            ...$this->marcaPendiente('modelos'),
        ], 'id_modelo');
    }

    public function creados(): array
    {
        return $this->creados;
    }

    /** Tras confirmar la transacción: listas de marcas y modelos al día. */
    public function refrescarSiHuboCambios(): void
    {
        if ($this->creados === []) {
            return;
        }

        app(SystemCacheService::class)->refreshModule('catalogos');
        Cache::forget('form.marcas.v2');
        Cache::forget('form.modelos.all.v2');
    }

    /** Texto para el aviso: «La marca «X» y el modelo «Y» se crearon…». */
    public function aviso(): ?string
    {
        if ($this->creados === []) {
            return null;
        }

        $partes = array_map(
            fn (array $c) => ($c['tipo'] === 'marca' ? 'la marca' : 'el modelo').' «'.$c['nombre'].'»',
            $this->creados
        );

        $lista = implode(' y ', $partes);

        $varios = count($partes) > 1;

        return ($varios ? 'Se crearon ' : 'Se creó ').$lista.'. '
            .($varios ? 'Complétalos' : 'Complétalo')
            .' en Administración › Marcas y modelos (aparece con «Falta información»).';
    }

    private function marcaPendiente(string $tabla): array
    {
        return Esquema::tieneColumna($tabla, 'pendiente_revision') ? ['pendiente_revision' => true] : [];
    }
}
