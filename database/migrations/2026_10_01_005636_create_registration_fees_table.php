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
        Schema::create('membership_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_code')->unique();
            $table->foreignUuid('member_id')
                ->constrained('members', 'id')
                ->cascadeOnDelete();
    
            $table->decimal('amount', 10, 2)
                ->comment('Membership registration fee amount');
    
            $table->string('status',50)->default('Pending');
    
            $table->date('paid_at')->nullable();
    
            $table->string('reference_number')
                ->nullable()
                ->unique()
                ->comment('Official receipt or payment reference number');
    
            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods', 'id')
                ->nullOnDelete();
    
            $table->text('remarks')->nullable();
    
            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
    
            $table->timestamps();
            $table->softDeletes();
    
            $table->index(['member_id', 'status']);
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_fees');
    }
};
