<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionsAdjustments extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'collection_adjustments';

    protected $fillable = [
        'campus_id',
        'adjustment_month',
        'reference_month',
        'adjustment_type',
        'amount',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'adjustment_month' => 'date',
        'reference_month' => 'date',
        'amount' => 'decimal:2',
    ];

    /*
     * ---------------------------------------------------------
     * Campus
     * ---------------------------------------------------------
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    /*
     * ---------------------------------------------------------
     * User who created the adjustment
     * ---------------------------------------------------------
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /*
     * ---------------------------------------------------------
     * User who last updated the adjustment
     * ---------------------------------------------------------
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}