<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(FundTransfer::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(TreasuryLedger::class);
    }
}
