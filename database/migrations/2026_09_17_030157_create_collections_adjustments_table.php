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
        Schema::create('collection_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
             * Campus to which the adjustment belongs.
             */
            $table->foreignUuid('campus_id')
                ->constrained('campuses', 'id')
                ->cascadeOnDelete();

            /*
             * Month when the adjustment is actually applied.
             *
             * Example:
             * September 2025
             */
            $table->date('adjustment_month');

            /*
             * Month from which the adjustment originated.
             *
             * Example:
             * June 2025 overdeduction
             *
             * Nullable because not every adjustment needs a
             * previous/source month.
             */
            $table->date('reference_month')->nullable();

            /*
             * Type/category of adjustment.
             *
             * Examples:
             * - Overdeduction
             * - Underdeduction
             * - Correction
             * - Other
             */
            $table->string('adjustment_type', 50);

            /*
             * Signed adjustment amount.
             *
             * Positive = increase collection
             * Negative = decrease collection
             */
            $table->decimal('amount', 12, 2);

            /*
             * Explanation of the adjustment.
             */
            $table->text('description')->nullable();

            /*
             * User who created the adjustment.
             */
            $table->foreignUuid('created_by')
                ->constrained('users', 'user_id')
                ->restrictOnDelete();

            /*
             * Optional user who last updated the adjustment.
             */
            $table->foreignUuid('updated_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            /*
             * Frequently used when generating monthly reports.
             */
            $table->index([
                'campus_id',
                'adjustment_month',
            ]);

            $table->index('reference_month');
            $table->index('adjustment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_adjustments');
    }
};
