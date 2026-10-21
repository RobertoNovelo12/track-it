<div>

    {{-- ============================================================
        INDICADORES
    ============================================================ --}}
    @include('livewire.security.partials.indicators')


    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-[minmax(0,1fr)_320px]

            gap-5
        "
    >

        {{-- Columna izquierda --}}
        <div class="space-y-5">

            {{-- Usuarios que esperan una contraseña temporal --}}
            @include('livewire.security.partials.restablecimientos')

            @include('livewire.security.partials.users')

            {{-- Equipos asignados a personas sin cuenta --}}
            @include('livewire.security.partials.colaboradores-sin-cuenta')

            @include('livewire.security.partials.requests')

            @include('livewire.security.partials.audit')

        </div>


        {{-- Columna derecha --}}
        <aside class="space-y-5">

            @include('livewire.security.partials.weekly-summary')

            @include('livewire.security.partials.alerts')

        </aside>

    </div>


    {{-- ============================================================
        MODALES
    ============================================================ --}}

    {{-- Aprobar usuario --}}
    @include('livewire.security.modals.approve-user')


    {{-- Ver detalles del usuario --}}
    @include('livewire.security.modals.user-details')


    {{-- Solicitudes de cambio organizacional --}}
    @include('livewire.security.modals.organization-request')


    {{-- Restablecer contraseña (contraseña temporal) --}}
    @include('livewire.security.modals.reset-password')

</div>