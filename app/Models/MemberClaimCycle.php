<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * A member's claim eligibility cycle. Dates are inclusive months:
 * cycle_start is the 1st of the starting month, cycle_end the last day of the final month.
 */
class MemberClaimCycle extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'member_id',
        'cycle_start',
        'cycle_end',
        'duration_years',
        'claim_limit',
    ];

    protected function casts(): array
    {
        return [
            'cycle_start' => 'date',
            'cycle_end' => 'date',
            'duration_years' => 'integer',
            'claim_limit' => 'decimal:2',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id')->withoutGlobalScopes();
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'member_claim_cycle_id');
    }

    /**
     * Cycles whose inclusive date range contains the given day.
     */
    public function scopeCovering($query, Carbon $date)
    {
        return $query->where('cycle_start', '<=', $date->copy()->endOfDay())
            ->where('cycle_end', '>=', $date->copy()->startOfDay());
    }

    public function covers(Carbon $date): bool
    {
        return $date->copy()->startOfDay()->betweenIncluded($this->cycle_start, $this->cycle_end);
    }

    public function isActive(?Carbon $date = null): bool
    {
        return $this->covers($date ?? now());
    }

    public function isExpired(?Carbon $date = null): bool
    {
        return ($date ?? now())->copy()->startOfDay()->gt($this->cycle_end);
    }

    public function label(): string
    {
        return $this->cycle_start->format('F Y') . ' - ' . $this->cycle_end->format('F Y');
    }
}
