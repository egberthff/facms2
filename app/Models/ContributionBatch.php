<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContributionBatch extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contribution_batches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'claim_id',
        'batch_reference',
        'amount_per_member',
        'total_members',
        'total_amount', 
        'generated_at',
        'generated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_per_member' => 'decimal:2',
            'total_ammount' => 'decimal:2',
            'total_members' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * Get the claim associated with the batch.
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }

    /**
     * Get the user who generated the batch.
     */
    public function generatedBy(): BelongsTo
    {
        // Custom keys defined since your migration references 'user_id' instead of 'id'
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    /**
     * Get the detailed contributions for this batch.
     */
    public function contributionDetails(): HasMany
    {
        return $this->hasMany(ContributionDetail::class, 'contribution_batch_id');
    }

    public function ledgerEntries()
    {
        return $this->morphMany(TreasuryLedger::class, 'sourceable');
    }
}
