<div>

    {{-- ============================================================
        TOAST GLOBAL
    ============================================================ --}}
    @include('livewire.settings.partials.toast')


    {{-- ============================================================
        NAVEGACIÓN
    ============================================================ --}}
    @include('livewire.settings.partials.navigation')


    {{-- ============================================================
        PLACEHOLDER → CUENTA
    ============================================================ --}}
    <div
        wire:loading.delay.shortest
        wire:target="setSection('cuenta')"

        class="w-full"
    >
        @include('livewire.placeholders.ajustes-cuenta')
    </div>


    {{-- ============================================================
        PLACEHOLDER → SEGURIDAD
    ============================================================ --}}
    <div
        wire:loading.delay.shortest
        wire:target="setSection('seguridad')"

        class="w-full"
    >
        @include('livewire.placeholders.ajustes-seguridad')
    </div>


    {{-- ============================================================
        CONTENIDO DE LA SECCIÓN
    ============================================================ --}}
    <div
        wire:loading.remove
        wire:target="setSection"
    >
        @if ($section === 'cuenta')

            @include('livewire.settings.account')

        @elseif ($section === 'seguridad')

            @include('livewire.settings.security')

        @elseif ($section === 'notificaciones')

            @include('livewire.settings.notifications')

        @elseif ($section === 'preferencias')

            @include('livewire.settings.preferences')

        @endif
    </div>


    {{-- ============================================================
        MODALES
    ============================================================ --}}
    @include('livewire.settings.modals.edit-profile')

    @include('livewire.settings.modals.request-organization-change')

    @include('livewire.settings.modals.change-password')

    @include('livewire.settings.modals.two-factor')

    @include('livewire.settings.modals.manage-two-factor')

    @include('livewire.settings.modals.active-sessions')

</div>