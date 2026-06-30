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
        Schema::create('claim_payments', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('claim_id')
                ->unique()
                ->constrained('claims', 'id')
                ->cascadeOnDelete();

            $table->foreignUuid('payment_method_id')
                ->constrained('payment_methods', 'id')
                ->restrictOnDelete();

            // Check Number, GCash Reference No., etc.
            $table->string('reference_number')
                ->nullable();

            $table->decimal('amount', 12, 2);

            // Treasurer who released the payment
            $table->foreignUuid('released_by')
                ->constrained('users', 'user_id')
                ->restrictOnDelete();

            $table->timestamp('released_at');

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('released_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_payments');
    }
};