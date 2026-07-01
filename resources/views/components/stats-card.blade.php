@props([

'title',

'value',

'icon',

'color'=>'blue'

])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <div class="flex justify-between">

        <div>

            <p class="text-gray-500">

                {{ $title }}

            </p>

            <h2 class="mt-2 text-3xl font-bold">

                {{ $value }}

            </h2>

        </div>

        <div>

            {{ $icon }}

        </div>

    </div>

</div>