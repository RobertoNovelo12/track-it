

@php

    $equipo = $baja['equipo'] ?? [];

    $fotos = $baja['fotos'] ?? [];



    $dato = static fn ($valor) =>

        filled($valor) ? (string) $valor : '—';



    $marcaModelo = trim(implode(' ', array_filter([

        $equipo['marca'] ?? null,

        $equipo['modelo'] ?? null,

    ])));



    $fotografias = [

        'Vista frontal' => $fotos['frontal'] ?? null,

        'Vista trasera' => $fotos['trasera'] ?? null,

        'Vista lateral 1' => $fotos['lateral_1'] ?? null,

        'Vista lateral 2' => $fotos['lateral_2'] ?? null,

    ];

@endphp



<style>

    .preview-baja {

        background: #ffffff;

        color: #273f57;

        padding: 5%;

        font-family: 'Aptos', 'Segoe UI', Arial, sans-serif;

        font-size: 7px;

        line-height: 1.35;

        overflow-wrap: anywhere;

    }



    .preview-baja * {

        box-sizing: border-box;

    }



    .preview-baja .marca {

        color: #a58d40;

        font-size: 15px;

        letter-spacing: 2px;

    }



    .preview-baja .grupo {

        color: #a58d40;

        font-size: 6px;

        letter-spacing: 3px;

    }



    .preview-baja .institucion {
        clear: both;

        margin-top: 6px;

        padding-bottom: 6px;

        border-bottom: 2px solid #ad9647;

        color: #718293;

        font-size: 6px;

    }



    .preview-baja h2 {

        margin: 9px 0 3px;

        color: #213c55;

        font-size: 12px;

        font-weight: bold;

    }



    .preview-baja .subtitulo {

        color: #748494;

        margin-bottom: 8px;

    }



    .preview-baja .seccion {

        margin: 8px 0 4px;

        padding: 6px 8px;

        background: #eff1f3;

        color: #2b435b;

        font-weight: bold;

    }



    .preview-baja .numero {

        margin-right: 8px;

    }



    .preview-baja .campos {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

    }



    .preview-baja .campos.tres {

        grid-template-columns: repeat(3, minmax(0, 1fr));

    }



    .preview-baja .campo {

        min-width: 0;

        padding: 6px;

        border: 1px solid #d1d5db;

    }



    .preview-baja .campo.ancho {

        grid-column: 1 / -1;

    }



    .preview-baja .etiqueta {

        display: block;

        margin-bottom: 3px;

        color: #617b91;

        font-size: 6px;

        font-weight: bold;

        text-transform: uppercase;

    }



    .preview-baja .valor {

        display: block;

        font-weight: 600;

        color: #253d56;

        white-space: pre-line;

        overflow-wrap: anywhere;

    }



    .preview-baja .firmas {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 6px;

    }



    .preview-baja .firma {

        border: 1px solid #d1d5db;

        padding: 6px;

    }



    .preview-baja .firma-titulo {

        font-weight: bold;

        color: #2b435b;

    }



    .preview-baja .linea {

        height: 38px;

        border-bottom: 1px solid #d1d5db;

    }



    .preview-baja .firma-pie {

        margin-top: 4px;

        color: #718293;

        font-size: 6px;

    }



    .preview-baja .fotografias {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 6px;

    }



    .preview-baja .fotografia {

        min-width: 0;

        border: 1px solid #d1d5db;

    }



    .preview-baja .foto-titulo {

        padding: 6px;

        background: #eff1f3;

        font-weight: bold;

        font-size: 6px;

    }



    .preview-baja .foto-cuerpo {

        display: flex;

        align-items: center;

        justify-content: center;

        height: 100px;

        padding: 5px;

        text-align: center;

        color: #8694a2;

        font-size: 6px;

    }



    .preview-baja .foto-cuerpo img {

        max-width: 100%;

        max-height: 100%;

        object-fit: contain;

    }



    .preview-baja .pie {

        margin-top: 8px;

        padding-top: 5px;

        border-top: 1px solid #e0e5ea;

        color: #8593a0;

        font-size: 6px;

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



<div class="overflow-hidden border border-slate-200 bg-white shadow-sm">

    <article class="preview-baja">



        {{-- ENCABEZADO --}}

        <img class="logo-palladium-izquierda" src="{{ app(\App\Services\OrganizacionService::class)->logoUrl() }}" alt="Palladium Hotel Group">
        <div class="institucion">

            {{ app(\App\Services\OrganizacionService::class)->nombre() }} | CONTROL DE ACTIVOS TI

        </div>



        <h2>ORDEN DE DECOMISO DE ACTIVOS</h2>



        <div class="subtitulo">

            Baja de equipo tecnológico · Control documental y autorización

        </div>



        {{-- 01. DATOS DE LA BAJA --}}

        <div class="seccion">

            <span class="numero">01</span>

            DATOS DE LA BAJA

        </div>



        <div class="campos tres">

            <div class="campo">

                <span class="etiqueta">Fecha de baja</span>

                <span class="valor">

                    {{ $dato($baja['fecha'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Folio de baja</span>

                <span class="valor">

                    {{ $dato($baja['folio'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Código de inventario</span>

                <span class="valor">

                    {{ $dato($equipo['codigo'] ?? null) }}

                </span>

            </div>

        </div>



        {{-- 02. INFORMACIÓN GENERAL --}}

        <div class="seccion">

            <span class="numero">02</span>

            INFORMACIÓN GENERAL DEL EQUIPO

        </div>



        <div class="campos">

            <div class="campo">

                <span class="etiqueta">Nombre del activo</span>

                <span class="valor">

                    {{ $dato($equipo['nombre'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Número de serie</span>

                <span class="valor">

                    {{ $dato($equipo['serie'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Tipo de equipo</span>

                <span class="valor">

                    {{ $dato($equipo['tipo'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Marca / Modelo</span>

                <span class="valor">

                    {{ $dato($marcaModelo) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Fecha de registro</span>

                <span class="valor">

                    {{ $dato($equipo['fecha_registro'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Factura</span>

                <span class="valor">

                    {{ $dato($equipo['factura'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Proveedor</span>

                <span class="valor">

                    {{ $dato($equipo['proveedor'] ?? null) }}

                </span>

            </div>



            <div class="campo">

                <span class="etiqueta">Garantía</span>

                <span class="valor">

                    {{ $dato($equipo['garantia'] ?? null) }}

                </span>

            </div>

        </div>



        {{-- 03. JUSTIFICACIÓN --}}

        <div class="seccion">

            <span class="numero">03</span>

            JUSTIFICACIÓN DE LA BAJA

        </div>



        <div class="campos">

            <div class="campo ancho">

                <span class="etiqueta">Motivo de baja</span>

                <span class="valor">

                    {{ $dato($baja['motivo'] ?? null) }}

                </span>

            </div>



            <div class="campo ancho">

                <span class="etiqueta">Comentarios y detalles adicionales</span>

                <span class="valor">

                    {{ $dato($baja['comentarios'] ?? null) }}

                </span>

            </div>

        </div>



        {{-- 04. FIRMAS --}}

        <div class="seccion">

            <span class="numero">04</span>

            AUTORIZACIÓN Y FIRMAS DE BAJA

        </div>



        @include('reportes.partials.firmas-baja', ['firmasDocumento' => $baja['firmas'] ?? [], 'fechaDocumento' => $baja['fecha'] ?? ''])

        <div class="pie">

            Departamento de Sistemas · Página 1 de 2

        </div>



        {{-- SEGUNDA PÁGINA, VISTA PREVIA --}}

        <div class="fotografias">

            @foreach ($fotografias as $nombre => $imagen)

                <div class="fotografia">

                    <div class="foto-titulo">

                        {{ $nombre }}

                    </div>



                    <div class="foto-cuerpo">

                        @if ($imagen)

                            <img

                                src="{{ $imagen }}"

                                alt="{{ $nombre }}"

                            >

                        @else

                            Evidencia fotográfica no registrada

                        @endif

                    </div>

                </div>

            @endforeach

        </div>





    </article>

</div>
