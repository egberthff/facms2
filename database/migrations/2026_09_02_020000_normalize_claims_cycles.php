<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('campuses')->pluck('id')->each(function ($campusId) {
            $previousEnd = null;

            DB::table('claims_cycles')
                ->where('campus_id', $campusId)
                ->orderBy('start_date')
                ->orderBy('id')
                ->get(['id', 'start_date', 'end_date'])
                ->each(function ($cycle) use (&$previousEnd) {
                    if ($previousEnd !== null && $cycle->start_date <= $previousEnd) {
                        DB::table('claims_cycles')->where('id', $cycle->id)->delete();
                        return;
                    }

                    $previousEnd = $cycle->end_date;
                });
        });
    }

    public function down(): void
    {
        // The removed rows were duplicate legacy projections and cannot be restored safely.
    }
};