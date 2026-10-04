<div class="relative overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-xs">
    <!-- $attributes allows you to pass custom classes, IDs, or data attributes dynamically -->
    <table {{ $attributes->merge(['class' => 'w-full text-sm text-left text-gray-500']) }}>
        {{ $slot }}
    </table>
</div>
