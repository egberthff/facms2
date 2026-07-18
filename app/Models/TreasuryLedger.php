<?php

namespace App\Models;

use App\Models\Scopes\CampusMemberScope; // 1. IMPORT YOUR SCOPE CLASS
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TreasuryLedger extends Model
{
    // Note: Removed SoftDeletes to preserve accounting audit-trail integrity
    use HasUuids;

    protected $table = 'treasury_ledger';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'campus_id',         // 2. EXPOSE THE CAMPUS FOREIGN KEY
        'sourceable_type',
        'sourceable_id',
        'reference_number',
        'description',
        'type',
        'amount',
        'running_balance',
        'entry_by',
    ];

    /**
     * The "booted" method applies global restrictions to queries.
     */
    protected static function booted(): void
    {
        // 3. REGISTER THE GLOBAL SCOPE FOR AUTO-FILTERING
        static::addGlobalScope(new CampusMemberScope);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
        ];
    }

    /**
     * Get the owning sourceable model.
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
