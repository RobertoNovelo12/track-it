{{-- ============================================================
    ENCABEZADO DEL CONSTRUCTOR
============================================================ --}}
<div class="mb-4 max-w-lg">
    <label for="clasificacion-tipo-bien" class="mb-1.5 block text-sm">Clasificación del bien *</label>
    <select id="clasificacion-tipo-bien" x-model="tipo.clasificacion_bien" x-on:change="hasChanges = true" class="w-full rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] px-3 py-2 text-sm">
        @foreach (\App\Services\ClasificacionBienService::OPCIONES as $clave => $nombre)
            <option value="{{ $clave }}">{{ $nombre }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Se aplica a todos los bienes de este tipo. Al guardar se agregan los campos de su reporte.</p>
</div>
<div
    class="
        flex
        flex-col

        lg:flex-row
        lg:items-end
        lg:justify-between

        gap-4
    "
>

    {{-- ========================================================
        INFORMACIÓN DEL TIPO
    ======================================================== --}}
    <div
        class="
            min-w-0
        "
    >
        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2
            "
        >

            <h2
                class="
                    text-lg
                    font-semibold
                    text-[var(--theme-text-strong)]
                "

                x-text="
                    tipo.nombre
                    ?? ''
                "
            ></h2>


            {{-- ESTADO --}}
            <span
                x-show="
                    tipo.activo
                "

                x-cloak

                class="
                    inline-flex
                    items-center

                    px-2
                    py-0.5

                    rounded-full

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]

                    text-[9px]
                    font-semibold
                    uppercase
                    tracking-wide
                "
            >
                Activo
            </span>


            <span
                x-show="
                    ! tipo.activo
                "

                x-cloak

                class="
                    inline-flex
                    items-center

                    px-2
                    py-0.5

                    rounded-full

                    bg-[var(--theme-surface-soft)]
                    text-[var(--theme-text-muted)]

                    text-[9px]
                    font-semibold
                    uppercase
                    tracking-wide
                "
            >
                Inactivo
            </span>

        </div>


        <p
            class="
                mt-1

                text-[10px]
                text-[var(--theme-text-muted)]
            "
        >
            Los cambios afectan únicamente a

            <strong
                class="
                    font-semibold
                    text-[var(--theme-text)]
                "

                x-text="
                    tipo.nombre
                    ?? ''
                "
            ></strong>.
        </p>
    </div>


    {{-- ========================================================
        ACCIONES
    ======================================================== --}}
    <div
        class="
            flex
            flex-col

            sm:flex-row
            sm:items-center

            gap-2
        "
    >

        {{-- RESTABLECER --}}
        <button
            type="button"

            @click="
                resetChanges()
            "

            :disabled="
                ! hasChanges
                || saving
            "

            class="
                h-9

                px-4

                inline-flex
                items-center
                justify-center

                rounded-md

                border
                border-[var(--theme-border)]

                bg-[var(--theme-surface)]

                text-xs
                font-medium
                text-[var(--theme-text)]

                hover:bg-[var(--theme-surface-soft)]

                disabled:opacity-40
                disabled:cursor-not-allowed

                transition-colors
            "
        >
            Restablecer
        </button>


        {{-- GUARDAR --}}
        <button
            type="button"

            @click="
                saveConfiguration()
            "

            :disabled="
                ! hasChanges
                || saving
            "

            class="
                min-w-[175px]
                h-9

                px-4

                inline-flex
                items-center
                justify-center
                gap-2

                rounded-md

                bg-[var(--theme-primary)]
                text-white

                text-xs
                font-medium

                hover:bg-[var(--theme-primary-hover)]

                disabled:opacity-50
                disabled:cursor-not-allowed

                transition-colors
            "
        >

            {{-- SPINNER --}}
            <svg
                x-show="
                    saving
                "

                x-cloak

                class="
                    w-4
                    h-4

                    animate-spin
                "

                viewBox="0 0 24 24"
                fill="none"
            ><use href="{{ \App\Support\Icono::url('i-607ce5b') }}"></use></svg>


            <span
                x-show="
                    ! saving
                "
            >
                Guardar configuración
            </span>


            <span
                x-show="
                    saving
                "

                x-cloak
            >
                Guardando...
            </span>

        </button>

    </div>

</div>