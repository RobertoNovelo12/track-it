        <div class="space-y-3">

        {{-- ====================================================
            GESTIÓN DE USUARIOS
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                overflow-hidden
            "
        >

            {{-- Cabecera --}}
            <div
                class="
                    px-4
                    sm:px-5
                    py-4


                    flex
                    flex-col
                    lg:flex-row
                    lg:items-center
                    lg:justify-between

                    gap-3
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Gestión de usuarios
                    </h2>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Consulta y administra las cuentas registradas.
                    </p>

                </div>


                {{-- Filtros --}}
                <div
                    class="
                        flex
                        flex-col
                        sm:flex-row
                        gap-2
                    "
                >

                    {{-- Buscar --}}
                    <div
                        class="
                            relative
                            w-full
                            sm:w-64
                        "
                    >

                        {{-- Lupa --}}
                        <svg
                            wire:loading.remove
                            wire:target="search"
                            class="
                                absolute
                                left-3
                                top-1/2
                                -translate-y-1/2

                                w-4
                                h-4

                                text-[var(--theme-text-muted)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        ><use href="{{ \App\Support\Icono::url('i-8bdcd83') }}"></use></svg>


                        {{-- Spinner mientras busca --}}
                        <svg
                            wire:loading
                            wire:target="search"
                            class="
                                absolute
                                left-3
                                top-1/2
                                -translate-y-1/2

                                w-4
                                h-4

                                animate-spin

                                text-[var(--theme-primary)]

                                pointer-events-none
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M21 12a9 9 0 00-9-9v3a6 6 0 016 6h3z"
                            />
                        </svg>


                        <input
                            type="search"
                            wire:model="search"
                            wire:keydown.enter="buscar"
                            placeholder="Buscar usuario..."
                            autocomplete="off"
                            class="
                                w-full
                                h-9

                                pl-9
                                pr-3

                                rounded-md

                                bg-[var(--theme-surface-soft)]

                                border
                                border-[var(--theme-border)]

                                text-xs
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]
                            "
                        >

                    </div>


                    {{-- Estado --}}
                    <div class="w-44 shrink-0">
                        <x-searchable-select
                            wire-model="estado"
                            :empty-as-null="false"
                            placeholder="Todos los estados"
                            :show-clear="true"
                            clear-label="Todos los estados"
                            compact
                            :options="[
                                ...collect($estados)->map(fn ($estadoOption) => ['value' => $estadoOption->clave, 'label' => $estadoOption->nombre])->all(),
                            ]"
                        />
                    </div>

                </div>

            </div>

        </section>

        {{-- =================================================
            TABLA: componente común (components/data-table)
            Mismo indicador de actualización, tarjetas en móvil,
            estado vacío y paginación que el resto del sistema.
            Las acciones abren los mismos modales de siempre:
            se les pasan los datos que antes iban en data-*.
        ================================================= --}}
        <x-data-table
            :rows="$usuarios"
            row-key="id_usuario"
            key-prefix="usuario"
            page-name="usuariosPage"
            per-page-model="perPage"
            refresh-action="$refresh"
            min-width="980px"
            result-singular="usuario encontrado"
            result-plural="usuarios encontrados"
            empty-message="No se encontraron usuarios."
            :mobile-title="fn ($usuario) => trim($usuario->nombres . ' ' . $usuario->apellido_paterno)"
            :mobile-subtitle="fn ($usuario) => '@' . $usuario->username"
            :columns="[
                [
                    'key' => 'nombre',
                    'label' => 'Usuario',
                    'width' => '190px',
                    'strong' => true,
                    'value' => fn ($usuario) => trim($usuario->nombres . ' ' . $usuario->apellido_paterno),
                    'mobile' => false,
                ],
                [
                    'key' => 'username',
                    'label' => 'Nombre de usuario',
                    'width' => '150px',
                    'value' => fn ($usuario) => $usuario->username ? '@' . $usuario->username : null,
                    'mobile' => false,
                ],
                [
                    'key' => 'correo',
                    'label' => 'Correo',
                    'width' => '220px',
                ],
                [
                    'key' => 'organizacion',
                    'label' => 'Hotel / departamento',
                    'width' => '220px',
                    'value' => fn ($usuario) => collect([$usuario->unidad_nombre, $usuario->departamento_nombre])->filter()->implode(' · '),
                    'default' => 'Sin asignar',
                ],
                [
                    'key' => 'rol_nombre',
                    'label' => 'Rol',
                    'width' => '150px',
                    'default' => 'Sin rol',
                ],
                [
                    'key' => 'estado_nombre',
                    'label' => 'Estado',
                    'width' => '120px',
                    'type' => 'status',
                    'default' => 'Sin estado',
                ],
                [
                    'key' => 'fecha_registro',
                    'label' => 'Registro',
                    'width' => '110px',
                    'value' => fn ($usuario) => $usuario->fecha_registro
                        ? \Illuminate\Support\Carbon::parse($usuario->fecha_registro)->format('d/m/Y')
                        : null,
                ],
            ]"
            :actions="fn ($usuario) => [
                'menu' => array_values(array_filter([
                    strtoupper($usuario->estado_clave ?? '') === 'PENDIENTE'
                        ? [
                            'label' => 'Aprobar usuario',
                            'js' => 'window.openApprovalUserModalFromButton({ dataset: ' . \Illuminate\Support\Js::from([
                                'approvalUserId' => (string) $usuario->id_usuario,
                                'approvalUserName' => trim(implode(' ', array_filter([
                                    $usuario->nombres,
                                    $usuario->apellido_paterno,
                                    $usuario->apellido_materno,
                                ]))),
                                'approvalUserEmail' => (string) $usuario->correo,
                            ]) . ' })',
                        ]
                        : null,
                    [
                        'label' => 'Ver detalles',
                        'js' => 'window.openUserDetailsModalFromButton({ dataset: { userId: ' . \Illuminate\Support\Js::from((string) $usuario->id_usuario) . ' } })',
                    ],
                ])),
            ]"
        />

        </div>