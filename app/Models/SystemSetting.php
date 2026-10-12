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
        'campus_id',
        'cycle_label'
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
    public function scopeAnnualLimit($query, ?string $campusId = null)
    {

        return $query->where('setting_key', 'annual_claim_limit')
                     ->where('cycle_label', null)
                     ->when($campusId, function ($query, $campusId) {
                return $query->where('campus_id', $campusId);
            });
    }

     /**
     * Static helper to quickly retrieve the raw decimal limit value.
     */
    public static function getGlobalLimit(?string $campusId = null): float
    {
        // Safely resolve the query builder scope, defaulting to 23000.00 if the DB row doesn't exist
        $setting = self::annualLimit($campusId)->first();   
        \Log::error('SystemSetting::getGlobalLimit() - Retrieved setting: ' . ($setting ? $setting->setting_value : 'null') . ' for campus_id: ' . ($campusId ?? 'null'));  
        return $setting ? (float) $setting->setting_value : 23000.00; 
    }

    public static function getCycleYears(?string $campusId = null): int
    {
        return self::getCycleValue('claim_cycle_years', $campusId, 2, 1, 20);
    }

    public static function getCycleStartMonth(?string $campusId = null): int
    {
        return self::getCycleValue('claim_cycle_start_month', $campusId, 10, 1, 12);
    }

    public static function getCycleStartYear(?string $campusId = null): int
    {
        $startMonth = self::getCycleStartMonth($campusId);
        $default = now()->month >= $startMonth ? now()->year : now()->year - 1;

        return self::getCycleValue('claim_cycle_start_year', $campusId, $default, 1900, 2200);
    }

    private static function getCycleValue(
        string $key,
        ?string $campusId,
        int $default,
        int $minimum,
        int $maximum
    ): int {
        $value = self::query()
            ->where('setting_key', $key)
            ->whereNull('cycle_label')
            ->when($campusId, fn ($query) => $query->where('campus_id', $campusId))
            ->value('setting_value');

        $value = filter_var($value, FILTER_VALIDATE_INT);

        return $value !== false && $value >= $minimum && $value <= $maximum
            ? $value
            : $default;
    }
}