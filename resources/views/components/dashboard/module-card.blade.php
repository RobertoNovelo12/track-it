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
                    >
                        <rect
                            x="3"
                            y="4"
                            width="18"
                            height="13"
                            rx="1"
                        />

                        <path d="M2 20h20"/>
                    </svg>

                    @break


                @case('swap')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M4 7h15"/>
                        <path d="M16 4l3 3-3 3"/>
                        <path d="M20 17H5"/>
                        <path d="M8 14l-3 3 3 3"/>
                    </svg>

                    @break


                @case('tools')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            d="
                                M14.7 6.3
                                a4 4 0 00-5.4 5.4
                                L3 18v3h3
                                l6.3-6.3
                                a4 4 0 005.4-5.4
                                l-2.5 2.5-2-2z
                            "
                        />
                    </svg>

                    @break


                @case('report')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M6 2h9l5 5v15H6z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M9 13h6"/>
                        <path d="M9 17h6"/>
                    </svg>

                    @break


                @case('users')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                        <circle cx="17" cy="9" r="2"/>
                    </svg>

                    @break


                @case('list')

                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M8 6h13"/>
                        <path d="M8 12h13"/>
                        <path d="M8 18h13"/>

                        <path d="M3 6l1 1 2-2"/>
                        <path d="M3 12l1 1 2-2"/>
                        <path d="M3 18l1 1 2-2"/>
                    </svg>

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
            >
                <path d="M9 5l7 7-7 7"/>
            </svg>

        </a>

    </div>

</div>