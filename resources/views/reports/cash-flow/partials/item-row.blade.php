@php
    $sourceLabel = match ($item->source_type) {
        \App\Models\CollectionSummaryItem::SOURCE_FUND_TRANSFER => 'FFA transfer',
        \App\Models\CollectionSummaryItem::SOURCE_CLAIM_PAYMENT => 'Claim release',
        default => 'Manual',
    };
@endphp
<tr x-data="{ editing: false }" class="align-top">
    <td class="px-4 py-2.5 {{ $indent ?? '' }}">
        <div x-show="!editing">
            <span class="text-gray-900">{{ $item->label }}</span>
            @if($item->notes)
                <span class="text-gray-500">({{ $item->notes }})</span>
            @endif
        </div>

        @if($canManage)
            <form x-show="editing" x-cloak method="POST" action="{{ route('reports.cash-flow.items.update', $item) }}" class="grid gap-2 md:grid-cols-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="{{ $item->section }}">
                <input type="text" name="label" value="{{ $item->label }}" class="md:col-span-3 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs" required>
                <input type="text" name="notes" value="{{ $item->notes }}" placeholder="Note, e.g. with P10 transaction fee" class="md:col-span-3 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                @if($item->is_manual)
                    <input type="date" name="entry_date" value="{{ $item->entry_date?->toDateString() }}" class="md:col-span-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs">
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ $item->amount }}" class="md:col-span-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-xs" required>
                @endif
                <div class="flex gap-2 {{ $item->is_manual ? 'md:col-span-2' : 'md:col-span-6' }}">
                    <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-blue-700">Save</button>
                    <button type="button" @click="editing = false" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-xs transition hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        @endif
    </td>
    <td class="px-4 py-2.5 whitespace-nowrap text-gray-500">{{ $item->entry_date?->format('M d, Y') }}</td>
    <td class="px-4 py-2.5 whitespace-nowrap">
        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $item->is_manual ? 'bg-slate-100 text-slate-600' : 'bg-blue-50 text-blue-700' }}">{{ $sourceLabel }}</span>
    </td>
    <td class="px-4 py-2.5 text-right whitespace-nowrap text-gray-900">{{ number_format((float) $item->amount, 2) }}</td>
    <td class="px-4 py-2.5 text-right whitespace-nowrap">
        @if($canManage)
            <button type="button" x-show="!editing" @click="editing = true" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Edit</button>
            @if($item->is_manual)
                <form method="POST" action="{{ route('reports.cash-flow.items.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove this line?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ml-2 text-xs font-semibold text-rose-600 hover:text-rose-800">Remove</button>
                </form>
            @endif
        @endif
    </td>
</tr>
