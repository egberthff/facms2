<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSummaryPeriod extends Model
{
    use HasUuids;

    protected $table = 'collection_summary_periods';

    protected $fillable = [
        'campus_id',
        'period_label',
        'title',
        'description',
        'prepared_by',
        'approved_by',
        'status',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by', 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CollectionSummaryItem::class, 'summary_id');
    }
}
