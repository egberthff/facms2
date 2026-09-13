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
        Schema::create('collection_summary_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campus_id')->constrained('campuses', 'id');
            $table->string('period_label');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('prepared_by')->nullable()->references('user_id')->on('users');
            $table->foreignUuid('approved_by')->nullable()->references('user_id')->on('users');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('collection_summary_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('summary_id')->constrained('collection_summary_periods', 'id')->cascadeOnDelete();
            $table->string('section')->default('collections');
            $table->string('label');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->boolean('is_manual')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source_type')->nullable();
            $table->string('source_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_summary_items');
        Schema::dropIfExists('collection_summary_periods');
    }
};
