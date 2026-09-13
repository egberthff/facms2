<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimsCycle extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'campus_id',
        'cycle_label',
        'start_date',
        'end_date',
        'duration_years',
        'start_month',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'duration_years' => 'integer',
            'start_month' => 'integer',
        ];
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}