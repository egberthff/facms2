<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cna_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('member_id')->constrained('members', 'id')->cascadeOnDelete();
            
            $table->year('year')->comment('Year the Share is contributed'); 
            
            $table->decimal('due_amount', 10, 2)->comment('Amount due for the share'); 

            $table->decimal('paid_amount', 10, 2)->comment('Actual amount paid'); 

            $table->boolean('is_paid')->default(false); 
            
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();

            $table->timestamps();

            $table->unique(['member_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cna_shares');
    }
};
