@props([

'title',

'description'

])

<div class="py-20 text-center">

    <svg
        class="mx-auto w-20 h-20 text-gray-300"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24">

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2v-4"/>

    </svg>

    <h3 class="mt-4 text-lg font-semibold">

        {{ $title }}

    </h3>

    <p class="text-gray-500 mt-2">

        {{ $description }}

    </p>

</div>