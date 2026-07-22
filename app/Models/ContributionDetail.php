<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionDetail extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contribution_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contribution_batch_id',
        'member_id',
        'amount',
        'status',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    /**
     * Get the batch this contribution detail belongs to.
     */
    public function contributionBatch(): BelongsTo
    {
        return $this->belongsTo(ContributionBatch::class, 'contribution_batch_id');
    }

    /**
     * Get the member associated with this contribution detail.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function ledgerEntries()
    {
        return $this->morphMany(TreasuryLedger::class, 'sourceable');
    }
}
