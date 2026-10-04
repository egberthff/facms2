<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CollectionSummaryItem extends Model
{
    use HasUuids;

    public const SECTION_FUNDS = 'funds';

    public const SECTION_DISBURSEMENTS = 'disbursements';

    public const SOURCE_FUND_TRANSFER = 'fund_transfer';

    public const SOURCE_CLAIM_PAYMENT = 'claim_payment';

    public const SOURCE_MANUAL = 'manual';

    protected $table = 'collection_summary_items';

    protected $fillable = [
        'summary_id',
        'section',
        'label',
        'amount',
        'entry_date',
        'group_label',
        'is_manual',
        'sort_order',
        'source_type',
        'source_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
            'is_manual' => 'boolean',
        ];
    }

    public function summary(): BelongsTo
    {
        return $this->belongsTo(CollectionSummaryPeriod::class, 'summary_id');
    }

    public function ledgerEntries(): MorphMany
    {
        return $this->morphMany(TreasuryLedger::class, 'sourceable');
    }
}
