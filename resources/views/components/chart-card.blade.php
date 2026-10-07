@props([
    'title',
    'subtitle' => null,
    'chart',
    'series' => ['labels' => [], 'values' => []],
    'type' => 'column',
    'format' => 'count',
    'critical' => null,
    'height' => 'h-72',
])

@php
    $labels = $series['labels'] ?? [];
    $values = $series['values'] ?? [];
    $hasData = collect($values)->contains(fn ($value) => (float) $value > 0);
    $formatValue = fn ($value) => $format === 'currency' ? '₱' . number_format((float) $value, 2) : number_format((float) $value);
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs']) }}>
    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3.5 sm:px-6">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">{{ $title }}</h3>
        @if($subtitle)
            <p class="mt-0.5 text-xs text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex-1 p-4 sm:p-6">
        @if($hasData)
            <div class="relative {{ $height }}">
                <canvas
                    data-chart="{{ $chart }}"
                    data-chart-type="{{ $type }}"
                    data-chart-format="{{ $format }}"
                    @if($critical) data-chart-critical="{{ $critical }}" @endif
                    role="img"
                    aria-label="{{ $title }}"></canvas>
            </div>

            <details class="group mt-4 text-sm">
                <summary class="cursor-pointer select-none text-xs font-semibold text-blue-600 hover:text-blue-700">View data</summary>
                <div class="mt-2 max-h-64 overflow-y-auto rounded-lg border border-gray-100">
                    <table class="w-full text-left text-xs">
                        <tbody class="divide-y divide-gray-100">
                            @foreach($labels as $index => $label)
                                <tr>
                                    <td class="px-3 py-2 text-gray-600">{{ $label }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-gray-900 tabular-nums">{{ $formatValue($values[$index] ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @else
            <div class="flex {{ $height }} items-center justify-center rounded-lg border border-dashed border-gray-200 text-sm text-gray-400">
                No data yet
            </div>
        @endif
    </div>
</div>
