<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundTransfer extends Model
{
    use HasUuids;
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'fund_account_id',
        'type',
        'source_campus_id',
        'destination_campus_id',
        'reference_number',
        'description',
        'amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'source_ledger_id',
        'destination_ledger_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function fundAccount(): BelongsTo
    {
        return $this->belongsTo(FundAccount::class);
    }

    public function sourceCampus(): BelongsTo
    {
        // return $this->belongsTo(Campus::class, 'source_campus_id');
        return $this->belongsTo(Campus::class, 'source_campus_id', 'id');
    }

    public function destinationCampus(): BelongsTo
    {
        // return $this->belongsTo(Campus::class, 'destination_campus_id');
        return $this->belongsTo(Campus::class, 'destination_campus_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(TreasuryLedger::class, 'transfer_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
