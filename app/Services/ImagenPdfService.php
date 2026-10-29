<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Convierte imágenes del disco "local" en data-URI optimizados (JPEG
 * reducido) para incrustarlos en PDFs y vistas previas.
 *
 * El resultado se guarda en disco (storage/app/private/cache-img), de modo
 * que cada foto se procesa UNA sola vez; las siguientes descargas leen el
 * archivo ya reducido y son prácticamente instantáneas.
 */
class ImagenPdfService
{
    private const TIPOS_PERMITIDOS = [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_WEBP,
    ];

    public function dataUri(
        ?string $ruta,
        int $ladoMaximo = 700,
        int $calidad = 70
    ): ?string {
        if (!filled($ruta)) {
            return null;
        }

        $disco = Storage::disk('local');

        if (!$disco->exists($ruta)) {
            return null;
        }

        $clave = sha1(
            $ruta . '|' .
            $disco->lastModified($ruta) . '|' .
            $ladoMaximo . '|' .
            $calidad
        );

        $rutaCache = 'cache-img/' . $clave . '.jpg';

        if ($disco->exists($rutaCache)) {
            return $this->aDataUri(
                $disco->get($rutaCache),
                'image/jpeg'
            );
        }

        $original = $disco->get($ruta);
        $reducida = $this->reducir($original, $ladoMaximo, $calidad);

        if ($reducida !== null) {
            $disco->put($rutaCache, $reducida);

            return $this->aDataUri($reducida, 'image/jpeg');
        }

        /*
         * Si GD no está disponible o la imagen no se pudo procesar,
         * se usa el archivo original (comportamiento anterior).
         */
        $info = @getimagesizefromstring($original);

        if (
            $info === false
            || !in_array($info[2], self::TIPOS_PERMITIDOS, true)
        ) {
            return null;
        }

        return $this->aDataUri($original, $info['mime']);
    }

    private function reducir(
        string $binario,
        int $ladoMaximo,
        int $calidad
    ): ?string {
        if (
            !function_exists('imagecreatefromstring')
            || !function_exists('imagejpeg')
        ) {
            return null;
        }

        $info = @getimagesizefromstring($binario);

        if (
            $info === false
            || !in_array($info[2], self::TIPOS_PERMITIDOS, true)
        ) {
            return null;
        }

        [$ancho, $alto] = $info;

        $origen = @imagecreatefromstring($binario);

        if ($origen === false) {
            return null;
        }

        $escala = min(1, $ladoMaximo / max($ancho, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        // Fondo blanco para imágenes PNG/WEBP con transparencia.
        imagefill(
            $lienzo,
            0,
            0,
            imagecolorallocate($lienzo, 255, 255, 255)
        );

        imagecopyresampled(
            $lienzo,
            $origen,
            0,
            0,
            0,
            0,
            $nuevoAncho,
            $nuevoAlto,
            $ancho,
            $alto
        );

        ob_start();
        imagejpeg($lienzo, null, $calidad);
        $resultado = ob_get_clean();

        return $resultado !== false && $resultado !== ''
            ? $resultado
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Logotipos
    |--------------------------------------------------------------------------
    |
    | Dompdf procesa el canal de transparencia de un PNG píxel por píxel
    | en PHP: con un logo grande eso tarda. Aquí el logo se reduce al
    | tamaño en que se imprime y se aplana sobre blanco (el fondo de los
    | PDF), sin transparencia. Se procesa una vez y queda en disco.
    |
    */

    private array $memoria = [];

    /*
     * Logo de la organización (Administración › Organización) o, si no
     * hay, el de Track IT (el mismo que muestra la interfaz).
     */
    public function logoDataUri(int $anchoMaximo = 480): ?string
    {
        $logo = app(OrganizacionService::class)->logoContenido();

        if (is_array($logo) && ! empty($logo['contenido'])) {
            $clave = 'logo-org-'.sha1(($logo['version'] ?? '').'|'.strlen($logo['contenido']).'|'.$anchoMaximo);

            return $this->memoria[$clave] ??= $this->logoProcesado(
                $clave,
                $logo['contenido'],
                (string) ($logo['mime'] ?? 'image/png'),
                $anchoMaximo
            );
        }

        return $this->archivoPublicoDataUri(OrganizacionService::LOGO_DOCUMENTOS, $anchoMaximo);
    }

    /*
     * Imagen de public/ (p. ej. images/logo-grand-palladium.png).
     */
    public function archivoPublicoDataUri(string $rutaPublica, int $anchoMaximo = 480): ?string
    {
        $archivo = public_path($rutaPublica);

        if (! is_file($archivo)) {
            return null;
        }

        $clave = 'logo-pub-'.sha1($rutaPublica.'|'.filemtime($archivo).'|'.filesize($archivo).'|'.$anchoMaximo);

        return $this->memoria[$clave] ??= $this->logoProcesado(
            $clave,
            (string) file_get_contents($archivo),
            (string) (mime_content_type($archivo) ?: 'image/png'),
            $anchoMaximo
        );
    }

    private function logoProcesado(string $clave, string $binario, string $mime, int $anchoMaximo): string
    {
        $disco = Storage::disk('local');
        $rutaCache = 'cache-img/'.$clave.'.png';

        if ($disco->exists($rutaCache)) {
            return $this->aDataUri($disco->get($rutaCache), 'image/png');
        }

        $reducido = $this->aplanarPng($binario, $anchoMaximo);

        if ($reducido === null) {
            // Sin GD o formato no soportado (p. ej. SVG): se usa tal cual.
            return $this->aDataUri($binario, $mime);
        }

        $disco->put($rutaCache, $reducido);

        return $this->aDataUri($reducido, 'image/png');
    }

    private function aplanarPng(string $binario, int $anchoMaximo): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return null;
        }

        $info = @getimagesizefromstring($binario);

        if ($info === false || ! in_array($info[2], self::TIPOS_PERMITIDOS, true)) {
            return null;
        }

        [$ancho, $alto] = $info;
        $origen = @imagecreatefromstring($binario);

        if ($origen === false) {
            return null;
        }

        $escala = min(1, $anchoMaximo / max(1, $ancho));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));
        imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        imagepng($lienzo, null, 9);
        $resultado = ob_get_clean();

        return $resultado !== false && $resultado !== '' ? $resultado : null;
    }

    private function aDataUri(string $binario, string $mime): string
    {
        return 'data:' . $mime . ';base64,' . base64_encode($binario);
    }
}