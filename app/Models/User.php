<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

        /**
     * Get and cache the user's campus ID for the current request duration.
     */
    public function getCachedCampusId(): ?string
    {
        // Use a static property cache to prevent duplicate queries on the same request
        static $campusId = 'unloaded';

        if ($campusId === 'unloaded') {
            // Force bypass global scopes on Member to avoid infinite loops
            $member = $this->member()->withoutGlobalScopes()->first();
            $campusId = $member ? $member->campus_id : null;
        }

        return $campusId;
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

    /**
     * Get an array of permission names assigned directly to the user.
     *
     * @return array
     */
    public function getDirectPermissionNames(): array
    {
        return $this->permissions()->pluck('name')->toArray();
    }

}


