<?php

namespace App\Support;

/*
|--------------------------------------------------------------------------
| Código de barras de equipos (Code 128, SVG)
|--------------------------------------------------------------------------
|
| El código de barras guarda SOLO el número del código de inventario:
| ACT-000061 → «000061». No se guarda nada en la base de datos: se
| calcula a partir de codigo_inventario. Al escanearlo, el lector
| escribe «000061» y la búsqueda de Equipos encuentra ACT-000061.
|
| Code 128: con dígitos en número par se usa el juego C (dos dígitos por
| símbolo, código más corto). Con número impar, el primer dígito va en
| el juego B y el resto en C. Sin dependencias externas.
|
|   CodigoBarras::numero('ACT-000061')  → '000061'
|   CodigoBarras::svg('000061')         → <svg …>
|
*/

class CodigoBarras
{
    /*
     * Patrones de los símbolos 0–105 de Code 128 (1 = barra, 0 = espacio,
     * 11 módulos cada uno). 103/104/105 = inicio A/B/C.
     */
    private const PATRONES = [
        '11011001100', '11001101100', '11001100110', '10010011000', '10010001100', '10001001100',
        '10011001000', '10011000100', '10001100100', '11001001000', '11001000100', '11000100100',
        '10110011100', '10011011100', '10011001110', '10111001100', '10011101100', '10011100110',
        '11001110010', '11001011100', '11001001110', '11011100100', '11001110100', '11101101110',
        '11101001100', '11100101100', '11100100110', '11101100100', '11100110100', '11100110010',
        '11011011000', '11011000110', '11000110110', '10100011000', '10001011000', '10001000110',
        '10110001000', '10001101000', '10001100010', '11010001000', '11000101000', '11000100010',
        '10110111000', '10110001110', '10001101110', '10111011000', '10111000110', '10001110110',
        '11101110110', '11010001110', '11000101110', '11011101000', '11011100010', '11011101110',
        '11101011000', '11101000110', '11100010110', '11101101000', '11101100010', '11100011010',
        '11101111010', '11001000010', '11110001010', '10100110000', '10100001100', '10010110000',
        '10010000110', '10000101100', '10000100110', '10110010000', '10110000100', '10011010000',
        '10011000010', '10000110100', '10000110010', '11000010010', '11001010000', '11110111010',
        '11000010100', '10001111010', '10100111100', '10010111100', '10010011110', '10111100100',
        '10011110100', '10011110010', '11110100100', '11110010100', '11110010010', '11011011110',
        '11011110110', '11110110110', '10101111000', '10100011110', '10001011110', '10111101000',
        '10111100010', '11110101000', '11110100010', '10111011110', '10111101110', '11101011110',
        '11110101110', '11010000100', '11010010000', '11010011100',
    ];

    // Parada + barra de terminación (13 módulos).
    private const PARADA = '1100011101011';

    private const INICIO_B = 104;

    private const INICIO_C = 105;

    private const CAMBIO_A_C = 99;

    /*
     * Número del código de inventario (solo dígitos) o null si no tiene.
     */
    public static function numero(?string $codigoInventario): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $codigoInventario);

        return $digitos === '' ? null : $digitos;
    }

    /*
     * Módulos del código (cadena de 1 y 0) para una cadena de dígitos.
     */
    public static function modulos(string $digitos): string
    {
        if ($digitos === '' || ! ctype_digit($digitos) || strlen($digitos) > 40) {
            throw new \InvalidArgumentException('El código de barras solo admite dígitos (1 a 40).');
        }

        $valores = [];

        if (strlen($digitos) % 2 === 1) {
            // Primer dígito en el juego B (valor = ASCII − 32), resto en C.
            $valores[] = self::INICIO_B;
            $valores[] = ord($digitos[0]) - 32;

            $digitos = substr($digitos, 1);

            if ($digitos !== '') {
                $valores[] = self::CAMBIO_A_C;
            }
        } else {
            $valores[] = self::INICIO_C;
        }

        if ($digitos !== '') {
            foreach (str_split($digitos, 2) as $par) {
                $valores[] = (int) $par;
            }
        }

        // Dígito de control: inicio + Σ(posición × valor), módulo 103.
        $suma = $valores[0];

        foreach (array_slice($valores, 1) as $posicion => $valor) {
            $suma += ($posicion + 1) * $valor;
        }

        $valores[] = $suma % 103;

        return implode('', array_map(fn (int $v): string => self::PATRONES[$v], $valores)) . self::PARADA;
    }

    /*
     * SVG listo para mostrar o imprimir: barras negras sobre fondo blanco
     * (los lectores necesitan ese contraste también en modo oscuro) y
     * margen silencioso de 10 módulos a cada lado.
     */
    public static function svg(string $digitos, int $alto = 60, int $modulo = 2): string
    {
        $modulos = self::modulos($digitos);
        $margen = 10 * $modulo;
        $ancho = strlen($modulos) * $modulo + $margen * 2;

        $barras = '';
        $x = $margen;

        // Barras contiguas en un solo rectángulo.
        foreach (preg_split('/(1+|0+)/', $modulos, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $tramo) {
            $largo = strlen($tramo) * $modulo;

            if ($tramo[0] === '1') {
                $barras .= '<rect x="' . $x . '" y="0" width="' . $largo . '" height="' . $alto . '"/>';
            }

            $x += $largo;
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $ancho . ' ' . $alto . '"'
            . ' width="' . $ancho . '" height="' . $alto . '" shape-rendering="crispEdges"'
            . ' role="img" aria-label="Código de barras ' . htmlspecialchars($digitos, ENT_QUOTES) . '">'
            . '<rect width="100%" height="100%" fill="#fff"/>'
            . '<g fill="#000">' . $barras . '</g>'
            . '</svg>';
    }
}
