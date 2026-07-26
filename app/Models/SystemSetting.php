<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'campus_id'
    ];

        /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    */
    //  public function getRouteKeyName(): string
    // {
    //     return 'id';
    // }

    // 
    // /Relationships
    // /*
    public function campus(){
        return $this->belongsTo(Campus::class);
    }

    /**
     * Scope to filter down to the annual limit row.
     */
    public function scopeAnnualLimit($query, ?string $campusId)
    {

        return $query->where('setting_key', 'annual_claim_limit');
    }

     /**
     * Static helper to quickly retrieve the raw decimal limit value.
     */
    public static function getGlobalLimit(): float
    {
        // Safely resolve the query builder scope, defaulting to 23000.00 if the DB row doesn't exist
        $setting = self::annualLimit()->first();
        return $setting ? (float) $setting->setting_value : 23000.00;
    }
}