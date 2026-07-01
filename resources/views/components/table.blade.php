<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <!-- $attributes allows you to pass custom classes, IDs, or data attributes dynamically -->
    <table {{ $attributes->merge(['class' => 'w-full text-sm text-left text-gray-500']) }}>
        {{ $slot }}
    </table>
</div>
