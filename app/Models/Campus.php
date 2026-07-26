<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Scopes\CampusMemberScope;
Use App\Models\Member;

class Campus extends Model
{
    /** @use HasFactory<\Database\Factories\CampusFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'campus_name',
        'acronym',
        'location',
        'is_active',
    ];

    protected function cast(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function treasuryLedger()
    {
        return $this->hasMany(TreasuryLedger::class);
    }

    public function settings()
    {
        return $this->hasMany(SystemSetting::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new CampusMemberScope);
    }
}
