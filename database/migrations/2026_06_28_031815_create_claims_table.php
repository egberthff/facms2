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
        Schema::create('claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Human-readable reference number
            $table->string('claim_number')->unique();
            // Relationships
            $table->foreignUuid('member_id')
                ->constrained('members', 'id')
                ->cascadeOnDelete();
            $table->foreignUuid('claim_type_id')
                ->constrained('claim_types', 'id')
                ->restrictOnDelete();
            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods', 'id')
                ->nullOnDelete();
            // Financial Information
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)
                ->nullable();
            $table->decimal('annual_limit', 12, 2);
            // $table->decimal('paid_amount', 12, 2)
            //     ->default(0);
            // Workflow
            $table->string('status')->default('Pending');
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            // Dates
            $table->timestamp('submitted_at');
            $table->timestamp('approved_at')
                ->nullable();
            $table->timestamp('disbursed_at')
                ->nullable();
            // Reviewer
            $table->foreignUuid('reviewed_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('member_id');
            $table->index('claim_type_id');
            $table->index('status');
            $table->index('submitted_at');
            $table->index('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};