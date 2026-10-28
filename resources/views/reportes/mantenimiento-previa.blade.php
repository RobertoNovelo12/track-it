

@php

    $equipo = $m['equipo'] ?? [];

    $refacciones = $m['refacciones'] ?? [];



    $dato = static fn ($valor) =>

        filled($valor) ? (string) $valor : '—';



    $marcaModelo = trim(

        ($equipo['marca'] ?? '') . ' ' .

        ($equipo['modelo'] ?? '')

    );

@endphp



<style>

    .preview-mantenimiento {

        background: #ffffff;

        color: #000000;

        font-family: DejaVu Sans, Arial, sans-serif;

        font-size: 7px;

        line-height: 1.35;

        padding: 5%;

        overflow-wrap: anywhere;

    }



    .preview-mantenimiento * {

        box-sizing: border-box;

    }



    .preview-mantenimiento .logo {
        float: none;

        width: 35%;

        max-width: 145px;

        display: block;

        height: auto;

        margin-bottom: 5px;
        margin: 0 0 5px 0;
        margin-left: 0;
        margin-right: auto;

    }



    .preview-mantenimiento .submarca {
        clear: both;

        color: #000000;

        font-size: .9em;

        border-bottom: 1px solid #000000;

        padding-bottom: 6px;

    }



    .preview-mantenimiento h2 {

        margin: 8px 0 3px;

        color: #000000;

        font-size: 1.55em;

        font-weight: normal;

        line-height: 1.2;

    }



    .preview-mantenimiento .subtitulo {

        color: #000000;

        margin-bottom: 6px;

    }



    .preview-mantenimiento .seccion {

        margin: 7px 0 4px;

        padding: 5px 8px;








        background: #000000; color: #ffffff; font-weight: normal;
}



    .preview-mantenimiento .numero {

        margin-right: 9px;

    }



    .preview-mantenimiento .campos {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));


        gap: 1px; padding: 1px; background: #000000;
}



    .preview-mantenimiento .campos.tres {

        grid-template-columns: repeat(3, minmax(0, 1fr));

    }



    .preview-mantenimiento .campos.cuatro {

        grid-template-columns: repeat(4, minmax(0, 1fr));

    }



    .preview-mantenimiento .campo {

        border: 0; background: #ffffff;

        padding: 5px 7px;

        min-width: 0;

    }



    .preview-mantenimiento .etiqueta {

        display: block;

        color: #000000;

        font-size: .82em;

        font-weight: normal;

        text-transform: uppercase;

        margin-bottom: 3px;

    }



    .preview-mantenimiento .valor {

        display: block;

        color: #000000;

        font-weight: normal;

        white-space: pre-line;

        overflow-wrap: anywhere;

    }



    .preview-mantenimiento .descripcion {

        border: 1px solid #000000;

        padding: 6px 8px;

        margin-bottom: 4px;

    }



    .preview-mantenimiento table {

        width: 100%;

        border-collapse: collapse;

        table-layout: fixed;

    }



    .preview-mantenimiento th {

        padding: 6px 5px;





        text-align: left;

        font-size: .85em;


        background: #000000; color: #ffffff; font-weight: normal;
}



    .preview-mantenimiento td {

        padding: 6px 5px;

        border: 1px solid #000000;

        vertical-align: top;

        overflow-wrap: anywhere;

    }



    .preview-mantenimiento .sin-refacciones {

        padding: 8px;

        border: 1px solid #000000;

        background: #ffffff;

        color: #000000;

    }



    .preview-mantenimiento .bloque-firmas {

        margin-top: 0;

    }



    .preview-mantenimiento .firmas {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 6px;

    }



    .preview-mantenimiento .firma {

        border: 1px solid #000000;

        padding: 6px;

        min-width: 0;

    }



    .preview-mantenimiento .firma-titulo {

        color: #000000;

        font-size: 1em;

        font-weight: normal;

    }



    .preview-mantenimiento .linea-firma {

        height: 32px;

        border-bottom: 1px solid #000000;

    }



    .preview-mantenimiento .firma-pie {

        margin-top: 4px;

        color: #000000;

        font-size: .95em;

        line-height: 1.4;

    }



    .preview-mantenimiento .firma-fecha {

        margin-top: 2px;

    }



    .preview-mantenimiento .pie {

        margin-top: 8px;

        padding-top: 5px;

        border-top: 1px solid #000000;

        color: #000000;

        font-size: .86em;

    }


    /* Imagen institucional en la esquina superior izquierda del documento. */
    .logo-palladium-izquierda {
        display: block;
        float: none;
        width: 32%;
        max-width: 135px;
        height: auto;
        margin: 0 0 8px 0;
    }
</style>



<div class="overflow-hidden border border-black bg-white shadow-sm">

    <article class="preview-mantenimiento">



        {{-- ENCABEZADO --}}

        <img

            class="logo"

            src="{{ asset('images/logo-grand-palladium.png') }}"

            alt="Grand Palladium"

        >



        <div class="submarca">

            GRAND PALLADIUM RIVIERA MAYA | CONTROL DE ACTIVOS TI

        </div>



        <h2>

            ACTA DE MANTENIMIENTO DE EQUIPO TECNOLÓGICO

        </h2>



        <div class="subtitulo">

            Registro técnico de intervención, diagnóstico y seguimiento

        </div>



        {{-- 01. INFORMACIÓN GENERAL --}}

        <div class="seccion">

            <span class="numero">01</span>

            INFORMACIÓN GENERAL DEL EQUIPO

        </div>



        <div class="campos tres">

            <div class="campo">

                <span class="etiqueta">

                    Código de inventario

                </span>

                <span class="valor">

                    {{ $dato($equipo['codigo'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Equipo</span>

                <span class="valor">

                    {{ $dato($equipo['nombre'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Host</span>

                <span class="valor">

                    {{ $dato($equipo['host'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Marca / Modelo</span>

                <span class="valor">

                    {{ $dato($marcaModelo) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Número de serie</span>

                <span class="valor">

                    {{ $dato($equipo['serie'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">

                    Folio de mantenimiento

                </span>

                <span class="valor">

                    {{ $dato($m['folio'] ?? null) }}

                </span>

            </div>

        </div>



        {{-- 02. INFORMACIÓN DEL MANTENIMIENTO --}}

        <div class="seccion">

            <span class="numero">02</span>

            INFORMACIÓN DEL MANTENIMIENTO

        </div>



        <div class="campos">

            <div class="campo">

                <span class="etiqueta">

                    Tipo de mantenimiento

                </span>

                <span class="valor">

                    {{ $dato($m['tipo_mantenimiento'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">

                    Tipo de intervención

                </span>

                <span class="valor">

                    {{ $dato($m['tipo_intervencion'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">

                    Técnico responsable

                </span>

                <span class="valor">

                    {{ $dato($m['tecnico'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">

                    Prioridad / Estado

                </span>

                <span class="valor">

                    {{ $dato($m['prioridad'] ?? null) }}



                    {{ $dato($m['estado'] ?? null) }}

                </span>

            </div>

            <div class="campo">
                <span class="etiqueta">Proveedor externo</span>
                <span class="valor">{{ $dato($m['proveedor'] ?? null) }}</span>
            </div>
            <div class="campo">
                <span class="etiqueta">Fin de garantía del equipo</span>
                <span class="valor">{{ $dato($m['fin_garantia_equipo'] ?? null) }}</span>
            </div>
        </div>



        {{-- 03. CONTROL DE TIEMPOS --}}

        <div class="seccion">

            <span class="numero">03</span>

            CONTROL DE TIEMPOS

        </div>



        <div class="campos cuatro">

            <div class="campo">

                <span class="etiqueta">Fecha inicio</span>

                <span class="valor">

                    {{ $dato($m['fecha_inicio'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Hora inicio</span>

                <span class="valor">

                    {{ $dato($m['hora_inicio'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Fecha fin</span>

                <span class="valor">

                    {{ $dato($m['fecha_fin'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Hora fin</span>

                <span class="valor">

                    {{ $dato($m['hora_fin'] ?? null) }}

                </span>

            </div>

        </div>



        {{-- 04. DESCRIPCIÓN TÉCNICA --}}

        <div class="seccion">

            <span class="numero">04</span>

            DESCRIPCIÓN TÉCNICA DE LA INTERVENCIÓN

        </div>



        <div class="descripcion">

            <span class="etiqueta">

                Falla reportada / Motivo

            </span>

            <span class="valor">

                {{ $dato($m['falla_reportada'] ?? null) }}

            </span>

        </div>



        <div class="descripcion">

            <span class="etiqueta">

                Diagnóstico técnico

            </span>

            <span class="valor">

                {{ $dato($m['diagnostico'] ?? null) }}

            </span>

        </div>



        <div class="descripcion">

            <span class="etiqueta">

                Intervención realizada

            </span>

            <span class="valor">

                {{ $dato($m['intervencion_realizada'] ?? null) }}

            </span>

        </div>



        {{-- 05. REFACCIONES --}}

        <div class="seccion">

            <span class="numero">05</span>

            REFACCIONES / PARTES UTILIZADAS

        </div>



        @if (count($refacciones) > 0)

            <table>

                <thead>

                    <tr>

                        <th width="42%">REFACCIÓN</th>

                        <th width="28%">CÓDIGO</th>

                        <th width="15%">CANTIDAD</th>

                        <th width="15%">UNIDAD</th>

                    </tr>

                </thead>



                <tbody>

                    @foreach ($refacciones as $r)

                        <tr>

                            <td>

                                {{ $dato($r['nombre'] ?? null) }}

                            </td>

                            <td>

                                {{ $dato($r['codigo'] ?? null) }}

                            </td>

                            <td>

                                {{ $dato($r['cantidad'] ?? null) }}

                            </td>

                            <td>

                                {{ $dato($r['unidad'] ?? null) }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="sin-refacciones">

                No se requirió la sustitución ni instalación de refacciones durante la intervención.

            </div>

        @endif



        {{-- 06. INFORMACIÓN ADICIONAL --}}

        <div class="seccion">

            <span class="numero">06</span>

            INFORMACIÓN ADICIONAL

        </div>



        <div class="campos">

            <div class="campo">

                <span class="etiqueta">

                    Observaciones

                </span>

                <span class="valor">

                    {{ $dato($m['observaciones'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">

                    Próximo mantenimiento

                </span>

                <span class="valor">

                    {{ $dato($m['proximo_mantenimiento'] ?? null) }}

                </span>

            </div>

        </div>



        {{-- 07. FIRMAS --}}

        <div class="bloque-firmas">

            <div class="seccion">

                <span class="numero">07</span>

                FIRMAS DE REALIZACIÓN Y RECEPCIÓN

            </div>



            <div class="firmas">

                <div class="firma">

                    <div class="firma-titulo">

                        Realizó · Técnico responsable

                    </div>



                    <div class="linea-firma"></div>



                    <div class="firma-pie">

                        {{ $dato($m['tecnico'] ?? null) }}

                        <div class="firma-fecha">

                            Fecha: ____ / ____ / ______

                        </div>

                    </div>

                </div>



                <div class="firma">

                    <div class="firma-titulo">

                        Recibió · Responsable del equipo

                    </div>



                    <div class="linea-firma"></div>



                    <div class="firma-pie">

                        Nombre y firma

                        <div class="firma-fecha">

                            Fecha: ____ / ____ / ______

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- PIE DEL DOCUMENTO --}}

        <div class="pie">

            Departamento de Sistemas · Control de Activos TI

        </div>



    </article>

</div>
