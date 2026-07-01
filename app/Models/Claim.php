<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'status',
        'remarks',
        'submitted_at',
        'approved_at',
        'paid_at',
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
            'paid_at' => 'datetime',
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
        return $this->belongsTo(Member::class);
    }

    public function claimType()
    {
        return $this->belongsTo(ClaimType::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
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
    
    public function scopePaid($query)
    {
        return $query->where('status', 'Paid');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }
}