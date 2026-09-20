<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CnaShare extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cna_shares';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_number',
        'member_id',
        'year',
        'due_amount',
        'paid_amount',
        'status',
        'paid_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'due_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the member that owns the CNA share contribution.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    /**
     * Get the payment method for this CNA share contribution.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class , 'payment_method_id', 'id');
    }

    /**
     * Get the user who recorded/created the contribution record.
     */
    public function creator(): BelongsTo
    {
        // Explicitly matches your custom foreign key column 'user_id' in the users table
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
