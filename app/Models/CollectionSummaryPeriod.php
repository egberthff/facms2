<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSummaryPeriod extends Model
{
    use HasUuids;

    public const TYPE_CASH_FLOW = 'cash_flow';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINALIZED = 'finalized';

    protected $table = 'collection_summary_periods';

    protected $fillable = [
        'campus_id',
        'report_type',
        'period_label',
        'period_from',
        'period_to',
        'forwarded_label',
        'forwarded_balance',
        'total_funds',
        'total_disbursements',
        'cash_on_hand',
        'title',
        'description',
        'prepared_by',
        'approved_by',
        'prepared_by_name',
        'checked_by_name',
        'status',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
            'forwarded_balance' => 'decimal:2',
            'total_funds' => 'decimal:2',
            'total_disbursements' => 'decimal:2',
            'cash_on_hand' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    public function scopeCashFlow(Builder $query): Builder
    {
        return $query->where('report_type', self::TYPE_CASH_FLOW);
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

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
