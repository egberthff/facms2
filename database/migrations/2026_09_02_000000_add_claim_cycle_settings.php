<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $campusIds = DB::table('campuses')->pluck('id');

        foreach ($campusIds as $campusId) {
            foreach ([
                ['claim_cycle_years', '2', 'Number of years in the claims cycle.'],
                ['claim_cycle_start_month', '10', 'Month when the claims cycle starts (1-12).'],
            ] as [$key, $value, $description]) {
                $exists = DB::table('system_settings')
                    ->where('setting_key', $key)
                    ->where('campus_id', $campusId)
                    ->whereNull('cycle_label')
                    ->exists();

                if (!$exists) {
                    DB::table('system_settings')->insert([
                        'id' => (string) Str::uuid(),
                        'setting_key' => $key,
                        'setting_value' => $value,
                        'description' => $description,
                        'campus_id' => $campusId,
                        'cycle_label' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('setting_key', ['claim_cycle_years', 'claim_cycle_start_month'])
            ->delete();
    }
};