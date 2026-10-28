@props([
    'heading',
    'icon' => 'user',
    'width' => 'md',
])

@php
    $widthClass = match ($width) {
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        default => 'max-w-md',
    };
@endphp

<div
    class="
        w-full
        {{ $widthClass }}

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border-strong)]

        rounded-lg

        shadow-sm
        shadow-[var(--theme-shadow)]

        px-6
        sm:px-8
        lg:px-10

        py-8
        sm:py-10
    "
>

    <div class="flex flex-col items-center mb-6">

        <span
            class="
                mb-3
                text-[var(--theme-text)]
            "
        >

            @switch($icon)

                @case('key')

                    <svg
                        class="w-9 h-9"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.3"
                    ><use href="{{ \App\Support\Icono::url('i-9a316c8') }}"></use></svg>

                    @break


                @case('user-plus')

                    <svg
                        class="w-9 h-9"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.3"
                    ><use href="{{ \App\Support\Icono::url('i-bc9f57d') }}"></use></svg>

                    @break


                @default

                    <svg
                        class="w-9 h-9"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.3"
                    ><use href="{{ \App\Support\Icono::url('i-c5c03e4') }}"></use></svg>

            @endswitch

        </span>


        <h1
            class="
                text-base
                font-semibold
                tracking-wide
                uppercase

                text-[var(--theme-text-strong)]
            "
        >
            {{ $heading }}
        </h1>

    </div>


    {{ $slot }}

</div>