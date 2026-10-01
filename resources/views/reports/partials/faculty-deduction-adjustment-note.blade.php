<td class="no-border text-center" style="vertical-align: top;">
    @foreach ($adjustments as $adjustment)
        <div class="adjustment-note">
            {{ $adjustment['description'] ?: ucwords(str_replace('_', ' ', $adjustment['type'])) }}
            <br>
            P{{ number_format(abs($adjustment['amount']), 2) }}{{ $adjustment['amount'] < 0 ? ' (less)' : '' }}
        </div>
    @endforeach
</td>
