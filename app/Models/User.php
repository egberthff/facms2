<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasUuids;

    protected $primaryKey = 'user_id';
     // ADD THIS LINE FOR SPATIE
    protected $guard_name = 'web'; 
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    */
     public function getRouteKeyName(): string
    {
        return 'user_id';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function member()
    {
        return $this->hasOne(Member::class, 'user_id', 'user_id');
    }

    public function reviewedClaims()
    {
        return $this->hasMany(Claim::class, 'reviewed_by');
    }

    public function releasedPayments()
    {
        return $this->hasMany(ClaimPayment::class, 'released_by');
    }

    public function fundTransactions()
    {
        return $this->hasMany(FundTransaction::class, 'created_by');
    }

    /**
     * Fetch all claims through the member bridging relationship.
     */
    public function claims(): HasManyThrough
    {
        return $this->hasManyThrough(
            Claim::class,
            Member::class,
            'user_id',    // Foreign key on members table
            'member_id',  // Foreign key on claims table
            'user_id',    // Local key on users table
            'id'          // Local key on members table
        );
    }
}


