<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnualContribution extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'annual_contributions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'reference_number',
        'member_id',
        'payment_method_id',
        'payment_reference_number',
        'contribution_year',
        'amount_paid',
        'status',
        'remarks',
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
            'amount_paid' => 'decimal:2',
            'contribution_year' => 'integer',
            'paid_at' => 'datetime',      
        ];
    }

    /**
     * Get the member associated with the annual contribution.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function ledgerEntries()
    {
        return $this->morphMany(TreasuryLedger::class, 'sourceable');
    }
}
