
<div
    x-data="{
        successVisible: false,
        successMessage: '',
        successTimer: null,
        showSuccess(message) {
            this.successMessage = message;
            window.trackitToast?.('success', this.successMessage); /* aviso global */

            if (this.successTimer) {
                clearTimeout(this.successTimer);
            }

            this.successTimer = setTimeout(() => {
                this.successVisible = false;
            }, 3000);
        }
    }"
    @baja-formulario-validado.window="showSuccess($event.detail.message)"
>
    @php
        $dato = static fn ($valor) =>
            filled($valor) ? trim((string) $valor) : '—';

        $marcaModelo = trim(implode(' ', array_filter([
            $equipo['marca'] ?? null,
            $equipo['modelo'] ?? null,
        ])));

        $datosEquipo = [
            'Nombre del activo' => $equipo['nombre_equipo'] ?? null,
            'Número de serie' => $equipo['numero_serie'] ?? null,
            'Fecha de registro' => $equipo['fecha_registro'] ?? null,
            'Tipo' => $equipo['tipo_equipo'] ?? null,
            'Código de inventario' => $equipo['codigo_inventario'] ?? null,
            'Factura' => $equipo['numero_factura'] ?? null,
            'Marca' => $equipo['marca'] ?? null,
            'Estado' => $equipo['estado'] ?? null,
            'Garantía' => $equipo['fecha_fin_garantia'] ?? null,
            'Modelo' => $equipo['modelo'] ?? null,
            'Proveedor' => $equipo['proveedor'] ?? null,
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
            background: #fff;
            color: #273f57;
            font-family: Arial, Helvetica, sans-serif;
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
            border-bottom: 2px solid #ad9647;
            color: #718293;
            font-size: 6px;
        }

        .baja-documento h2 {
            margin: 10px 0 3px;
            color: #213c55;
            font-size: 12px;
            font-weight: bold;
        }

        .baja-documento .subtitulo {
            color: #748494;
            margin-bottom: 9px;
        }

        .baja-documento .seccion {
            margin: 9px 0 4px;
            padding: 7px 8px;
            background: #eff1f3;
            color: #2b435b;
            font-weight: bold;
        }

        .baja-documento .numero {
            display: inline-block;
            margin-right: 8px;
        }

        .baja-documento .campos {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .baja-documento .campos.tres {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .baja-documento .campo {
            min-width: 0;
            padding: 7px;
            border: 1px solid #d6dfe7;
        }

        .baja-documento .campo.ancho {
            grid-column: 1 / -1;
        }

        .baja-documento .etiqueta {
            display: block;
            margin-bottom: 4px;
            color: #617b91;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .baja-documento .valor {
            display: block;
            color: #253d56;
            font-weight: 600;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .baja-documento .firmas {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        .baja-documento .firma {
            border: 1px solid #d6dfe7;
            padding: 7px;
        }

        .baja-documento .firma-titulo {
            color: #213c55;
            font-weight: bold;
        }

        .baja-documento .linea {
            height: 45px;
            border-bottom: 1px solid #8797a6;
        }

        .baja-documento .firma-pie {
            margin-top: 5px;
            font-size: 6px;
            color: #718293;
        }

        .baja-documento .fotografias {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        .baja-documento .fotografia {
            min-width: 0;
            border: 1px solid #d6dfe7;
        }

        .baja-documento .foto-titulo {
            background: #eff1f3;
            padding: 6px;
            font-weight: bold;
        }

        .baja-documento .foto-cuerpo {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 115px;
            padding: 5px;
            text-align: center;
            color: #8694a2;
        }

        .baja-documento .foto-cuerpo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .baja-documento .pie {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #e0e5ea;
            color: #8593a0;
            font-size: 6px;
        }

        .baja-documento .pagina-evidencias {
            margin-top: 20px;
        }
    </style>

    {{-- NOTIFICACIÓN --}}
    <div
        x-show="successVisible"
        x-cloak
        x-transition
        class="fixed top-20 left-4 right-4 z-[150] flex items-start gap-3 rounded-xl border border-emerald-500/30 bg-[var(--theme-surface)] px-4 py-3 shadow-lg sm:left-auto sm:right-6 sm:w-96"
    >
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12l4 4L19 7"/>
            </svg>
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
            <svg class="h-4 w-4" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    {{-- ENCABEZADO --}}
    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl font-semibold leading-tight text-[var(--theme-text-strong)]">
                Dar de baja equipo
            </h1>

            <p class="mt-1 text-xs leading-relaxed text-[var(--theme-text-muted)]">
                <a
                    href="{{ route('equipos.index') }}"
                    class="transition-colors hover:text-[var(--theme-primary)]"
                >
                    Inventario
                </a>

                <span class="mx-1">›</span>

                <a
                    href="{{ route('equipos.show', $equipoId) }}"
                    class="transition-colors hover:text-[var(--theme-primary)]"
                >
                    Detalle del equipo
                </a>

                <span class="mx-1">›</span>

                <a
                    href="{{ route('equipos.edit', $equipoId) }}"
                    class="transition-colors hover:text-[var(--theme-primary)]"
                >
                    Editar equipo
                </a>

                <span class="mx-1">›</span>
                <span class="text-[var(--theme-text)]">Dar de baja</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('equipos.edit', $equipoId) }}"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)]"
            >
                Cancelar
            </a>

            <x-ui.button
                wire:click="confirmar"
                target="confirmar"
                variant="danger"
                loading-text="Dando de baja…"
            >
                Confirmar baja
            </x-ui.button>
        </div>
    </div>

    {{-- CONTENIDO --}}
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
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 10v6M12 7h.01"/>
                    </svg>

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

            {{-- EVIDENCIAS --}}
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
                            wire:key="baja-foto-{{ $fotografia['tipo'] }}"
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
                                        wire:target="eliminarFoto('{{ $fotografia['tipo'] }}')"
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
                                <label
                                    class="flex h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] px-3 text-center transition-colors hover:border-[var(--theme-primary)]"
                                >
                                    <svg
                                        class="mb-2 h-5 w-5 text-[var(--theme-text-muted)]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>
                                    </svg>

                                    <span class="text-xs text-[var(--theme-text)]">
                                        Seleccionar fotografía
                                    </span>
                                    <span class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                                        JPG, PNG o WEBP
                                    </span>

                                    <input
                                        type="file"
                                        wire:model="{{ $fotografia['modelo'] }}"
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

            <div class="flex items-start gap-3 rounded-xl border border-sky-500/20 bg-sky-500/10 px-4 py-3">
                <svg
                    class="mt-0.5 h-4 w-4 shrink-0 text-sky-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 10v6M12 7h.01"/>
                </svg>

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

            {{-- VISTA RÁPIDA: FORMATO OFICIAL --}}
            <section class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                        Vista rápida del documento final
                    </h2>

                    <span class="rounded border border-[var(--theme-border-strong)] px-2 py-0.5 text-[10px] font-medium text-[var(--theme-text-muted)]">
                        PDF
                    </span>
                </div>

                <div class="mx-auto w-full max-w-[480px] overflow-hidden border border-[var(--theme-border)] bg-white shadow-md">
                    <article class="baja-documento p-[5%]">

                        <div class="marca">PALLADIUM</div>
                        <div class="grupo">HOTEL GROUP</div>

                        <div class="institucion">
                            GRAND PALLADIUM RIVIERA MAYA |
                            CONTROL DE ACTIVOS TI
                        </div>

                        <h2>ORDEN DE DECOMISO DE ACTIVOS</h2>

                        <div class="subtitulo">
                            Baja de equipo tecnológico · Control documental y autorización
                        </div>

                        {{-- 01 --}}
                        <div class="seccion">
                            <span class="numero">01</span>
                            DATOS DE LA BAJA
                        </div>

                        <div class="campos tres">
                            <div class="campo">
                                <span class="etiqueta">Fecha de baja</span>
                                <span class="valor">
                                    {{ $fechaBaja ? \Carbon\Carbon::parse($fechaBaja)->format('d/m/Y') : '—' }}
                                </span>
                            </div>

                            <div class="campo">
                                <span class="etiqueta">Folio de baja</span>
                                <span class="valor">
                                    Pendiente de generar
                                </span>
                            </div>

                            <div class="campo">
                                <span class="etiqueta">Código de inventario</span>
                                <span class="valor">
                                    {{ $dato($equipo['codigo_inventario'] ?? null) }}
                                </span>
                            </div>
                        </div>

                        {{-- 02 --}}
                        <div class="seccion">
                            <span class="numero">02</span>
                            INFORMACIÓN GENERAL DEL EQUIPO
                        </div>

                        <div class="campos">
                            <div class="campo">
                                <span class="etiqueta">Nombre del activo</span>
                                <span class="valor">
                                    {{ $dato($equipo['nombre_equipo'] ?? null) }}
                                </span>
                            </div>

                            <div class="campo">
                                <span class="etiqueta">Número de serie</span>
                                <span class="valor">
                                    {{ $dato($equipo['numero_serie'] ?? null) }}
                                </span>
                            </div>

                            <div class="campo">
                                <span class="etiqueta">Tipo de equipo</span>
                                <span class="valor">
                                    {{ $dato($equipo['tipo_equipo'] ?? null) }}
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
                                    {{ $dato($equipo['numero_factura'] ?? null) }}
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
                                    {{ $dato($equipo['fecha_fin_garantia'] ?? null) }}
                                </span>
                            </div>
                        </div>

                        {{-- 03 --}}
                        <div class="seccion">
                            <span class="numero">03</span>
                            JUSTIFICACIÓN DE LA BAJA
                        </div>

                        <div class="campos">
                            <div class="campo ancho">
                                <span class="etiqueta">Motivo de baja</span>
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

                        {{-- 04 --}}
                        <div class="seccion">
                            <span class="numero">04</span>
                            AUTORIZACIÓN Y FIRMAS DE BAJA
                        </div>

                        <div class="campo">
                            <span class="etiqueta">Persona que autoriza</span>
                            <span class="valor">
                                {{ $dato($autoriza) }}
                            </span>
                        </div>

                        <div class="firmas">
                            <div class="firma">
                                <div class="firma-titulo">
                                    Entregó · Departamento de Sistemas
                                </div>

                                <div class="linea"></div>

                                <div class="firma-pie">
                                    Nombre y firma
                                    <div>Fecha: ____ / ____ / ______</div>
                                </div>
                            </div>

                            <div class="firma">
                                <div class="firma-titulo">
                                    Autorizó · Responsable de la baja
                                </div>

                                <div class="linea"></div>

                                <div class="firma-pie">
                                    {{ $dato($autoriza) }}
                                    <div>Fecha: ____ / ____ / ______</div>
                                </div>
                            </div>
                        </div>

                        <div class="pie">
                            Departamento de Sistemas · Página 1 de 2
                        </div>

                        {{-- PÁGINA 2: EVIDENCIAS --}}
                        <div class="pagina-evidencias">
                            <div class="seccion">
                                EVIDENCIAS FOTOGRÁFICAS · PÁGINA 2
                            </div>

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

                            <div class="pie">
                                Departamento de Sistemas · Página 2 de 2
                            </div>
                        </div>
                    </article>
                </div>

                <p class="mt-3 text-center text-[11px] text-[var(--theme-text-muted)]">
                    Vista preliminar. El folio definitivo se genera al confirmar la baja.
                </p>
            </section>

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
                    class="h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
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
