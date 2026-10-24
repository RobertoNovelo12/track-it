
@php
    $fechaDocumento = (string) ($documento['fecha'] ?? '');

    $fechaVisible = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDocumento)
        ? \Carbon\Carbon::parse($fechaDocumento)->format('d/m/Y')
        : ($fechaDocumento ?: '—');

    $listaEquipos = $documento['equipos'] ?? [];
@endphp

<style>
    .bm-documento {
        box-sizing: border-box;
        width: auto;
        padding: 14px;
        background: #fff;
        color: #273f57;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 8px;
        line-height: 1.35;
        overflow-wrap: break-word;
    }

    .bm-documento * {
        box-sizing: border-box;
    }

    /* LOGO Y ORGANIZACIÓN */

    .bm-documento .bm-logo {
        display: block;
        max-width: 165px;
        max-height: 48px;
        width: auto;
        height: auto;
        margin: 0 0 6px;
    }

    .bm-documento .bm-institucion {
        padding: 0 0 6px;
        border-bottom: 1px solid #d1d5db;
        font-size: 7px;
    }

    /*
    |--------------------------------------------------------------------------
    | ENCABEZADO COMPACTO
    |--------------------------------------------------------------------------
    | Título y subtítulo a la izquierda.
    | Folio y fecha a la derecha, a la misma altura.
    | Compatible con DomPDF mediante tabla.
    */

    .bm-documento .bm-encabezado {
        width: 100%;
        margin-top: 8px;
        margin-bottom: 3px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .bm-documento .bm-encabezado td {
        border: none;
        padding: 0;
        background: transparent;
        vertical-align: top;
    }

    .bm-documento .bm-encabezado .bm-titulo-col {
        width: 73%;
        padding-right: 8px;
    }

    .bm-documento .bm-encabezado .bm-folio-col {
        width: 27%;
        text-align: right;
    }

    .bm-documento h2 {
        margin: 0;
        color: #273f57;
        font-size: 12px;
        font-weight: bold;
        line-height: 1.3;
    }

    .bm-documento .bm-subtitulo {
        margin: 3px 0 0;
        font-size: 8px;
        line-height: 1.3;
    }

    .bm-documento .bm-meta {
        margin: 0;
        text-align: right;
        font-size: 8px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | SECCIONES DEL DOCUMENTO
    |--------------------------------------------------------------------------
    */

    .bm-documento h3 {
        margin: 8px 0 5px;
        padding: 5px 6px;
        color: #273f57;
        background: #eff1f3;
        font-size: 9px;
        font-weight: bold;
    }

    .bm-documento .bm-encabezado + h3 {
        margin-top: 5px;
    }

    .bm-documento table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .bm-documento td,
    .bm-documento th {
        border: 1px solid #d1d5db;
        padding: 4px 6px;
        text-align: left;
        vertical-align: top;
        word-wrap: break-word;
    }

    .bm-documento th {
        background: #edf1f5;
        font-size: 8px;
    }

    .bm-documento .bm-label {
        display: block;
        margin: 0 0 3px;
        color: #526679;
        font-size: 7px;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLA DE EQUIPOS
    |--------------------------------------------------------------------------
    */

    .bm-documento .bm-tabla-equipos th:nth-child(1) {
        width: 20%;
    }

    .bm-documento .bm-tabla-equipos th:nth-child(2) {
        width: 29%;
    }

    .bm-documento .bm-tabla-equipos th:nth-child(3) {
        width: 31%;
    }

    .bm-documento .bm-tabla-equipos th:nth-child(4) {
        width: 20%;
    }

    .bm-documento tr {
        page-break-inside: avoid;
    }

    /*
    |--------------------------------------------------------------------------
    | SALTO DE PÁGINA
    |--------------------------------------------------------------------------
    */

    .bm-documento.bm-pagina-nueva {
        page-break-before: always;
    }

    /*
    |--------------------------------------------------------------------------
    | FIRMAS
    |--------------------------------------------------------------------------
    */

    .bm-documento .bm-firmas-bloque {
        margin-top: 8px;
    }

    .bm-documento .firmas-baja {
        margin-top: 5px;
    }

    .bm-documento .firmas-baja .firma-recuadro {
        border-color: #d1d5db;
    }

    .bm-documento .firmas-baja .firma-espacio {
        height: 15px;
    }

    .bm-documento .firmas-baja .firma-nombre {
        min-height: 10px;
        padding: 3px 6px;
    }

    .bm-documento .firmas-baja .firma-fecha {
        padding: 2px 6px;
    }

    /*
    |--------------------------------------------------------------------------
    | EVIDENCIAS FOTOGRÁFICAS
    |--------------------------------------------------------------------------
    */

    .bm-documento .bm-fotos {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px 10px;
        table-layout: fixed;
    }

    .bm-documento .bm-fotos td {
        width: 50%;
        border: 0;
        vertical-align: top;
        padding: 0;
    }

    .bm-documento .bm-foto-titulo {
        padding: 5px 6px;
        border: 1px solid #d1d5db;
        background: #eff1f3;
        color: #273f57;
        font-size: 8px;
        font-weight: bold;
    }

    .bm-documento .bm-foto-contenedor {
        height: 270px;
        padding: 8px;
        border: 1px solid #d1d5db;
        border-top: 0;
        text-align: center;
        vertical-align: middle;
    }

    .bm-documento .bm-foto-contenedor img {
        max-width: 100%;
        max-height: 250px;
        width: auto;
        height: auto;
    }

    .bm-documento .bm-sin-foto {
        padding-top: 110px;
        color: #778597;
        font-size: 8px;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA PREVIA EN EL SISTEMA
    |--------------------------------------------------------------------------
    */

    .baja-preview .bm-documento {
        max-width: 100%;
        margin: 0 auto 12px;
        border: 1px solid #d1d5db;
        box-shadow: 0 2px 7px rgba(0, 0, 0, .08);
    }

    .baja-preview .bm-documento.bm-pagina-nueva {
        margin-top: 12px;
    }

    .baja-preview .bm-documento .bm-foto-contenedor {
        height: 220px;
    }

    .baja-preview .bm-documento .bm-foto-contenedor img {
        max-height: 200px;
    }

    .baja-preview .bm-documento .bm-sin-foto {
        padding-top: 90px;
    }
</style>


{{-- ================================================== --}}
{{-- HOJA 1: INFORMACIÓN, EQUIPOS Y FIRMAS              --}}
{{-- ================================================== --}}

<article class="bm-documento">

    {{-- LOGO DEL HOTEL O LA ORGANIZACIÓN --}}

    @if (!empty($documento['logo']))
        <img
            class="bm-logo"
            src="{{ $documento['logo'] }}"
            alt="{{ $documento['organizacion'] ?? 'Organización' }}"
        >
    @endif

    <div class="bm-institucion">
        {{ $documento['organizacion'] ?? 'Organización' }}
        | CONTROL DE ACTIVOS TI
    </div>

    {{-- TÍTULO Y FOLIO EN LA MISMA ALTURA --}}

    <table class="bm-encabezado">
        <tbody>
            <tr>
                <td class="bm-titulo-col">

                    <h2>
                        ACTA DE BAJA MÚLTIPLE DE EQUIPOS TECNOLÓGICOS
                    </h2>

                    <div class="bm-subtitulo">
                        Solicitud de baja de equipos ·
                        {{ $documento['departamento'] ?: 'Departamento por asignar' }}
                    </div>

                </td>

                <td class="bm-folio-col">

                    <div class="bm-meta">
                        Folio: {{ $documento['folio'] ?? 'Por asignar' }}
                        <br>
                        Fecha: {{ $fechaVisible }}
                    </div>

                </td>
            </tr>
        </tbody>
    </table>

    {{-- INFORMACIÓN GENERAL --}}

    <h3>01 &nbsp; INFORMACIÓN GENERAL</h3>

    <table>
        <tbody>

            <tr>
                <td width="50%">
                    <span class="bm-label">
                        HOTEL / SEDE
                    </span>

                    {{ $documento['hotel'] ?: '—' }}
                </td>

                <td width="50%">
                    <span class="bm-label">
                        DEPARTAMENTO
                    </span>

                    {{ $documento['departamento'] ?: '—' }}
                </td>
            </tr>

            <tr>
                <td>
                    <span class="bm-label">
                        QUIÉN SOLICITA LA BAJA
                    </span>

                    {{ $documento['solicitante'] ?: '—' }}
                </td>

                <td>
                    <span class="bm-label">
                        FECHA DE BAJA
                    </span>

                    {{ $fechaVisible }}
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="bm-label">
                        MOTIVO DE LA BAJA
                    </span>

                    {{ $documento['motivo'] ?: '—' }}
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="bm-label">
                        OBSERVACIONES
                    </span>

                    {{ $documento['observaciones'] ?: '—' }}
                </td>
            </tr>

        </tbody>
    </table>

    {{-- EQUIPOS INCLUIDOS --}}

    <h3>
        02 &nbsp; EQUIPOS INCLUIDOS
        ({{ count($listaEquipos) }})
    </h3>

    <table class="bm-tabla-equipos">
        <thead>
            <tr>
                <th>Inventario</th>
                <th>Equipo</th>
                <th>Marca / Modelo</th>
                <th>Número de serie</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($listaEquipos as $equipo)
                <tr>

                    <td>
                        {{ $equipo['codigo_inventario'] ?? '—' }}
                    </td>

                    <td>
                        {{ $equipo['nombre_equipo'] ?? '—' }}
                    </td>

                    <td>
                        {{ ($equipo['marca'] ?? null) ?: '—' }}
                        /
                        {{ ($equipo['modelo'] ?? null) ?: '—' }}
                    </td>

                    <td>
                        {{ ($equipo['numero_serie'] ?? null) ?: '—' }}
                    </td>

                </tr>
            @endforeach

        </tbody>
    </table>

    {{-- FIRMAS JUSTO DEBAJO DE LA TABLA --}}

    <div class="bm-firmas-bloque">

        <h3>
            03 &nbsp; FIRMAS DE BAJA
        </h3>

        @include('reportes.partials.firmas-baja-multiple', [
        'firmasDocumento' => $documento['firmas'] ?? [],
        'fechaDocumento' => $documento['fecha'] ?? '',
    ])

    </div>

</article>


{{-- ================================================== --}}
{{-- HOJA 2: EVIDENCIAS FOTOGRÁFICAS                    --}}
{{-- ================================================== --}}

<article class="bm-documento bm-pagina-nueva">

    {{-- LOGO EN LA SEGUNDA HOJA --}}

    @if (!empty($documento['logo']))
        <img
            class="bm-logo"
            src="{{ $documento['logo'] }}"
            alt="{{ $documento['organizacion'] ?? 'Organización' }}"
        >
    @endif

    <div class="bm-institucion">
        {{ $documento['organizacion'] ?? 'Organización' }}
        | CONTROL DE ACTIVOS TI
    </div>

    {{-- ENCABEZADO COMPACTO TAMBIÉN EN HOJA 2 --}}

    <table class="bm-encabezado">
        <tbody>
            <tr>

                <td class="bm-titulo-col">

                    <h2>
                        ACTA DE BAJA MÚLTIPLE DE EQUIPOS TECNOLÓGICOS
                    </h2>

                    <div class="bm-subtitulo">
                        Evidencia fotográfica del conjunto de equipos
                    </div>

                </td>

                <td class="bm-folio-col">

                    <div class="bm-meta">
                        Folio: {{ $documento['folio'] ?? 'Por asignar' }}
                        <br>
                        Fecha: {{ $fechaVisible }}
                    </div>

                </td>

            </tr>
        </tbody>
    </table>

    {{-- EVIDENCIAS FOTOGRÁFICAS --}}

    <h3>
        04 &nbsp; EVIDENCIA FOTOGRÁFICA
    </h3>

    <table class="bm-fotos">
        <tbody>

            @foreach (
                array_chunk(
                    [
                        'Vista frontal',
                        'Vista trasera',
                        'Vista lateral 1',
                        'Vista lateral 2'
                    ],
                    2
                ) as $fila
            )

                <tr>

                    @foreach ($fila as $titulo)

                        <td>

                            <div class="bm-foto-titulo">
                                {{ $titulo }}
                            </div>

                            <div class="bm-foto-contenedor">

                                @if (!empty($documento['fotos'][$titulo]))

                                    <img
                                        src="{{ $documento['fotos'][$titulo] }}"
                                        alt="{{ $titulo }}"
                                    >

                                @else

                                    <div class="bm-sin-foto">
                                        Evidencia fotográfica no registrada
                                    </div>

                                @endif

                            </div>

                        </td>

                    @endforeach

                </tr>

            @endforeach

        </tbody>
    </table>

</article>
