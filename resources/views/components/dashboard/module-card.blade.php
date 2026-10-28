@props([
    'title',
    'description',
    'action',
    'route',
    'icon' => null,
])

<div
    class="
        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border)]

        rounded-lg

        px-6
        py-6

        min-h-44

        flex
        flex-col

        hover:border-[var(--theme-primary-border)]
        hover:shadow-[0_4px_12px_var(--theme-shadow)]

        transition-[border-color,box-shadow]
        duration-200
    "
>

    {{-- ============================================================
        CABECERA
    ============================================================ --}}
    <div
        class="
            flex
            items-start
            justify-between

            gap-4
        "
    >

        <h3
            class="
                text-base
                font-semibold
                text-[var(--theme-text-strong)]
            "
        >
            {{ $title }}
        </h3>


        {{-- Icono --}}
        <div
            class="
                shrink-0
                text-[var(--theme-text-muted)]
            "
        >

            @switch($icon)

                @case('laptop')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-bbbb43a') }}"></use></svg>

                    @break


                @case('swap')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-e5c8e79') }}"></use></svg>

                    @break


                @case('tools')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-eb20d2d') }}"></use></svg>

                    @break


                @case('report')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-3964335') }}"></use></svg>

                    @break


                @case('users')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-339a7ec') }}"></use></svg>

                    @break


                @case('list')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    ><use href="{{ \App\Support\Icono::url('i-e645fe3') }}"></use></svg>

                    @break

            @endswitch

        </div>

    </div>


    {{-- ============================================================
        DESCRIPCIÓN
    ============================================================ --}}
    <p
        class="
            mt-4

            flex-1

            text-xs
            leading-relaxed

            text-[var(--theme-text-muted)]
        "
    >
        {{ $description }}
    </p>


    {{-- ============================================================
        ACCIÓN
    ============================================================ --}}
    <div
        class="
            mt-5
            pt-4

            border-t
            border-[var(--theme-border)]
        "
    >

        <a
            href="{{ Route::has($route) ? route($route) : '#' }}"
            class="
                flex
                items-center
                justify-between

                text-xs
                font-medium

                text-[var(--theme-primary)]

                hover:text-[var(--theme-primary-hover)]

                transition-colors
            "
        >

            <span>
                {{ $action }}
            </span>


            <svg
                class="
                    w-4
                    h-4
                    shrink-0
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            ><use href="{{ \App\Support\Icono::url('i-21d8a82') }}"></use></svg>

        </a>

    </div>

</div>