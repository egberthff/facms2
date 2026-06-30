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
        Schema::create('fund_transactions', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->enum('transaction_type', [
                'Opening Balance',
                'Contribution',
                'Claim Payment',
                'Adjustment'
            ]);

            $table->foreignUuid('member_id')
                ->nullable()
                ->constrained('members', 'id')
                ->nullOnDelete();

            $table->foreignUuid('claim_id')
                ->nullable()
                ->constrained('claims', 'id')
                ->nullOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('reference')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->foreignUuid('created_by')
                ->constrained('users', 'user_id')
                ->restrictOnDelete();

            $table->timestamp('transaction_date');

            $table->timestamps();

            $table->index('transaction_type');
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transactions');
    }
};