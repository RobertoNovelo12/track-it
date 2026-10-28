<?php

namespace App\Support;

/*
|--------------------------------------------------------------------------
| Íconos (sprite SVG)
|--------------------------------------------------------------------------
|
| Los íconos de la interfaz viven en public/images/iconos.svg como
| <symbol>. En las vistas se dibujan con:
|
|   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" …>
|       <use href="{{ \App\Support\Icono::url('i-xxxxxxx') }}"></use>
|   </svg>
|
| El <svg> exterior conserva tamaño, color (currentColor) y grosor, así
| que el tema claro/oscuro sigue funcionando. El archivo se descarga una
| sola vez y queda en caché; ?v= cambia cuando cambia su contenido.
|
*/

class Icono
{
    private static ?string $base = null;

    public static function url(string $id): string
    {
        return (self::$base ??= self::base()).'#'.$id;
    }

    private static function base(): string
    {
        $ruta = public_path('images/iconos.svg');
        $version = is_file($ruta) ? substr(md5((string) filemtime($ruta).filesize($ruta)), 0, 8) : '1';

        return asset('images/iconos.svg').'?v='.$version;
    }
}
