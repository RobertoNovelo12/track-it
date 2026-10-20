<style>
    /*
    |--------------------------------------------------------------------------
    | DENSIDAD COMPACTA (Ajustes › Preferencias)
    |--------------------------------------------------------------------------
    |
    | Se activa con data-densidad="compacta" en <html> (todas las tablas)
    | o en la raíz de cada tabla de datos (componente data-table), que lo recibe del servidor.
    |
    | - Tablas: filas más bajas y texto un punto menor.
    | - Botones de acciones de la fila: más pequeños.
    | - Tarjetas (móvil) de las tablas de datos: menos relleno y separación.
    | Las celdas con colspan (mensajes de "sin resultados") no cambian.
    | La vista previa de Preferencias se excluye (maneja su propio tamaño).
    |
    */

    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody td:not([colspan]) {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        font-size: 0.75rem !important;
        line-height: 1.1rem !important;
    }

    /*
     * Las filas tienen altura fija (h-12 / 2xl:h-14 en la tabla de
     * datos y h-11/h-12 en el encabezado): en compacta se reduce.
     */
    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody tr {
        height: 2.25rem !important;
    }

    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) thead tr {
        height: 2.5rem !important;
    }

    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) thead th {
        padding-top: 0.375rem !important;
        padding-bottom: 0.375rem !important;
    }

    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody td button {
        min-height: 0 !important;
    }

    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody td .h-8,
    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody td .h-9 {
        height: 1.625rem !important;
    }

    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody td .w-8,
    [data-densidad="compacta"] table:not([data-vista-previa-densidad]) tbody td .w-9 {
        width: 1.625rem !important;
    }

    [data-densidad="compacta"][data-data-table] article,
    [data-densidad="compacta"] [data-data-table] article {
        padding: 0.625rem 0.75rem !important;
    }

    [data-densidad="compacta"][data-data-table] .grid.gap-3,
    [data-densidad="compacta"] [data-data-table] .grid.gap-3 {
        gap: 0.5rem !important;
    }
</style>
