{{-- ============================================================
    MODAL: DETALLES DE USUARIO

    No realiza peticiones a Livewire para abrir/cerrar.
    Los datos se toman de los usuarios ya cargados en la página.
============================================================ --}}

@php
    /*
     * Sin consultas extra: las solicitudes vigentes salen de pendientes()
     * (la misma lista del aviso de restablecimiento, se consulta una vez)
     * y los equipos asignados vienen en la consulta de usuarios.
     */
    try {
        $solicitudesRestablecimientoDetalle = collect(app(\App\Services\RestablecimientoContrasenaService::class)->pendientes())
            ->mapWithKeys(fn (array $solicitud) => [(int) $solicitud['id'] => (string) $solicitud['fecha']])
            ->all();
    } catch (\Throwable $e) {
        $solicitudesRestablecimientoDetalle = [];
    }

    $equiposPorUsuarioDetalle = $usuarios->getCollection()
        ->mapWithKeys(fn ($usuario) => [(int) $usuario->id_usuario => (int) ($usuario->equipos_asignados ?? 0)])
        ->all();

    $userDetailsData = $usuarios->getCollection()
        ->mapWithKeys(function ($usuario) use ($solicitudesRestablecimientoDetalle, $equiposPorUsuarioDetalle) {
            $solicitudReset = $solicitudesRestablecimientoDetalle[(int) $usuario->id_usuario] ?? null;

            $nombreCompleto = trim(
                implode(' ', array_filter([
                    $usuario->nombres,
                    $usuario->apellido_paterno,
                    $usuario->apellido_materno,
                ]))
            );

            return [
                (string) $usuario->id_usuario => [
                    'name' => $nombreCompleto,
                    'username' => $usuario->username,
                    'email' => $usuario->correo,
                    'collaborator' => $usuario->numero_colaborador ?: 'Sin asignar',
                    'position' => $usuario->puesto ?: 'Sin asignar',
                    'role' => $usuario->rol_nombre ?: 'Sin rol',
                    'area' => $usuario->area_nombre ?: 'Sin asignar',
                    'department' => $usuario->departamento_nombre ?: 'Sin asignar',
                    'status' => $usuario->estado_nombre ?: 'Sin estado',
                    'registered' => $usuario->fecha_registro
                        ? \App\Support\Fecha::conHora($usuario->fecha_registro)
                        : 'Sin registro',
                    'lastAccess' => $usuario->ultimo_acceso
                        ? \App\Support\Fecha::conHora($usuario->ultimo_acceso)
                        : 'Sin registro',
                    // Restablecer contraseña (solo con solicitud vigente).
                    'id' => (int) $usuario->id_usuario,
                    'resetRequestedAt' => $solicitudReset
                        ? \App\Support\Fecha::conHora($solicitudReset)
                        : null,
                    'isSelf' => (int) $usuario->id_usuario === (int) auth()->id(),
                    // Dar de baja / reactivar.
                    'statusKey' => strtoupper((string) ($usuario->estado_clave ?? '')),
                    'assignedCount' => (int) ($equiposPorUsuarioDetalle[(int) $usuario->id_usuario] ?? 0),
                ],
            ];
        });
@endphp

<script
    id="user-details-data"
    type="application/json"
>@json($userDetailsData)</script>

<div
    id="user-details-modal"
    class="
        hidden
        fixed inset-0 z-[110]
        flex items-center justify-center
        px-4 py-6
    "
    aria-hidden="true"
    onclick="if (event.target === this) window.closeUserDetailsModalFast()"
>
    <div
        id="user-details-backdrop"
        class="absolute inset-0 bg-[var(--theme-overlay)] backdrop-blur-[2px]"
        style="opacity: 0; transition: opacity 180ms ease-out;"
        onclick="window.closeUserDetailsModalFast()"
    ></div>

    <div
        id="user-details-panel"
        class="
            relative z-10
            w-full max-w-lg max-h-[90vh]
            overflow-y-auto
            bg-[var(--theme-surface)]
            border border-[var(--theme-border)]
            rounded-xl theme-shadow-xl
        "
        style="
            opacity: 0;
            transform: translateY(10px) scale(0.975);
            transition:
                opacity 180ms ease-out,
                transform 180ms cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="user-details-modal-title"
    >
        {{-- Encabezado --}}
        <div
            class="
                sticky top-0 z-10
                px-5 py-4
                bg-[var(--theme-surface)]
                border-b border-[var(--theme-border)]
                flex items-start justify-between gap-4
            "
        >
            <div>
                <h3
                    id="user-details-modal-title"
                    class="text-base font-semibold text-[var(--theme-text-strong)]"
                >
                    Detalles del usuario
                </h3>

                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Información general y organizacional de la cuenta.
                </p>
            </div>

            <button
                id="user-details-close-button"
                type="button"
                onclick="window.closeUserDetailsModalFast()"
                class="
                    w-8 h-8 shrink-0 rounded-md
                    flex items-center justify-center
                    text-[var(--theme-text-muted)]
                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text-strong)]
                    transition-colors
                "
                aria-label="Cerrar"
            >
                <svg
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>
        </div>

        {{-- Contenido --}}
        <div class="p-5 space-y-5">
            {{-- Usuario --}}
            <div
                class="
                    p-4 rounded-lg
                    bg-[var(--theme-surface-soft)]
                    border border-[var(--theme-border)]
                "
            >
                <div class="flex items-center gap-3">
                    <div
                        id="user-details-initial"
                        class="
                            w-11 h-11 shrink-0 rounded-full
                            flex items-center justify-center
                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]
                            text-sm font-semibold
                        "
                    >
                        U
                    </div>

                    <div class="min-w-0">
                        <p
                            id="user-details-name"
                            class="text-sm font-semibold text-[var(--theme-text-strong)]"
                        >
                            Usuario
                        </p>

                        <p
                            id="user-details-email"
                            class="mt-0.5 text-xs text-[var(--theme-text-muted)] break-all"
                        >
                            —
                        </p>
                    </div>
                </div>
            </div>

            {{-- Cuenta --}}
            <div>
                <p
                    class="
                        mb-2 text-[10px] font-semibold uppercase tracking-wider
                        text-[var(--theme-text-muted)]
                    "
                >
                    Cuenta
                </p>

                <div
                    class="
                        rounded-lg
                        border border-[var(--theme-border)]
                        divide-y divide-[var(--theme-border)]
                    "
                >
                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Usuario</span>
                        <span id="user-details-username" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Estado</span>
                        <span id="user-details-status" class="text-xs font-medium text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Fecha de registro</span>
                        <span id="user-details-registered" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Último acceso</span>
                        <span id="user-details-last-access" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>
                </div>
            </div>

            {{-- Contraseña: restablecer con contraseña temporal --}}
            <div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-[var(--theme-text-muted)]">
                    Contraseña
                </p>

                <div class="rounded-lg border border-[var(--theme-border)] px-3 py-3">
                    <p id="user-details-reset-text" class="text-xs leading-relaxed text-[var(--theme-text-muted)]">—</p>

                    <button
                        id="user-details-reset-button"
                        type="button"
                        onclick="window.openResetPasswordFromDetails()"
                        class="mt-3 hidden h-9 items-center justify-center gap-2 rounded-md bg-[var(--theme-primary)] px-3 text-xs font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="8" cy="15" r="4"/>
                            <path d="M10.85 12.15L19 4M18 5l2 2M15 8l2 2"/>
                        </svg>
                        Restablecer contraseña
                    </button>
                </div>
            </div>

            {{-- Cuenta: dar de baja (devuelve sus equipos) o reactivar --}}
            <div id="user-details-account-section" class="hidden">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-[var(--theme-text-muted)]">
                    Cuenta
                </p>

                <div class="rounded-lg border border-[var(--theme-border)] px-3 py-3">
                    <p id="user-details-account-text" class="text-xs leading-relaxed text-[var(--theme-text-muted)]">—</p>

                    <button
                        id="user-details-deactivate-button"
                        type="button"
                        onclick="window.darDeBajaDesdeDetalles()"
                        class="mt-3 hidden h-9 items-center justify-center gap-2 rounded-md border border-transparent bg-[var(--theme-danger-soft)] px-3 text-xs font-medium text-[var(--theme-danger)] transition-opacity hover:opacity-80"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 8h5"/>
                        </svg>
                        Dar de baja la cuenta
                    </button>

                    <button
                        id="user-details-reactivate-button"
                        type="button"
                        onclick="window.reactivarDesdeDetalles()"
                        class="mt-3 hidden h-9 items-center justify-center gap-2 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
                    >
                        Reactivar cuenta
                    </button>
                </div>
            </div>

            {{-- Organización --}}
            <div>
                <p
                    class="
                        mb-2 text-[10px] font-semibold uppercase tracking-wider
                        text-[var(--theme-text-muted)]
                    "
                >
                    Información organizacional
                </p>

                <div
                    class="
                        rounded-lg
                        border border-[var(--theme-border)]
                        divide-y divide-[var(--theme-border)]
                    "
                >
                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Número de colaborador</span>
                        <span id="user-details-collaborator" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Puesto</span>
                        <span id="user-details-position" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Rol</span>
                        <span id="user-details-role" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Área</span>
                        <span id="user-details-area" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>

                    <div class="px-3 py-2.5 flex items-start justify-between gap-4">
                        <span class="text-xs text-[var(--theme-text-muted)]">Departamento</span>
                        <span id="user-details-department" class="text-xs text-right text-[var(--theme-text)]">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pie --}}
        <div
            class="
                sticky bottom-0
                px-5 py-4
                bg-[var(--theme-surface-soft)]
                border-t border-[var(--theme-border)]
                flex justify-end
            "
        >
            <button
                type="button"
                onclick="window.closeUserDetailsModalFast()"
                class="
                    h-9 px-4 rounded-md
                    border border-[var(--theme-border-strong)]
                    bg-[var(--theme-surface)]
                    text-xs font-medium text-[var(--theme-text)]
                    hover:bg-[var(--theme-surface-soft)]
                    transition-colors
                "
            >
                Cerrar
            </button>
        </div>
    </div>
</div>

@script
<script>
(() => {
    const setText = (id, value, fallback = 'Sin asignar') => {
        const element = document.getElementById(id);
        if (!element) return;

        const normalized = String(value ?? '').trim();
        element.textContent = normalized !== '' ? normalized : fallback;
    };

    const getUsersData = () => {
        const source = document.getElementById('user-details-data');
        if (!source) return {};

        try {
            return JSON.parse(source.textContent || '{}');
        } catch (error) {
            return {};
        }
    };

    window.openUserDetailsModalFromButton = (button) => {
        if (!button) return;

        const modal = document.getElementById('user-details-modal');
        if (!modal) return;

        const userId = String(button.dataset.userId ?? '');
        const user = getUsersData()[userId];
        if (!user) return;

        const backdrop = document.getElementById('user-details-backdrop');
        const panel = document.getElementById('user-details-panel');
        const reduceMotion = window.matchMedia?.(
            '(prefers-reduced-motion: reduce)'
        )?.matches ?? false;

        setText(
            'user-details-initial',
            String(user.name ?? '').trim().charAt(0).toUpperCase(),
            'U'
        );
        setText('user-details-name', user.name, 'Usuario');
        setText('user-details-email', user.email, 'Sin correo');
        setText('user-details-username', user.username);
        setText('user-details-status', user.status, 'Sin estado');
        setText('user-details-registered', user.registered, 'Sin registro');
        setText('user-details-last-access', user.lastAccess, 'Sin registro');
        setText('user-details-collaborator', user.collaborator);
        setText('user-details-position', user.position);
        setText('user-details-role', user.role, 'Sin rol');
        setText('user-details-area', user.area);
        setText('user-details-department', user.department);

        // Contraseña: solo se restablece con una solicitud verificada vigente.
        window.__userDetailsActual = user;

        const resetButton = document.getElementById('user-details-reset-button');
        let resetText = 'Sin solicitudes. Solo se puede restablecer cuando el usuario responde sus preguntas de seguridad en «¿Olvidaste tu contraseña?».';

        if (user.isSelf) {
            resetText = 'Para cambiar tu propia contraseña usa Ajustes › Seguridad.';
        } else if (user.resetRequestedAt) {
            resetText = 'Respondió sus preguntas de seguridad el ' + user.resetRequestedAt + ' y espera una contraseña temporal.';
        }

        setText('user-details-reset-text', resetText);
        resetButton?.classList.toggle('hidden', ! (user.resetRequestedAt && ! user.isSelf));
        resetButton?.classList.toggle('inline-flex', !! (user.resetRequestedAt && ! user.isSelf));

        // Cuenta: dar de baja (con devolución de equipos) o reactivar.
        const cuenta = document.getElementById('user-details-account-section');
        const bajaBoton = document.getElementById('user-details-deactivate-button');
        const reactivarBoton = document.getElementById('user-details-reactivate-button');
        const estado = String(user.statusKey ?? '');
        const activa = estado === 'ACTIVO';
        const deBaja = estado === 'BAJA' || estado === 'INACTIVO';
        const equipos = Number(user.assignedCount ?? 0);

        cuenta?.classList.toggle('hidden', user.isSelf || ! (activa || deBaja));
        bajaBoton?.classList.toggle('hidden', ! activa);
        bajaBoton?.classList.toggle('inline-flex', activa);
        reactivarBoton?.classList.toggle('hidden', ! deBaja);
        reactivarBoton?.classList.toggle('inline-flex', deBaja);

        setText('user-details-account-text', deBaja
            ? 'Esta cuenta está dada de baja: no puede entrar al sistema.'
            : equipos > 0
                ? 'Tiene ' + equipos + (equipos === 1 ? ' equipo asignado' : ' equipos asignados') + '. Al dar de baja la cuenta se devolverán automáticamente y quedarán disponibles.'
                : 'No tiene equipos asignados a su cuenta.');

        if (window.__userDetailsCloseTimer) {
            clearTimeout(window.__userDetailsCloseTimer);
            window.__userDetailsCloseTimer = null;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (backdrop) {
            backdrop.style.opacity = '0';
            backdrop.style.transitionDuration = reduceMotion ? '0ms' : '180ms';
        }

        if (panel) {
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(10px) scale(0.975)';
            panel.style.transitionDuration = reduceMotion ? '0ms' : '180ms';
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (backdrop) backdrop.style.opacity = '1';

                if (panel) {
                    panel.style.opacity = '1';
                    panel.style.transform = 'translateY(0) scale(1)';
                }

                document
                    .getElementById('user-details-close-button')
                    ?.focus({ preventScroll: true });
            });
        });
    };

    window.darDeBajaDesdeDetalles = () => {
        const user = window.__userDetailsActual;
        if (!user) return;

        const equipos = Number(user.assignedCount ?? 0);
        window.closeUserDetailsModalFast();

        window.dispatchEvent(new CustomEvent('confirmar', {
            detail: {
                titulo: '¿Dar de baja la cuenta de ' + user.name + '?',
                mensaje: (equipos > 0
                    ? 'Se devolverán automáticamente sus ' + equipos + (equipos === 1 ? ' equipo' : ' equipos') + ' y quedarán disponibles para asignarse. '
                    : '')
                    + 'Ya no podrá entrar al sistema y se cerrarán sus sesiones. Puedes reactivarla después.',
                confirmar: 'Dar de baja',
                peligro: true,
                accion: () => $wire.darDeBajaUsuario(user.id),
            },
        }));
    };

    window.reactivarDesdeDetalles = () => {
        const user = window.__userDetailsActual;
        if (!user) return;

        window.closeUserDetailsModalFast();

        window.dispatchEvent(new CustomEvent('confirmar', {
            detail: {
                titulo: '¿Reactivar la cuenta de ' + user.name + '?',
                mensaje: 'Podrá volver a entrar al sistema con su contraseña. Los equipos que se devolvieron no se reasignan solos.',
                confirmar: 'Reactivar',
                accion: () => $wire.reactivarUsuario(user.id),
            },
        }));
    };

    window.openResetPasswordFromDetails = () => {
        const user = window.__userDetailsActual;
        if (!user) return;

        window.closeUserDetailsModalFast();

        window.dispatchEvent(new CustomEvent('abrir-restablecer-contrasena', {
            detail: { id: user.id, nombre: user.name, username: user.username },
        }));
    };

    window.closeUserDetailsModalFast = () => {
        const modal = document.getElementById('user-details-modal');
        const backdrop = document.getElementById('user-details-backdrop');
        const panel = document.getElementById('user-details-panel');

        if (!modal || modal.classList.contains('hidden')) return;

        const reduceMotion = window.matchMedia?.(
            '(prefers-reduced-motion: reduce)'
        )?.matches ?? false;
        const duration = reduceMotion ? 0 : 160;

        if (backdrop) backdrop.style.opacity = '0';

        if (panel) {
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(8px) scale(0.985)';
        }

        modal.setAttribute('aria-hidden', 'true');

        if (window.__userDetailsCloseTimer) {
            clearTimeout(window.__userDetailsCloseTimer);
        }

        window.__userDetailsCloseTimer = window.setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            window.__userDetailsCloseTimer = null;
        }, duration);
    };

    if (!window.__userDetailsListenersInstalled) {
        window.__userDetailsListenersInstalled = true;

        window.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            const modal = document.getElementById('user-details-modal');
            if (modal && !modal.classList.contains('hidden')) {
                window.closeUserDetailsModalFast();
            }
        });
    }
})();
</script>
@endscript