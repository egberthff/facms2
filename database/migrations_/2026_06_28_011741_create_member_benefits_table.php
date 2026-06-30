<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

return new class extends Migration
{
    use HasUuids;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_benefits', function (Blueprint $table) {
            $table->uuid('member_benefit_id')->primary();
            $table->foreignUuid('member_id')->constrained('members', 'member_id');
            $table->string('benefit_year')->nullable();
            $table->decimal('annual_limit', 10, 2)->default(0.00);
            $table->decimal('amount_claimed', 10, 2)->default(0.00);
            $table->decimal('remaining_balance', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_benefits');
    }
};
