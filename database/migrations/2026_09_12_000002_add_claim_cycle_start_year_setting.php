<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('campuses')->pluck('id')->each(function (string $campusId) use ($now): void {
            $exists = DB::table('system_settings')
                ->where('setting_key', 'claim_cycle_start_year')
                ->where('campus_id', $campusId)
                ->whereNull('cycle_label')
                ->exists();

            if ($exists) {
                return;
            }

            $startMonth = (int) (DB::table('system_settings')
                ->where('setting_key', 'claim_cycle_start_month')
                ->where('campus_id', $campusId)
                ->whereNull('cycle_label')
                ->value('setting_value') ?: 10);
            $startYear = $now->month >= $startMonth ? $now->year : $now->year - 1;

            DB::table('system_settings')->insert([
                'id' => (string) Str::uuid(),
                'setting_key' => 'claim_cycle_start_year',
                'setting_value' => (string) $startYear,
                'description' => 'Initial year when the claims cycle starts.',
                'campus_id' => $campusId,
                'cycle_label' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('setting_key', 'claim_cycle_start_year')
            ->delete();
    }
};
