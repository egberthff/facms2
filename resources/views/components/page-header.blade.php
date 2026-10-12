@props([

'title',

'description'=>''

])

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

    <div>

        <h1 class="text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl">

            {{ $title }}

        </h1>

        <p class="mt-1 text-sm text-gray-500">

            {{ $description }}

        </p>

    </div>

    <div class="flex flex-wrap items-center gap-2">

        {{ $actions ?? '' }}

    </div>

</div>