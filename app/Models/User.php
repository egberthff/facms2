<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasUuids;

    protected $primaryKey = 'user_id';
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

    // public function scopeCampus(Builder $query, ?string $campusId = null): Builder
    // {
    //     $authUser = auth()->user();
    
    //     // Security Fallback: empty result set if guest logs in
    //     if (!$authUser) {
    //         return $query->whereRaw('1 = 0'); 
    //     }
    
    //     // Case 1: President Role Logic
    //     if ($authUser->role === 'president') {
    //         // If a valid campus UUID filter is provided, restrict to it. Otherwise, return all.
    //         return $campusId 
    //             ? $query->whereHas('member', fn($q) => $q->where('campus_id', $campusId))
    //             : $query;
    //     }
    
    //     // Case 2: Treasurer Role Logic (Fallback)
    //     // Safely look up the treasurer's assigned campus UUID via relationship
    //     $treasurerCampusId = $authUser->member?->campus_id;
    
    //     if (!$treasurerCampusId) {
    //         return $query->whereRaw('1 = 0'); // Block access if treasurer lacks a campus assignment
    //     }
    
    //     return $query->whereHas('member', fn($q) => $q->where('campus_id', $treasurerCampusId));
    // }

}


