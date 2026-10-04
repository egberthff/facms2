@props([

'title',

'description'

])

<div class="px-4 py-14 text-center sm:py-16">

    <svg
        class="mx-auto h-12 w-12 text-gray-300"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24">

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2v-4"/>

    </svg>

    <h3 class="mt-4 text-base font-semibold text-gray-900">

        {{ $title }}

    </h3>

    <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">

        {{ $description }}

    </p>

</div>