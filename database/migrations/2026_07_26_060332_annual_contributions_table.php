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
        Schema::create('annual_contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Human-readable transaction reference code for this transaction
            $table->string('reference_number')->unique()->description("Human-readable transaction reference code for this record");

            // Relationships
            $table->foreignUuid('member_id')
                ->constrained('members', 'id')
                ->cascadeOnDelete();

            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods', 'id')
                ->nullOnDelete();

            $table->string('payment_reference_number')->default(null)->description('Reference number from the payment gateway or bank transaction');    

            // Tracking the specific year this payment covers
            $table->year('contribution_year');

            // Financial Information
            $table->decimal('amount_paid', 12, 2);

            // Workflow / Tracking status
            $table->enum('status', [
                'Pending',
                'Verified',
                'Failed',
            ])->default('Pending');

            $table->text('remarks')->nullable();

            // Dates
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes & Constraints
            |--------------------------------------------------------------------------
            */
            $table->index('member_id');
            $table->index('contribution_year');
            $table->index('status');

            $table->unique(['member_id', 'contribution_year']); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('annual_contributions');
    }
};
