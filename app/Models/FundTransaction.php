<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FundTransaction extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'transaction_type',
        'member_id',
        'claim_id',
        'amount',
        'reference',
        'remarks',
        'created_by',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'datetime',
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
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}