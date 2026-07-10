<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder; 
use Carbon\Carbon;

class Claim extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'claim_number',
        'member_id',
        'claim_type_id',
        'payment_method_id',
        'requested_amount',
        'approved_amount',
        'annual_limit',
        'description',
        'status',
        'remarks',
        'submitted_at',
        'approved_at',
        'disbursed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'annual_limit' => 'decimal:2',

            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    */
     public function getRouteKeyName(): string
    {
        return 'id';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

public function claimType()
{
    return $this->belongsTo(ClaimType::class, 'claim_type_id', 'id');
}

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class , 'payment_method_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents()
    {
        return $this->hasMany(ClaimDocument::class);
    }

    public function payment()
    {
        return $this->hasOne(ClaimPayment::class);
    }

    public function contributions()
    {
        return $this->hasMany(ContributionDetail::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }
    
    public function scopeDisbursed($query)
    {
        return $query->where('status', 'Disbursed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    /**
     * Scope a query to only include claims belonging to a specific user's member profile.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('member', function ($builder) use ($userId) {
            $builder->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to only include claims belonging to a specific member ID.
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope to filter claims approved or disbursed within the current year.
     */
    public function scopeApprovedThisYear(Builder $query): Builder
    {
        return $query->whereIn('status', ['Approved', 'Disbursed'])
                     ->whereYear('submitted_at', Carbon::now()->year);
    }

    /**
     * Scope to filter disbursed claims within the current month.
     */
    public function scopeDisbursedThisMonth(Builder $query): Builder
    {
        return $query->where('status', 'Disbursed')
                     ->whereMonth('disbursed_at', Carbon::now()->month)
                     ->whereYear('disbursed_at', Carbon::now()->year);
    }
}