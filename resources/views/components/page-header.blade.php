@props([

'title',

'description'=>''

])

<div class="mb-6 flex justify-between items-center">

    <div>

        <h1 class="text-2xl font-bold">

            {{ $title }}

        </h1>

        <p class="text-gray-500 mt-1">

            {{ $description }}

        </p>

    </div>

    <div>

        {{ $actions ?? '' }}

    </div>

</div>