<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Claim eligibility cycles are per member: each starts at the month of the member's first
        // qualifying claim and snapshots the campus duration/limit in force at that moment.
        Schema::create('member_claim_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('cycle_start');
            $table->date('cycle_end');
            $table->unsignedTinyInteger('duration_years');
            $table->decimal('claim_limit', 12, 2);
            $table->timestamps();

            $table->unique(['member_id', 'cycle_start']);
            $table->index(['member_id', 'cycle_start', 'cycle_end']);
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->foreignUuid('member_claim_cycle_id')
                ->nullable()
                ->after('member_id')
                ->constrained('member_claim_cycles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_claim_cycle_id');
        });

        Schema::dropIfExists('member_claim_cycles');
    }
};
