{{-- ============================================================
    MODAL: EDITAR PERFIL
============================================================ --}}
<div
    x-data="{ open: false }"
    @abrir-edicion-perfil.window="open = true"
    @perfil-actualizado.window="open = false"
    x-show="open"
    x-cloak

    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"

    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"

    @keydown.escape.window="
        if (open) {
            open = false;
            $wire.closeEditProfileModal();
        }
    "

    class="
        fixed
        inset-0
        z-[100]

        flex
        items-center
        justify-center

        px-4
        py-6
    "
>

    {{-- Fondo --}}
    <div
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"

        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"

        @click="
            open = false;
            $wire.closeEditProfileModal();
        "

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "
    ></div>


    {{-- Modal --}}
    <div
        class="
            relative
            z-10

            w-full
            max-w-lg

            max-h-[90vh]
            overflow-y-auto

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            theme-shadow-xl
        "
    >

        {{-- Cabecera --}}
        <div
            class="
                sticky
                top-0
                z-10

                px-5
                py-4

                bg-[var(--theme-surface)]

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                justify-between
                gap-4
            "
        >

            <div>

                <h3
                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Editar información personal
                </h3>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Actualiza los datos personales de tu cuenta.
                </p>

            </div>


            <button
                type="button"
                @click="
                        open = false;
                        $wire.closeEditProfileModal();
                    "
                class="
                    w-8
                    h-8
                    shrink-0

                    rounded-md

                    flex
                    items-center
                    justify-center

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


        <form wire:submit="updateProfile">

            <div class="p-5 space-y-4">

                {{-- Nombre --}}
                <div>

                    <label
                        for="editNombres"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Nombre

                        <span class="text-[var(--theme-danger)]">*</span>
                    </label>

                    <input
                        id="editNombres"
                        type="text"
                        wire:model="editNombres"
                        autocomplete="given-name"
                        class="
                            w-full
                            h-10

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >

                    @error('editNombres')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Apellido paterno --}}
                <div>

                    <label
                        for="editApellidoPaterno"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Apellido paterno

                        <span class="text-[var(--theme-danger)]">*</span>
                    </label>

                    <input
                        id="editApellidoPaterno"
                        type="text"
                        wire:model="editApellidoPaterno"
                        autocomplete="family-name"
                        class="
                            w-full
                            h-10

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >

                    @error('editApellidoPaterno')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Apellido materno --}}
                <div>

                    <label
                        for="editApellidoMaterno"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Apellido materno

                        <span
                            class="
                                font-normal
                                text-[var(--theme-text-muted)]
                            "
                        >
                            (opcional)
                        </span>
                    </label>

                    <input
                        id="editApellidoMaterno"
                        type="text"
                        wire:model="editApellidoMaterno"
                        class="
                            w-full
                            h-10

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >

                    @error('editApellidoMaterno')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Teléfono --}}
                <div>

                    <label
                        for="editTelefono"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Teléfono

                        <span
                            class="
                                font-normal
                                text-[var(--theme-text-muted)]
                            "
                        >
                            (opcional)
                        </span>
                    </label>

                    <input
                        id="editTelefono"
                        type="tel"
                        wire:model="editTelefono"
                        autocomplete="tel"
                        placeholder="Ej. 998 123 4567"
                        class="
                            w-full
                            h-10

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >

                    @error('editTelefono')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Información no editable --}}
                <div
                    class="
                        p-3

                        rounded-lg

                        bg-[var(--theme-primary-soft-subtle)]

                        border
                        border-[var(--theme-primary-border)]

                        flex
                        items-start
                        gap-2.5
                    "
                >

                    <svg
                        class="
                            w-4
                            h-4
                            shrink-0
                            mt-0.5

                            text-[var(--theme-primary)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v6"/>
                        <path d="M12 7h.01"/>
                    </svg>


                    <p
                        class="
                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        El correo, usuario, puesto, rol, área y departamento
                        deben ser gestionados por un administrador.
                    </p>

                </div>

            </div>


            {{-- Acciones --}}
            <div
                class="
                    sticky
                    bottom-0

                    px-5
                    py-4

                    bg-[var(--theme-surface-soft)]

                    border-t
                    border-[var(--theme-border)]

                    flex
                    items-center
                    justify-end
                    gap-2
                "
            >

                <button
                    type="button"
                    @click="
                        open = false;
                        $wire.closeEditProfileModal();
                    "
                    wire:loading.attr="disabled"
                    wire:target="updateProfile"
                    class="
                        h-9
                        px-4

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-xs
                        font-medium
                        text-[var(--theme-text)]

                        hover:bg-[var(--theme-surface-soft)]

                        disabled:opacity-50

                        transition-colors
                    "
                >
                    Cancelar
                </button>


                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="updateProfile"
                    class="
                        h-9
                        px-4

                        rounded-md

                        bg-[var(--theme-primary)]
                        text-white

                        text-xs
                        font-medium

                        hover:bg-[var(--theme-primary-hover)]

                        disabled:opacity-60
                        disabled:cursor-not-allowed

                        transition-colors
                    "
                >

                    <span
                        wire:loading.remove
                        wire:target="updateProfile"
                    >
                        Guardar cambios
                    </span>


                    <span
                        wire:loading
                        wire:target="updateProfile"
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >
                        <svg
                            class="
                                w-4
                                h-4
                                animate-spin
                            "
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            />
                        </svg>

                        Guardando...
                    </span>

                </button>

            </div>

        </form>

    </div>

</div>
