
<div
    data-avisar-cambios
    wire:key="formulario-baja-{{ $equipoId }}"
    x-data="{
        successVisible: false,
        successMessage: '',
        successTimer: null,

        showSuccess(message) {
            this.successMessage = message;

            window.trackitToast?.(
                'success',
                this.successMessage
            );

            if (this.successTimer) {
                clearTimeout(this.successTimer);
            }

            this.successTimer = setTimeout(() => {
                this.successVisible = false;
            }, 3000);
        }
    }"
    @baja-formulario-validado.window="showSuccess($event.detail.message)"
    @descargar-acta-baja.window="
        const enlace = document.createElement('a');
        enlace.href = $event.detail.url;
        enlace.style.display = 'none';
        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();
    "
>
    @php
        $dato = static fn ($valor) =>
            filled($valor)
                ? trim((string) $valor)
                : '—';

        $marcaModelo = trim(
            implode(
                ' ',
                array_filter([
                    $equipo['marca'] ?? null,
                    $equipo['modelo'] ?? null,
                ])
            )
        );

        $datosEquipo = [
            'Nombre del activo' =>
                $equipo['nombre_equipo'] ?? null,

            'Número de serie' =>
                $equipo['numero_serie'] ?? null,

            'Fecha de registro' =>
                $equipo['fecha_registro'] ?? null,

            'Tipo' =>
                $equipo['tipo_equipo'] ?? null,

            'Código de inventario' =>
                $equipo['codigo_inventario'] ?? null,

            'Factura' =>
                $equipo['numero_factura'] ?? null,

            'Marca' =>
                $equipo['marca'] ?? null,

            'Estado' =>
                $equipo['estado'] ?? null,

            'Garantía' =>
                $equipo['fecha_fin_garantia'] ?? null,

            'Modelo' =>
                $equipo['modelo'] ?? null,

            'Proveedor' =>
                $equipo['proveedor'] ?? null,
        ];

        $fotografias = [
            [
                'titulo' => 'Vista frontal',
                'modelo' => 'fotoFrontal',
                'tipo' => 'frontal',
                'foto' => $fotoFrontal,
            ],
            [
                'titulo' => 'Vista trasera',
                'modelo' => 'fotoTrasera',
                'tipo' => 'trasera',
                'foto' => $fotoTrasera,
            ],
            [
                'titulo' => 'Vista lateral 1',
                'modelo' => 'fotoLateral1',
                'tipo' => 'lateral1',
                'foto' => $fotoLateral1,
            ],
            [
                'titulo' => 'Vista lateral 2',
                'modelo' => 'fotoLateral2',
                'tipo' => 'lateral2',
                'foto' => $fotoLateral2,
            ],
        ];
    @endphp

    <style>
        .baja-documento {
            background: #ffffff;
            color: #000000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 7px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .baja-documento * {
            box-sizing: border-box;
        }

        .baja-documento .marca {
            color: #a58d40;
            font-size: 16px;
            letter-spacing: 2px;
        }

        .baja-documento .grupo {
            color: #a58d40;
            font-size: 6px;
            letter-spacing: 3px;
        }

        .baja-documento .institucion {
            margin-top: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #000000;
            color: #000000;
            font-size: 6px;
        }

        .baja-documento h2 {
            margin: 10px 0 3px;
            color: #000000;
            font-size: 12px;
            font-weight: normal;
        }

        .baja-documento .subtitulo {
            color: #000000;
            margin-bottom: 9px;
        }

        .baja-documento .seccion {
            margin: 9px 0 4px;
            padding: 7px 8px;




        background: #000000; color: #ffffff; font-weight: normal;
}

        .baja-documento .numero {
            display: inline-block;
            margin-right: 8px;
        }

        .baja-documento .campos {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));

        gap: 1px; padding: 1px; background: #000000;
}

        .baja-documento .campos.tres {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

        .baja-documento .campo {
            min-width: 0;
            padding: 7px;
            border: 0; background: #ffffff;
        }

        .baja-documento .campo.ancho {
            grid-column: 1 / -1;
        }

        .baja-documento .etiqueta {
            display: block;
            margin-bottom: 4px;
            color: #000000;
            font-size: 6px;
            font-weight: normal;
            text-transform: uppercase;
        }

        .baja-documento .valor {
            display: block;
            color: #000000;
            font-weight: normal;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .baja-documento .firmas {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        .baja-documento .firma {
            border: 1px solid #000000;
            padding: 7px;
        }

        .baja-documento .firma-titulo {
            color: #000000;
            font-weight: normal;
        }

        .baja-documento .linea {
            height: 45px;
            border-bottom: 1px solid #000000;
        }

        .baja-documento .firma-pie {
            margin-top: 5px;
            font-size: 6px;
            color: #000000;
        }

        .baja-documento .fotografias {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        .baja-documento .fotografia {
            min-width: 0;
            border: 1px solid #000000;
        }

        .baja-documento .foto-titulo {
            background: #ffffff;
            padding: 6px;
            font-weight: normal;
        }

        .baja-documento .foto-cuerpo {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 115px;
            padding: 5px;
            text-align: center;
            color: #000000;
        }

        .baja-documento .foto-cuerpo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .baja-documento .pie {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #000000;
            color: #000000;
            font-size: 6px;
        }

        .baja-documento .pagina-evidencias {
            margin-top: 20px;
        }
    </style>

    {{-- NOTIFICACIÓN DE ÉXITO --}}

    <div
        x-show="successVisible"
        x-cloak
        x-transition
        class="fixed top-20 left-4 right-4 z-[150] flex items-start gap-3 rounded-xl border border-emerald-500/30 bg-[var(--theme-surface)] px-4 py-3 shadow-lg sm:left-auto sm:right-6 sm:w-96"
    >
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500">
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            ><use href="{{ \App\Support\Icono::url('i-e7e7dec') }}"></use></svg>
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Información completa
            </p>

            <p
                class="mt-0.5 text-xs text-[var(--theme-text)]"
                x-text="successMessage"
            ></p>
        </div>

        <button
            type="button"
            @click="successVisible = false"
            class="text-[var(--theme-text-muted)] hover:text-[var(--theme-text)]"
            aria-label="Cerrar notificación"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            ><use href="{{ \App\Support\Icono::url('i-c00fbb2') }}"></use></svg>
        </button>
    </div>

    {{-- PROGRESO DE BAJAS INDIVIDUALES --}}

    @if ($this->enCola())

        @php
            $totalLote = count($hechos) + count($pendientes);
            $posicionLote = count($hechos) + 1;
        @endphp

        <section
            wire:key="baja-lote-{{ $equipoId }}"
            aria-label="Progreso de la baja en lote"
            class="mb-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3 sm:px-5"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">

                <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    Baja en lote · equipo
                    {{ $posicionLote }}
                    de
                    {{ $totalLote }}
                </p>

                <p class="text-xs text-[var(--theme-text-muted)]">
                    Al confirmar se genera el PDF.
                    Para el siguiente equipo solo se conserva la fecha.
                </p>

            </div>

            @if (count($tiposCola) > 1)

                <p
                    role="alert"
                    class="mt-2 flex items-start gap-2 rounded-lg border border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] px-3 py-2 text-xs text-[var(--theme-text)]"
                >
                    <svg
                        class="mt-0.5 h-4 w-4 shrink-0 text-[var(--theme-warning)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    ><use href="{{ \App\Support\Icono::url('i-a049c21') }}"></use></svg>

                    <span>
                        <span class="font-semibold">
                            Estás dando de baja equipos de diferente tipo:
                        </span>

                        {{ collect($tiposCola)->join(', ', ' y ') }}.
                    </span>
                </p>

            @endif

            <ol class="mt-3 flex flex-wrap gap-1.5">

                @foreach ($cola as $idLote)

                    @php
                        $estadoLote = in_array(
                            $idLote,
                            $hechos,
                            true
                        )
                            ? 'hecho'
                            : (
                                $idLote === $equipoId
                                    ? 'actual'
                                    : (
                                        in_array(
                                            $idLote,
                                            $pendientes,
                                            true
                                        )
                                            ? 'pendiente'
                                            : 'previo'
                                    )
                            );
                    @endphp

                    <li
                        @class([
                            'inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-medium',
                            'border-[var(--theme-primary-border-strong)] bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]' => $estadoLote === 'actual',
                            'border-[var(--theme-border)] text-[var(--theme-text-muted)] line-through' => $estadoLote === 'hecho',
                            'border-[var(--theme-border)] text-[var(--theme-text)]' => $estadoLote === 'pendiente',
                            'border-dashed border-[var(--theme-border)] text-[var(--theme-text-muted)]' => $estadoLote === 'previo',
                        ])

                        @if ($estadoLote === 'actual')
                            aria-current="step"
                        @endif

                        title="{{ match ($estadoLote) {
                            'hecho' => 'Dado de baja',
                            'actual' => 'Equipo actual',
                            'pendiente' => 'Pendiente',
                            default => 'Ya estaba dado de baja: consulta sus actas anteriores en Reportes'
                        } }}"
                    >
                        @if ($estadoLote === 'hecho')
                            <svg
                                class="h-3 w-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                aria-hidden="true"
                            ><use href="{{ \App\Support\Icono::url('i-e7e7dec') }}"></use></svg>
                        @endif

                        {{ $codigosCola[$idLote] ?? $idLote }}
                    </li>

                @endforeach

            </ol>
        </section>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | BOTONES SUPERIORES
    |--------------------------------------------------------------------------
    | Se aplicó el mismo estilo del botón
    | Dar de baja del módulo Inventario.
    --}}

    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-end">

        <div class="flex flex-wrap items-center gap-3">

            {{-- CANCELAR --}}

            <button type="button" wire:click="omitir" wire:loading.attr="disabled" class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)]"
            >
                Cancelar
            </button>

            {{-- DAR DE BAJA Y GENERAR PDF --}}

            <button
                type="button"
                wire:click="confirmar"
                wire:loading.attr="disabled"
                wire:target="confirmar"
                data-guardar
                class="
                    inline-flex
                    h-10
                    items-center
                    justify-center
                    gap-2
                    rounded-md
                    bg-[var(--theme-danger-soft)]
                    px-5
                    text-sm
                    font-medium
                    text-[var(--theme-danger)]
                    transition-all
                    duration-200
                    hover:opacity-80
                    disabled:cursor-not-allowed
                    disabled:opacity-50
                "
            >
                {{-- ICONO NORMAL --}}

                <svg
                    wire:loading.remove
                    wire:target="confirmar"
                    class="h-4 w-4 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                ><use href="{{ \App\Support\Icono::url('i-0a879ac') }}"></use></svg>

                {{-- ICONO CARGANDO --}}

                <svg
                    wire:loading
                    wire:target="confirmar"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                ><use href="{{ \App\Support\Icono::url('i-5387e71') }}"></use></svg>

                {{-- TEXTO NORMAL --}}

                <span
                    wire:loading.remove
                    wire:target="confirmar"
                >
                    Dar de baja y generar PDF
                </span>

                {{-- TEXTO CARGANDO --}}

                <span
                    wire:loading
                    wire:target="confirmar"
                >
                    Generando PDF...
                </span>

            </button>

        </div>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | CONTENIDO PRINCIPAL
    |--------------------------------------------------------------------------
    --}}

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(340px,0.95fr)]">

        {{-- COLUMNA IZQUIERDA --}}

        <div class="space-y-4">

            {{-- INFORMACIÓN GENERAL --}}

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">

                <div class="mb-5 flex items-center gap-2">

                    <svg
                        class="h-5 w-5 text-[var(--theme-text-muted)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    ><use href="{{ \App\Support\Icono::url('i-501b71d') }}"></use></svg>

                    <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                        Información general
                    </h2>

                </div>

                <div class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ($datosEquipo as $etiqueta => $valor)

                        <div>
                            <p class="text-xs font-medium text-[var(--theme-text)]">
                                {{ $etiqueta }}
                            </p>

                            <p class="mt-1 break-words text-sm text-[var(--theme-text-muted)]">
                                {{ $dato($valor) }}
                            </p>
                        </div>

                    @endforeach

                </div>

            </section>

            {{-- INFORMACIÓN DE BAJA --}}

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">

                <h2 class="mb-4 text-base font-semibold text-[var(--theme-text-strong)]">
                    Información de baja
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    @include('livewire.equipos.numero-ticket')

                    {{-- MOTIVO --}}

                    <div>
                        <label
                            for="motivo-baja"
                            class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]"
                        >
                            Motivo de baja
                            <span class="text-[var(--theme-danger)]">*</span>
                        </label>

                        <input
                            id="motivo-baja"
                            type="text"
                            wire:model.live.debounce.350ms="motivoBaja"
                            maxlength="500"
                            placeholder="Escribe el motivo de la baja"
                            class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                        >

                        @error('motivoBaja')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- FECHA --}}

                    <div>
                        <label
                            for="fecha-baja"
                            class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]"
                        >
                            Fecha de baja
                            <span class="text-[var(--theme-danger)]">*</span>
                        </label>

                        <input
                            id="fecha-baja"
                            type="date"
                            wire:model.live="fechaBaja"
                            class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                        >

                        @error('fechaBaja')
                            <p class="mt-1 text-xs text-[var(--theme-danger)]">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                {{-- COMENTARIOS --}}

                <div class="mt-4">

                    <label
                        for="comentarios-baja"
                        class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]"
                    >
                        Comentarios de baja
                    </label>

                    <textarea
                        id="comentarios-baja"
                        wire:model.live.debounce.350ms="comentariosBaja"
                        rows="3"
                        maxlength="2000"
                        placeholder="Ingresa comentarios o detalles adicionales sobre la baja del equipo"
                        class="min-h-[88px] w-full resize-y rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                    ></textarea>

                    @error('comentariosBaja')
                        <p class="mt-1 text-xs text-[var(--theme-danger)]">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </section>

            {{-- EVIDENCIAS FOTOGRÁFICAS --}}

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-5">

                <div class="mb-4">

                    <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                        Evidencias de baja
                    </h2>

                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        Agrega las cuatro fotografías que se utilizarán
                        en el formato oficial de baja.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    @foreach ($fotografias as $fotografia)

                        <div
                            wire:key="baja-foto-{{ $equipoId }}-{{ $fotografia['tipo'] }}"
                            class="rounded-lg border border-[var(--theme-border)] p-3"
                        >

                            <div class="mb-3 flex items-center justify-between gap-2">

                                <span class="text-sm font-medium text-[var(--theme-text-strong)]">
                                    {{ $fotografia['titulo'] }}
                                </span>

                                @if ($fotografia['foto'])
                                    <button
                                        type="button"
                                        wire:click="eliminarFoto('{{ $fotografia['tipo'] }}')"
                                        wire:loading.attr="disabled"
                                        class="text-xs text-[var(--theme-danger)] hover:underline"
                                    >
                                        Quitar
                                    </button>
                                @endif

                            </div>

                            @if ($fotografia['foto'])

                                <div class="overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface-soft)]">

                                    <img
                                        src="{{ $fotografia['foto']->temporaryUrl() }}"
                                        alt="{{ $fotografia['titulo'] }}"
                                        class="h-36 w-full object-contain"
                                    >

                                </div>

                            @else

                                <label class="flex h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] px-3 text-center transition-colors hover:border-[var(--theme-primary)]">

                                    <svg
                                        class="mb-2 h-5 w-5 text-[var(--theme-text-muted)]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    ><use href="{{ \App\Support\Icono::url('i-5480bc2') }}"></use></svg>

                                    <span class="text-xs text-[var(--theme-text)]">
                                        Seleccionar fotografía
                                    </span>

                                    <span class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                                        JPG, PNG o WEBP
                                    </span>

                                    <input
                                        type="file"
                                        wire:model="{{ $fotografia['modelo'] }}"
                                        wire:key="baja-archivo-{{ $equipoId }}-{{ $fotografia['tipo'] }}"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                    >

                                </label>

                            @endif

                            <div
                                wire:loading
                                wire:target="{{ $fotografia['modelo'] }}"
                                class="mt-2 text-xs text-[var(--theme-text-muted)]"
                            >
                                Cargando fotografía...
                            </div>

                            @error($fotografia['modelo'])
                                <p class="mt-2 text-xs text-[var(--theme-danger)]">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    @endforeach

                </div>

            </section>

            {{-- INFORMACIÓN --}}

            <div class="flex items-start gap-3 rounded-xl border border-sky-500/20 bg-sky-500/10 px-4 py-3">

                <svg
                    class="mt-0.5 h-4 w-4 shrink-0 text-sky-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                ><use href="{{ \App\Support\Icono::url('i-501b71d') }}"></use></svg>

                <div>

                    <p class="text-xs font-semibold text-sky-500">
                        Información
                    </p>

                    <p class="mt-0.5 text-xs text-[var(--theme-text)]">
                        Para procesar la baja se requiere el motivo
                        y la autorización. Las fotografías que adjuntes
                        se guardarán como evidencias del equipo.
                    </p>

                </div>

            </div>

        </div>

        {{-- COLUMNA DERECHA --}}

        <div class="space-y-4">

            {{-- VISTA RÁPIDA DEL DOCUMENTO --}}

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4">

                <div class="mb-4 flex items-center justify-between gap-3">

                    <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                        Vista rápida del documento final
                    </h2>

                    <span class="rounded border border-[var(--theme-border-strong)] px-2 py-0.5 text-[10px] font-medium text-[var(--theme-text-muted)]">
                        PDF
                    </span>

                </div>

                <div class="h-[600px] min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain pr-1 [scrollbar-width:thin] [scrollbar-color:#64748b_transparent] mx-auto w-full border border-[var(--theme-border)] bg-white shadow-md">

                    <article class="baja-documento p-[5%]">

                        {{-- LOGO --}}

                        <img
                            src="{{ app(\App\Services\OrganizacionService::class)->logoUrl() }}"
                            alt="{{ app(\App\Services\OrganizacionService::class)->nombre() }}"
                            style="max-width:150px; max-height:45px; margin-bottom:8px;"
                        >

                        <div class="institucion">
                            {{ app(\App\Services\OrganizacionService::class)->nombre() }}
                            | CONTROL DE ACTIVOS TI
                        </div>

                        <h2>ORDEN DE DECOMISO DE ACTIVOS</h2>

                        <div class="subtitulo">
                            Baja de equipo tecnológico ·
                            Control documental y autorización
                        </div>

                        {{-- 01 DATOS DE LA BAJA --}}

                        <div class="seccion">
                            <span class="numero">01</span>
                            DATOS DE LA BAJA
                        </div>

                        <div class="campos tres">

                            <div class="campo">

                                <span class="etiqueta">
                                    Fecha de baja
                                </span>

                                <span class="valor">
                                    {{ $fechaBaja
                                        ? \Carbon\Carbon::parse($fechaBaja)->format('d/m/Y')
                                        : '—'
                                    }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    N° Tiket:
                                </span>

                                <span class="valor">
                                    {{ $numeroTicket !== '' ? 'GPRVM-'.$numeroTicket : 'Por capturar' }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Código de inventario
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['codigo_inventario'] ?? null) }}
                                </span>

                            </div>

                        </div>

                        {{-- 02 INFORMACIÓN GENERAL --}}

                        <div class="seccion">
                            <span class="numero">02</span>
                            INFORMACIÓN GENERAL DEL EQUIPO
                        </div>

                        <div class="campos">

                            <div class="campo">

                                <span class="etiqueta">
                                    Nombre del activo
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['nombre_equipo'] ?? null) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Número de serie
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['numero_serie'] ?? null) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Tipo de equipo
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['tipo_equipo'] ?? null) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Marca / Modelo
                                </span>

                                <span class="valor">
                                    {{ $dato($marcaModelo) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Fecha de registro
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['fecha_registro'] ?? null) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Factura
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['numero_factura'] ?? null) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Proveedor
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['proveedor'] ?? null) }}
                                </span>

                            </div>

                            <div class="campo">

                                <span class="etiqueta">
                                    Garantía
                                </span>

                                <span class="valor">
                                    {{ $dato($equipo['fecha_fin_garantia'] ?? null) }}
                                </span>

                            </div>

                        </div>

                        {{-- 03 JUSTIFICACIÓN --}}

                        <div class="seccion">
                            <span class="numero">03</span>
                            JUSTIFICACIÓN DE LA BAJA
                        </div>

                        <div class="campos">

                            <div class="campo ancho">

                                <span class="etiqueta">
                                    Motivo de baja
                                </span>

                                <span class="valor">
                                    {{ $dato($motivoBaja) }}
                                </span>

                            </div>

                            <div class="campo ancho">

                                <span class="etiqueta">
                                    Comentarios y detalles adicionales
                                </span>

                                <span class="valor">
                                    {{ $dato($comentariosBaja) }}
                                </span>

                            </div>

                        </div>

                        {{-- 04 FIRMAS --}}

                        <div class="seccion">
                            <span class="numero">04</span>
                            AUTORIZACIÓN Y FIRMAS DE BAJA
                        </div>

                        @include('reportes.partials.firmas-baja', [
                            'firmasDocumento' => $firmas,
                            'fechaDocumento' => $fechaBaja,
                        ])

                        <div class="pie">
                            Departamento de Sistemas · Página 1 de 2
                        </div>

                        {{-- EVIDENCIAS --}}

                        <div class="pagina-evidencias">

                            <div class="fotografias">

                                @foreach ($fotografias as $fotografia)

                                    <div class="fotografia">

                                        <div class="foto-titulo">
                                            {{ $fotografia['titulo'] }}
                                        </div>

                                        <div class="foto-cuerpo">

                                            @if ($fotografia['foto'])

                                                <img
                                                    src="{{ $fotografia['foto']->temporaryUrl() }}"
                                                    alt="{{ $fotografia['titulo'] }}"
                                                >

                                            @else

                                                Evidencia fotográfica no registrada

                                            @endif

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    </article>

                </div>

                <p class="mt-3 text-center text-[11px] text-[var(--theme-text-muted)]">
                    Vista preliminar. El tiket capturado se incluirá en el acta.
                </p>

            </section>

            {{-- FIRMAS DE BAJA --}}

            @include('livewire.equipos.firmas-baja')

            {{-- AUTORIZACIÓN --}}

            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4">

                <label
                    for="autoriza"
                    class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]"
                >
                    Autoriza
                    <span class="text-[var(--theme-danger)]">*</span>
                </label>

                <input
                    id="autoriza"
                    type="text"
                    wire:model.live.debounce.350ms="autoriza"
                    maxlength="255"
                    placeholder="Nombre completo de la persona que autoriza la baja"
                    class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]"
                >

                @error('autoriza')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </section>

        </div>

    </div>
</div>
