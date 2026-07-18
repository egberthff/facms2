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
        Schema::create('treasury_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // 1. Establish the campus ownership
            $table->foreignUuid('campus_id')
                ->constrained('campuses') 
                ->cascadeOnDelete();
                
            $table->nullableUuidMorphs('sourceable'); 
            
            $table->string('reference_number')->unique();
            $table->text('description');                  
            
            $table->enum('type', ['Credit', 'Debit']);    
            $table->decimal('amount', 12, 2);
            
            // 2. Critical Change: Running balance must be calculated per campus!
            $table->decimal('running_balance', 12, 2);    
            
            $table->foreignUuid('entry_by')->constrained('users', 'user_id');
            $table->timestamps();
        
            // Indexing for high-performance campus filtering
            $table->index(['campus_id', 'created_at']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_ledger');
    }
};
