<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $settings = [
        'cash_flow_prepared_by' => 'Prepared by, Campus Cash Flow report. Defaults to the campus Treasurer when empty.',
        'cash_flow_checked_by' => 'Checked by, Campus Cash Flow report.',
    ];

    public function up(): void
    {
        $now = now();

        DB::table('campuses')->pluck('id')->each(function (string $campusId) use ($now): void {
            foreach ($this->settings as $key => $description) {
                $exists = DB::table('system_settings')
                    ->where('setting_key', $key)
                    ->where('campus_id', $campusId)
                    ->whereNull('cycle_label')
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('system_settings')->insert([
                    'id' => (string) Str::uuid(),
                    'setting_key' => $key,
                    'setting_value' => '',
                    'description' => $description,
                    'campus_id' => $campusId,
                    'cycle_label' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('setting_key', array_keys($this->settings))
            ->delete();
    }
};
