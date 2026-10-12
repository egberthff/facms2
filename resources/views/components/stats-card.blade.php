@props([

'title',

'value',

'icon',

'color'=>'blue'

])

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs sm:p-6">

    <div class="flex items-start justify-between gap-4">

        <div>

            <p class="text-sm font-medium text-gray-500">

                {{ $title }}

            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 tabular-nums sm:text-3xl">

                {{ $value }}

            </h2>

        </div>

        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gray-50 ring-1 ring-gray-100 [&>svg]:h-6 [&>svg]:w-6">

            {{ $icon }}

        </div>

    </div>

</div>