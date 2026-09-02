<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('cycle_label');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedTinyInteger('duration_years');
            $table->unsignedTinyInteger('start_month');
            $table->timestamps();

            $table->unique(['campus_id', 'cycle_label']);
            $table->unique(['campus_id', 'start_date']);
            $table->index(['campus_id', 'start_date', 'end_date']);
        });

        $now = now();
        $campusIds = DB::table('campuses')->pluck('id');

        foreach ($campusIds as $campusId) {
            $labels = DB::table('system_settings')
                ->where('campus_id', $campusId)
                ->whereNotNull('cycle_label')
                ->pluck('cycle_label')
                ->filter(fn ($label) => preg_match('/^(\d{4})-(\d{4})$/', $label));

            foreach ($labels->unique() as $label) {
                [$startYear, $endYear] = array_map('intval', explode('-', $label));
                $duration = max(1, $endYear - $startYear);
                $start = now()->setDate($startYear, 10, 1)->startOfDay();
                $end = (clone $start)->addYears($duration)->subSecond();

                DB::table('claims_cycles')->insertOrIgnore([
                    'id' => (string) Str::uuid(),
                    'campus_id' => $campusId,
                    'cycle_label' => $label,
                    'start_date' => $start,
                    'end_date' => $end,
                    'duration_years' => $duration,
                    'start_month' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('claims_cycles');
    }
};