<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * La selección de programas ya existe en el sistema, pero solo aparece
     * para los tipos con incluye_programas = true.
     *
     * Activamos esa opción en los equipos que normalmente ejecutan software:
     * computadoras (PC, laptop, Mac), móviles, tabletas y TPV.
     * No se alteran programas existentes ni tipos no relacionados.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('tipos_equipo', 'incluye_programas')) {
            return;
        }

        $tipos = DB::table('tipos_equipo')
            ->select('id_tipo_equipo', 'nombre', 'incluye_programas')
            ->get();

        foreach ($tipos as $tipo) {
            if ((bool) $tipo->incluye_programas || ! $this->permiteProgramas((string) $tipo->nombre)) {
                continue;
            }

            DB::table('tipos_equipo')
                ->where('id_tipo_equipo', $tipo->id_tipo_equipo)
                ->update(['incluye_programas' => true]);
        }
    }

    /**
     * No desactivar al revertir: después de ejecutar la migración, el
     * administrador puede cambiar el ajuste y no debemos sobrescribirlo.
     */
    public function down(): void
    {
        // Se conservan las preferencias del administrador.
    }

    private function permiteProgramas(string $nombre): bool
    {
        $nombre = Str::lower(Str::ascii(trim($nombre)));
        $nombre = preg_replace('/\s+/', ' ', $nombre) ?? $nombre;

        return preg_match(
            '/(?:^|\b)(?:pc|pc de escritorio|computadora de escritorio|computador de escritorio|'
                . 'equipo de escritorio|desktop|all in one|mini pc|laptop|notebook|portatil|'
                . 'mac|imac|macbook|mac mini|movil|celular|smartphone|telefono inteligente|'
                . 'tablet|tableta|ipad|tpv|terminal de punto de venta|'
                . 'terminal punto de venta|punto de venta|pos)(?:\b|$)/u',
            $nombre
        ) === 1;
    }
};
