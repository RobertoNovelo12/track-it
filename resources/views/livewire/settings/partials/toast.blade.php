{{-- ============================================================
    TOAST
============================================================ --}}
<div
    x-data="{
        visible: false,
        title: '',
        message: '',
        timeout: null,

        show(title, message) {
            this.title = title;
            this.message = message;
            this.visible = true;

            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            this.timeout = setTimeout(() => {
                this.visible = false;
            }, 3000);
        }
    }"

    @perfil-actualizado.window="
        show(
            'Perfil actualizado',
            $event.detail.message
                ?? 'Tu información se actualizó correctamente.'
        )
    "

    @password-updated.window="
        show(
            'Contraseña actualizada',
            $event.detail.message
                ?? 'Tu contraseña se actualizó correctamente.'
        )
    "

    @two-factor-enabled.window="
        show(
            '2FA activado',
            $event.detail.message
                ?? 'La autenticación en dos pasos se activó correctamente.'
        )
    "

    @two-factor-recovery-codes-regenerated.window="
    show(
        'Códigos regenerados',
        $event.detail.message
            ?? 'Se generaron nuevos códigos de recuperación.'
    )
    "

    @two-factor-disabled.window="
        show(
            '2FA desactivado',
            $event.detail.message
                ?? 'La autenticación en dos pasos se desactivó correctamente.'
        )
    "

    x-show="visible"
    x-cloak

    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"

    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"

    class="
        fixed
        top-5
        right-4
        sm:right-6

        z-[110]

        w-[calc(100%_-_2rem)]
        max-w-sm

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-success-border)]

        rounded-xl

        theme-shadow-xl

        px-4
        py-3

        flex
        items-start
        gap-3
    "
>
    {{-- Icono --}}
    <div
        class="
            shrink-0

            w-8
            h-8

            rounded-full

            flex
            items-center
            justify-center

            bg-[var(--theme-success-soft)]
            text-[var(--theme-success)]
        "
    >
        <svg
            class="w-4 h-4"

            viewBox="0 0 24 24"

            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M5 12l4 4L19 6"/>
        </svg>
    </div>


    {{-- Contenido --}}
    <div class="min-w-0">

        <p
            class="
                text-sm
                font-semibold
                text-[var(--theme-text-strong)]
            "

            x-text="title"
        ></p>


        <p
            class="
                mt-0.5

                text-xs
                text-[var(--theme-text-muted)]
            "

            x-text="message"
        ></p>

    </div>
</div>