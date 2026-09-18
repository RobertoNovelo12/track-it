{{-- ============================================================
MODAL: APROBAR USUARIO

============================================================ --}}
@if ($approvalModalOpen)

<div
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
    wire:click.self="closeApprovalModal"
>

    {{-- Fondo --}}
    <div
        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "
        wire:click="closeApprovalModal"
    ></div>


    {{-- Modal --}}
    <div
        class="
            relative
            z-10

            w-full
            max-w-md

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            theme-shadow-xl

            overflow-hidden
        "
    >

        {{-- Encabezado --}}
        <div
            class="
                px-5
                py-4

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
                    Aprobar usuario
                </h3>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Asigna un rol antes de activar la cuenta.
                </p>

            </div>


            <button
                type="button"
                wire:click="closeApprovalModal"
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


        <form wire:submit="approveUser">

            <div class="p-5 space-y-5">

                {{-- Usuario --}}
                <div
                    class="
                        p-4

                        rounded-lg

                        bg-[var(--theme-surface-soft)]

                        border
                        border-[var(--theme-border)]
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            gap-3
                        "
                    >

                        <div
                            class="
                                w-10
                                h-10
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-primary-soft)]
                                text-[var(--theme-primary)]

                                font-semibold
                                text-sm
                            "
                        >
                            @if ($approvalUserName !== '')
                                {{ mb_strtoupper(mb_substr($approvalUserName, 0, 1)) }}
                            @else
                                U
                            @endif
                        </div>


                        <div class="min-w-0">

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-[var(--theme-text-strong)]

                                    truncate
                                "
                            >
                                {{ $approvalUserName }}
                            </p>

                            <p
                                class="
                                    mt-0.5

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    truncate
                                "
                            >
                                {{ $approvalUserEmail }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Rol --}}
                <div>

                    <label
                        for="approvalRoleId"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Rol del usuario

                        <span class="text-[var(--theme-danger)]">
                            *
                        </span>
                    </label>


                    <select
                        id="approvalRoleId"
                        wire:model="approvalRoleId"
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

                        <option value="">
                            Selecciona un rol
                        </option>

                        @foreach ($roles as $rol)

                            <option value="{{ $rol->id_rol }}">
                                {{ $rol->nombre }}
                            </option>

                        @endforeach

                    </select>


                    @error('approvalRoleId')

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


                    <p
                        class="
                            mt-2
                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        El usuario quedará activo inmediatamente después
                        de confirmar la aprobación.
                    </p>

                </div>

            </div>


            {{-- Acciones --}}
                <div
                    class="
                        px-5
                        py-4

                        border-t
                        border-[var(--theme-border)]

                        flex
                        items-center
                        justify-end
                        gap-2

                        bg-[var(--theme-surface-soft)]
                    "
                >

                    <button
                        type="button"
                        wire:click="closeApprovalModal"
                        wire:loading.attr="disabled"
                        wire:target="approveUser"
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
                        wire:target="approveUser"
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
                            wire:target="approveUser"
                        >
                            Aprobar usuario
                        </span>


                        <span
                            wire:loading
                            wire:target="approveUser"
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

                            Aprobando...

                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

@endif
