<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CNAShare extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    // Explicitly link the table if you renamed it to 'cna_shares' in the migration
    protected $table = 'cna_shares'; 

    protected $fillable = [
        'member_id',
        'year',
        'amount',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($share) {
            // Automatically tie the creator ID safely on creation
            $share->created_by = Auth::id();
        });
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

      public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class , 'payment_method_id', 'id');
    }
}
