<div
    {{ $attributes->merge([
        'class'=>'bg-white rounded-xl shadow-xs border border-gray-200 dark:bg-gray-800 dark:border-gray-700',
    ]) }}>

    <div class="p-4 sm:p-6">

        {{ $slot }}

    </div>

</div>