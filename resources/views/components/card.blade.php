<div
    {{ $attributes->merge([
        'class'=>'bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300 dark:bg-gray-800 dark:border-gray-700',
    ]) }}>

    <div class="p-6">

        {{ $slot }}

    </div>

</div>