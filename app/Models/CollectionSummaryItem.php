<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionSummaryItem extends Model
{
    use HasUuids;

    protected $table = 'collection_summary_items';

    protected $fillable = [
        'summary_id',
        'section',
        'label',
        'amount',
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
            'is_manual' => 'boolean',
        ];
    }

    public function summary(): BelongsTo
    {
        return $this->belongsTo(CollectionSummaryPeriod::class, 'summary_id');
    }
}
