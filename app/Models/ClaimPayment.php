<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClaimPayment extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'claim_id',
        'payment_method_id',
        'reference_number',
        'amount',
        'processed_by',
        'released_at',
        'receipt_path',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'released_at' => 'datetime',
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

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function claimType() 
    { 
        return $this->belongsTo(ClaimType::class); 
    }
    public function member() 
    {
         return $this->belongsTo(Member::class); 
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function ledgerEntries()
    {
        return $this->morphMany(TreasuryLedger::class, 'sourceable');
    }
}