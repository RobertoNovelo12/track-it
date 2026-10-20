{{-- ============================================================
    MODAL: REVISAR SOLICITUD ORGANIZACIONAL

    Apertura/cierre instantáneos en navegador.
    Aprobar y rechazar siguen usando Livewire.
============================================================ --}}

<input id="org-request-model-open" type="checkbox" wire:model="organizationRequestModalOpen" class="hidden">
<input id="org-request-model-id" type="hidden" wire:model="organizationRequestId">
<input id="org-request-model-user" type="hidden" wire:model="organizationRequestUserName">
<input id="org-request-model-field" type="hidden" wire:model="organizationRequestField">
<input id="org-request-model-label" type="hidden" wire:model="organizationRequestFieldLabel">
<input id="org-request-model-current" type="hidden" wire:model="organizationRequestCurrentValue">
<input id="org-request-model-requested" type="hidden" wire:model="organizationRequestRequestedValue">
<input id="org-request-model-reason" type="hidden" wire:model="organizationRequestReason">
<input id="org-request-model-catalog" type="hidden" wire:model="organizationRequestCatalogId">

<div
    id="organization-request-modal"
    aria-hidden="{{ $organizationRequestModalOpen ? 'false' : 'true' }}"
    class="
        {{ $organizationRequestModalOpen ? '' : 'hidden' }}
        fixed inset-0 z-[100]
        flex items-center justify-center
        px-4 py-6
    "
    onclick="if (event.target === this) window.closeOrganizationRequestModalFast()"
>
    <div
        id="organization-request-backdrop"
        class="absolute inset-0 bg-[var(--theme-overlay)] backdrop-blur-[2px]"
        style="
            opacity: 0;
            transition: opacity 180ms ease-out;
        "
        onclick="window.closeOrganizationRequestModalFast()"
    ></div>

    <div
        id="organization-request-panel"
        class="
            relative z-10 w-full max-w-lg max-h-[90vh] overflow-y-auto
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
        aria-labelledby="organization-request-modal-title"
    >
        <div
            class="
                sticky top-0 z-10 px-5 py-4
                bg-[var(--theme-surface)]
                border-b border-[var(--theme-border)]
                flex items-start justify-between gap-4
            "
        >
            <div>
                <h3
                    id="organization-request-modal-title"
                    class="text-base font-semibold text-[var(--theme-text-strong)]"
                >
                    Revisar solicitud
                </h3>

                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Verifica la información antes de aprobar o rechazar el cambio.
                </p>
            </div>

            <button
                id="organization-request-close-button"
                type="button"
                onclick="window.closeOrganizationRequestModalFast()"
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
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <div class="p-5 space-y-5">
            <div
                class="
                    p-4 rounded-lg bg-[var(--theme-surface-soft)]
                    border border-[var(--theme-border)]
                "
            >
                <div class="flex items-center gap-3">
                    <div
                        id="organization-request-user-initial"
                        class="
                            w-10 h-10 shrink-0 rounded-full
                            flex items-center justify-center
                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]
                            text-sm font-semibold
                        "
                    >
                        {{ $organizationRequestUserName !== ''
                            ? mb_strtoupper(mb_substr($organizationRequestUserName, 0, 1))
                            : 'U'
                        }}
                    </div>

                    <div class="min-w-0">
                        <p
                            id="organization-request-user-name"
                            class="text-sm font-medium text-[var(--theme-text-strong)]"
                        >
                            {{ $organizationRequestUserName }}
                        </p>

                        <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                            Solicitud #<span id="organization-request-id-text">{{ $organizationRequestId }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <p
                    id="organization-request-field-label"
                    class="mb-2 text-xs font-medium text-[var(--theme-text)]"
                >
                    {{ $organizationRequestFieldLabel }}
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div
                        class="
                            p-3 rounded-lg bg-[var(--theme-surface-soft)]
                            border border-[var(--theme-border)]
                        "
                    >
                        <p class="text-[10px] uppercase tracking-wide text-[var(--theme-text-muted)]">
                            Valor actual
                        </p>

                        <p
                            id="organization-request-current-value"
                            class="mt-1.5 text-sm text-[var(--theme-text)]"
                        >
                            {{ $organizationRequestCurrentValue ?: 'Sin asignar' }}
                        </p>
                    </div>

                    <div
                        class="
                            p-3 rounded-lg bg-[var(--theme-primary-soft-subtle)]
                            border border-[var(--theme-primary-border)]
                        "
                    >
                        <p class="text-[10px] uppercase tracking-wide text-[var(--theme-primary)]">
                            Valor solicitado
                        </p>

                        <p
                            id="organization-request-requested-value"
                            class="mt-1.5 text-sm font-medium text-[var(--theme-text-strong)]"
                        >
                            {{ $organizationRequestRequestedValue ?: 'Sin especificar' }}
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <p class="mb-1.5 text-xs font-medium text-[var(--theme-text)]">
                    Motivo de la solicitud
                </p>

                <div
                    id="organization-request-reason"
                    class="
                        min-h-10 px-3 py-2.5 rounded-md
                        bg-[var(--theme-surface-soft)]
                        border border-[var(--theme-border)]
                        text-xs leading-relaxed text-[var(--theme-text-muted)]
                    "
                >
                    {{ $organizationRequestReason ?: 'El usuario no agregó comentarios.' }}
                </div>
            </div>

            <div
                id="organization-request-catalog-role"
                class="{{ $organizationRequestField === 'rol' ? '' : 'hidden' }}"
            >
                <label
                    for="organization-request-role-select"
                    class="block mb-1.5 text-xs font-medium text-[var(--theme-text)]"
                >
                    Rol que se asignará
                    <span class="text-[var(--theme-danger)]">*</span>
                </label>

                {{-- Lista con buscador (x-searchable-select). El valor se envía a
                     organizationRequestCatalogId con syncOrganizationRequestCatalog(). --}}
                <div
                    id="organization-request-role-select"
                    data-options="{{ json_encode(collect($roles)->map(fn ($rol) => ['value' => (string) $rol->id_rol, 'label' => $rol->nombre])->values()->all()) }}"
                    x-data="{ valor: '' }"
                    x-init="$watch('valor', v => window.syncOrganizationRequestCatalog(v ?? ''))"
                    @org-request-catalog.window="if ($event.detail.id === $el.id) valor = $event.detail.value"
                >
                    <x-searchable-select
                        x-model="valor"
                        :options="collect($roles)->map(fn ($rol) => ['value' => (string) $rol->id_rol, 'label' => $rol->nombre])->values()->all()"
                        placeholder="Selecciona un rol"
                        :show-clear="true"
                        clear-label="Selecciona un rol"
                        :empty-as-null="false"
                    />
                </div>
            </div>

            <div
                id="organization-request-catalog-area"
                class="{{ $organizationRequestField === 'area' ? '' : 'hidden' }}"
            >
                <label
                    for="organization-request-area-select"
                    class="block mb-1.5 text-xs font-medium text-[var(--theme-text)]"
                >
                    Área que se asignará
                    <span class="text-[var(--theme-danger)]">*</span>
                </label>

                {{-- Lista con buscador (x-searchable-select). El valor se envía a
                     organizationRequestCatalogId con syncOrganizationRequestCatalog(). --}}
                <div
                    id="organization-request-area-select"
                    data-options="{{ json_encode(collect($areas)->map(fn ($area) => ['value' => (string) $area->id_area, 'label' => $area->nombre])->values()->all()) }}"
                    x-data="{ valor: '' }"
                    x-init="$watch('valor', v => window.syncOrganizationRequestCatalog(v ?? ''))"
                    @org-request-catalog.window="if ($event.detail.id === $el.id) valor = $event.detail.value"
                >
                    <x-searchable-select
                        x-model="valor"
                        :options="collect($areas)->map(fn ($area) => ['value' => (string) $area->id_area, 'label' => $area->nombre])->values()->all()"
                        placeholder="Selecciona un área"
                        :show-clear="true"
                        clear-label="Selecciona un área"
                        :empty-as-null="false"
                    />
                </div>
            </div>

            <div
                id="organization-request-catalog-department"
                class="{{ $organizationRequestField === 'departamento' ? '' : 'hidden' }}"
            >
                <label
                    for="organization-request-department-select"
                    class="block mb-1.5 text-xs font-medium text-[var(--theme-text)]"
                >
                    Departamento que se asignará
                    <span class="text-[var(--theme-danger)]">*</span>
                </label>

                {{-- Lista con buscador (x-searchable-select). El valor se envía a
                     organizationRequestCatalogId con syncOrganizationRequestCatalog(). --}}
                <div
                    id="organization-request-department-select"
                    data-options="{{ json_encode(collect($departamentos)->map(fn ($departamento) => ['value' => (string) $departamento->id_departamento, 'label' => $departamento->nombre])->values()->all()) }}"
                    x-data="{ valor: '' }"
                    x-init="$watch('valor', v => window.syncOrganizationRequestCatalog(v ?? ''))"
                    @org-request-catalog.window="if ($event.detail.id === $el.id) valor = $event.detail.value"
                >
                    <x-searchable-select
                        x-model="valor"
                        :options="collect($departamentos)->map(fn ($departamento) => ['value' => (string) $departamento->id_departamento, 'label' => $departamento->nombre])->values()->all()"
                        placeholder="Selecciona un departamento"
                        :show-clear="true"
                        clear-label="Selecciona un departamento"
                        :empty-as-null="false"
                    />
                </div>
            </div>

            <div>
                <label
                    for="organizationRequestReviewComment"
                    class="block mb-1.5 text-xs font-medium text-[var(--theme-text)]"
                >
                    Comentario de revisión
                    <span class="font-normal text-[var(--theme-text-muted)]">
                        (obligatorio al rechazar)
                    </span>
                </label>

                <textarea
                    id="organizationRequestReviewComment"
                    wire:model="organizationRequestReviewComment"
                    rows="3"
                    placeholder="Agrega una observación sobre la decisión."
                    class="
                        w-full px-3 py-2.5 rounded-md resize-none
                        bg-[var(--theme-surface)]
                        border border-[var(--theme-border-strong)]
                        text-sm text-[var(--theme-text)]
                        placeholder:text-[var(--theme-text-muted)]
                        focus:ring-1 focus:ring-[var(--theme-primary)]
                        focus:border-[var(--theme-primary)]
                    "
                ></textarea>

                @error('organizationRequestReviewComment')
                    <p
                        id="organization-request-comment-error"
                        class="mt-1.5 text-xs text-[var(--theme-danger)]"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            @error('organizationRequestDecision')
                <div
                    id="organization-request-decision-error"
                    class="
                        p-3 rounded-lg
                        bg-[var(--theme-danger-soft)]
                        border border-[var(--theme-danger)]
                        text-xs text-[var(--theme-danger)]
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div
            class="
                sticky bottom-0 px-5 py-4
                bg-[var(--theme-surface-soft)]
                border-t border-[var(--theme-border)]
                flex flex-col-reverse sm:flex-row
                sm:items-center sm:justify-between gap-2
            "
        >
            <x-ui.button
                wire:click="rejectOrganizationRequest"
                target="rejectOrganizationRequest"
                disable-target="rejectOrganizationRequest,approveOrganizationRequest"
                variant="danger-soft"
                size="compact"
                loading-text="Rechazando…"
            >
                Rechazar
            </x-ui.button>

            <div class="flex items-center justify-end gap-2">
                <button
                    type="button"
                    onclick="window.closeOrganizationRequestModalFast()"
                    wire:loading.attr="disabled"
                    wire:target="rejectOrganizationRequest,approveOrganizationRequest"
                    class="
                        h-9 px-4 rounded-md
                        border border-[var(--theme-border-strong)]
                        bg-[var(--theme-surface)]
                        text-xs font-medium text-[var(--theme-text)]
                        hover:bg-[var(--theme-surface-soft)]
                        disabled:opacity-50 transition-colors
                    "
                >
                    Cancelar
                </button>

                <x-ui.button
                    wire:click="approveOrganizationRequest"
                    target="approveOrganizationRequest"
                    disable-target="approveOrganizationRequest,rejectOrganizationRequest"
                    size="compact"
                    loading-text="Aprobando…"
                >
                    Aprobar solicitud
                </x-ui.button>
            </div>
        </div>
    </div>
</div>

@script
<script>
(() => {
    const normalizeText = (value) =>
        String(value ?? '').trim().toLocaleLowerCase('es');

    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    };

    const setModelValue = (id, value) => {
        const input = document.getElementById(id);
        if (!input) return;

        input.value = value ?? '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const setModalOpenModel = (open) => {
        const checkbox = document.getElementById('org-request-model-open');
        if (!checkbox) return;

        checkbox.checked = Boolean(open);
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const hideValidationErrors = () => {
        document.getElementById('organization-request-comment-error')?.classList.add('hidden');
        document.getElementById('organization-request-decision-error')?.classList.add('hidden');
    };

    const hideCatalogs = () => {
        document.getElementById('organization-request-catalog-role')?.classList.add('hidden');
        document.getElementById('organization-request-catalog-area')?.classList.add('hidden');
        document.getElementById('organization-request-catalog-department')?.classList.add('hidden');
    };

    const selectRequestedCatalogValue = (selectId, requestedValue) => {
        const wrapper = document.getElementById(selectId);
        if (!wrapper) return '';

        let options = [];

        try {
            options = JSON.parse(wrapper.dataset.options ?? '[]');
        } catch (error) {
            options = [];
        }

        const requested = normalizeText(requestedValue);

        const match = requested === ''
            ? null
            : options.find((option) =>
                String(option.value) !== '' && normalizeText(option.label) === requested
            );

        const value = match ? String(match.value) : '';

        // Actualiza el valor mostrado en la lista con buscador.
        window.dispatchEvent(new CustomEvent('org-request-catalog', {
            detail: { id: selectId, value },
        }));

        return value;
    };

    window.syncOrganizationRequestCatalog = (value) => {
        setModelValue('org-request-model-catalog', value ?? '');
    };

    window.openOrganizationRequestModalFromButton = (button) => {
        const modal = document.getElementById('organization-request-modal');
        if (!modal || !button) return;

        const requestId = button.dataset.orgRequestId ?? '';
        const userName = button.dataset.orgUser ?? '';
        const field = button.dataset.orgField ?? '';
        const fieldLabel = button.dataset.orgLabel ?? '';
        const currentValue = button.dataset.orgCurrent ?? '';
        const requestedValue = button.dataset.orgRequested ?? '';
        const reason = button.dataset.orgReason ?? '';

        setModalOpenModel(true);
        setModelValue('org-request-model-id', requestId);
        setModelValue('org-request-model-user', userName);
        setModelValue('org-request-model-field', field);
        setModelValue('org-request-model-label', fieldLabel);
        setModelValue('org-request-model-current', currentValue);
        setModelValue('org-request-model-requested', requestedValue);
        setModelValue('org-request-model-reason', reason);
        setModelValue('org-request-model-catalog', '');

        const reviewComment = document.getElementById('organizationRequestReviewComment');
        if (reviewComment) {
            reviewComment.value = '';
            reviewComment.dispatchEvent(new Event('input', { bubbles: true }));
        }

        hideValidationErrors();

        setText(
            'organization-request-user-initial',
            userName.trim().charAt(0).toUpperCase() || 'U'
        );
        setText('organization-request-user-name', userName);
        setText('organization-request-id-text', requestId);
        setText('organization-request-field-label', fieldLabel);
        setText('organization-request-current-value', currentValue || 'Sin asignar');
        setText('organization-request-requested-value', requestedValue || 'Sin especificar');
        setText(
            'organization-request-reason',
            reason || 'El usuario no agregó comentarios.'
        );

        hideCatalogs();
        let catalogId = '';

        if (field === 'rol') {
            document.getElementById('organization-request-catalog-role')?.classList.remove('hidden');
            catalogId = selectRequestedCatalogValue(
                'organization-request-role-select',
                requestedValue
            );
        } else if (field === 'area') {
            document.getElementById('organization-request-catalog-area')?.classList.remove('hidden');
            catalogId = selectRequestedCatalogValue(
                'organization-request-area-select',
                requestedValue
            );
        } else if (field === 'departamento') {
            document.getElementById('organization-request-catalog-department')?.classList.remove('hidden');
            catalogId = selectRequestedCatalogValue(
                'organization-request-department-select',
                requestedValue
            );
        }

        window.syncOrganizationRequestCatalog(catalogId);

        const backdrop = document.getElementById('organization-request-backdrop');
        const panel = document.getElementById('organization-request-panel');
        const reduceMotion = window.matchMedia?.(
            '(prefers-reduced-motion: reduce)'
        )?.matches ?? false;

        if (window.__organizationRequestCloseTimer) {
            clearTimeout(window.__organizationRequestCloseTimer);
            window.__organizationRequestCloseTimer = null;
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
                if (backdrop) {
                    backdrop.style.opacity = '1';
                }

                if (panel) {
                    panel.style.opacity = '1';
                    panel.style.transform = 'translateY(0) scale(1)';
                }

                document
                    .getElementById('organization-request-close-button')
                    ?.focus({ preventScroll: true });
            });
        });
    };

    window.closeOrganizationRequestModalFast = () => {
        const modal = document.getElementById('organization-request-modal');
        const backdrop = document.getElementById('organization-request-backdrop');
        const panel = document.getElementById('organization-request-panel');

        if (!modal || modal.classList.contains('hidden')) {
            return;
        }

        const reduceMotion = window.matchMedia?.(
            '(prefers-reduced-motion: reduce)'
        )?.matches ?? false;

        const duration = reduceMotion ? 0 : 160;

        if (backdrop) {
            backdrop.style.opacity = '0';
        }

        if (panel) {
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(8px) scale(0.985)';
        }

        modal.setAttribute('aria-hidden', 'true');

        if (window.__organizationRequestCloseTimer) {
            clearTimeout(window.__organizationRequestCloseTimer);
        }

        window.__organizationRequestCloseTimer = window.setTimeout(() => {
            modal.classList.add('hidden');
            setModalOpenModel(false);
            document.body.style.overflow = '';
            window.__organizationRequestCloseTimer = null;
        }, duration);
    };

    if (!window.__organizationRequestModalListenersInstalled) {
        window.__organizationRequestModalListenersInstalled = true;

        window.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            const modal = document.getElementById('organization-request-modal');
            if (modal && !modal.classList.contains('hidden')) {
                window.closeOrganizationRequestModalFast();
            }
        });

        window.addEventListener(
            'solicitud-organizacion-aprobada',
            () => window.closeOrganizationRequestModalFast()
        );

        window.addEventListener(
            'solicitud-organizacion-rechazada',
            () => window.closeOrganizationRequestModalFast()
        );
    }
})();
</script>
@endscript