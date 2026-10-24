<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Búsqueda tolerante a errores de escritura
|--------------------------------------------------------------------------
|
| - Sin acentos ni mayúsculas: «movil» encuentra «Móvil» y al revés.
| - Ignora signos tecleados por accidente: «laptop{», «[hp]», «dell;»
|   (las llaves y corchetes están junto a Enter). Se conservan los que
|   forman parte de códigos y series: guion, punto, guion bajo y diagonal.
| - Códigos y series también sin guiones: «act000061» = «ACT-000061».
|
| Funciona en Postgres sin extensiones (translate en lugar de unaccent).
|
*/
class BusquedaTolerante
{
    private const CON_ACENTO = 'áàäâãéèëêíìïîóòöôõúùüûñçÁÀÄÂÃÉÈËÊÍÌÏÎÓÒÖÔÕÚÙÜÛÑÇ';

    private const SIN_ACENTO = 'aaaaaeeeeiiiiooooouuuuncaaaaaeeeeiiiiooooouuuunc';

    /**
     * Texto escrito → términos limpios (minúsculas, sin acentos ni signos sueltos).
     */
    public static function terminos(?string $texto): array
    {
        $texto = Str::lower(Str::ascii((string) $texto));

        // Todo lo que no sea letra, número o signo de código se vuelve espacio.
        $texto = preg_replace('/[^a-z0-9\-\.\_\/]+/', ' ', $texto) ?? '';

        return collect(preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            // Signos de código sueltos («-», «.») no son un término.
            ->map(fn (string $termino) => trim($termino, '-._/'))
            ->filter(fn (string $termino) => $termino !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Expresión SQL de la columna en minúsculas y sin acentos.
     */
    public static function columna(string $columna): string
    {
        return "translate(lower(COALESCE(CAST({$columna} AS TEXT), '')), '"
            . self::CON_ACENTO . "', '" . self::SIN_ACENTO . "')";
    }

    /**
     * Cada término debe aparecer en alguna de las columnas.
     * $compactas: columnas donde también se compara sin guiones ni espacios
     * (códigos de inventario, números de serie).
     */
    public static function aplicar(Builder $query, ?string $texto, array $columnas, array $compactas = []): bool
    {
        $terminos = self::terminos($texto);

        if ($terminos === []) {
            return false;
        }

        foreach ($terminos as $termino) {
            $like = '%' . addcslashes($termino, '%_\\') . '%';
            $compacto = '%' . addcslashes(str_replace(['-', ' ', '.', '/', '_'], '', $termino), '%_\\') . '%';

            $query->where(function (Builder $sub) use ($columnas, $compactas, $like, $compacto): void {
                foreach ($columnas as $columna) {
                    $sub->orWhereRaw(self::columna($columna) . ' LIKE ?', [$like]);
                }

                if ($compacto !== '%%') {
                    foreach ($compactas as $columna) {
                        $sub->orWhereRaw(
                            "regexp_replace(" . self::columna($columna) . ", '[\\s\\-\\./_]', '', 'g') LIKE ?",
                            [$compacto]
                        );
                    }
                }
            });
        }

        return true;
    }
}
