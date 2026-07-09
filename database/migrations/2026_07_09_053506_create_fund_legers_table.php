<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fund_ledgers', function (Blueprint $table) {
            $table->uuid('ledger_id')->primary();
            $table->date('transaction_date')->nullable(false);
            $table->enum('transaction_type', ['FUND_ALLOCATION', 'CLAIM_DISBURSEMENT', 'CONTRIBUTION', 'PERPETUAL', 'ANNUAL', 'ADJUSTMENT'])->nullable(false);
            $table->string('reference_type', 100)->nullable();
            $table->string('reference_id', 36)->nullable(); 
            
            // Balance Tracking
            $table->decimal('previous_balance', 15, 2)->default(0.00);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);

            $table->text('remarks')->nullable();

            $table->foreignUuid('created_by')->constrained('users', 'user_id');
            $table->timestamps();

            $table->index(['transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_ledgers');
    }
};
