<?php

namespace App\Models;

use App\Enums\ServerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Campus;
use App\Models\Scopes\CampusMemberScope;

class Member extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birth_date',
        'department',
        'position',
        'employment_status',
        'membership_status',
        'contact_number',
        'address',
        'gcash_number',
        'gcash_account_name',
        'campus_id'
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new CampusMemberScope);
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function claims()
    {
        // return $this->hasMany(Claim::class);
        return $this->hasMany(Claim::class, 'member_id', 'id');
    }

    public function contributions()
    {
        // return $this->hasMany(ContributionDetail::class);
        return $this->hasMany(ContributionDetail::class, 'member_id', 'id');
    }

    public function fundTransactions()
    {
        return $this->hasMany(FundTransaction::class, 'member_id', 'id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id', 'id'); 
    }

    public function annualContributions()
    {
        return $this->hasMany(AnnualContribution::class, "member_id", 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim(
            "{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}"
        );
    }

    public function scopeActive($query)
    {
        return $query->where('membership_status', 'Active');
    }

    // Local filter scope so the President can switch between campuses via dropdown selection
    public function scopeFilterCampus($query, ?string $campusId = null)
    {
        return $query->when($campusId, fn($q) => $q->where('campus_id', $campusId));
    }


}