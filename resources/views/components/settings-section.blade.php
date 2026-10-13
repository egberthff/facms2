{{-- Shared shell for every block on the settings page so they share one header and spacing. --}}
{{-- Pass :clip="false" when the section holds dropdowns that must spill past its edges. --}}
@props(['title', 'description' => null, 'clip' => true])

<section {{ $attributes->class(['rounded-xl border border-slate-200 bg-white shadow-xs', 'overflow-hidden' => $clip]) }}>
    <header class="flex flex-col gap-1 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-5">
        <div class="min-w-0">
            <h3 class="text-base font-semibold tracking-tight text-slate-900">{{ $title }}</h3>
            @if($description)
                <p class="text-xs leading-5 text-slate-500">{{ $description }}</p>
            @endif
        </div>
        {{ $actions ?? '' }}
    </header>

    <div class="p-4 sm:p-5">
        {{ $slot }}
    </div>
</section>
