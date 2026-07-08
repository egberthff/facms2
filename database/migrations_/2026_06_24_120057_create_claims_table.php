<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
return new class extends Migration
{
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('claims', function (Blueprint $table) {
            // New Primary Key (Using standard id string/integer structure as per new definition)
            $table->uuid('claim_id')->primary(); 
            $table->string('claim_number', 100)->unique()->nullable(false);
            
            // Foreign Keys
            $table->foreignUuid('member_id')->constrained('members', 'member_id');
            $table->foreignUuid('claim_type_id')->constrained('claim_types', 'claim_type_id');
            $table->foreignUuid('benefit_id')->constrained('member_benefits', 'member_benefit_id'); // Added from new schema
            $table->foreignUuid('payment_method_id')->nullable()->constrained('payment_methods', 'payment_methods_id'); // Added from new schema

            // Amounts
            $table->decimal('requested_amount', 15, 2)->nullable(false); // Renamed from amount_requested
            $table->decimal('approved_amount', 15, 2)->nullable()->default(0.00); // Added from new schema
            
            // Status and Text Fields
            $table->enum('status', ['DRAFT', 'PENDING', 'PROCESSING', 'APPROVED', 'DISBURSED', 'REJECTED'])->default('DRAFT'); // Renamed from current_status
            $table->longText('remarks')->nullable(); // Replaces longText description/rejection_reason

            // User / Audit Tracking
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users', 'user_id'); // Added from new schema
            $table->foreignUuid('approved_by')->nullable()->constrained('users', 'user_id'); // Kept from old schema
            
            // Dates & Timestamps
            $table->dateTime('submitted_at')->nullable(); // Changed from date to dateTime
            $table->dateTime('reviewed_at')->nullable(); // Added from new schema
            $table->dateTime('approved_at')->nullable(); // Changed from date to dateTime
            $table->dateTime('disbursed_at')->nullable(); // Added from new schema
            
            $table->timestamps(); // Handles created_at and updated_at
            $table->softDeletes(); // Handles deleted_at

            // Optimised Performance Indexes
            $table->index(['claim_number']); 
            $table->index(['status']);
            $table->index(['member_id']);
            $table->index(['submitted_at']);
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
