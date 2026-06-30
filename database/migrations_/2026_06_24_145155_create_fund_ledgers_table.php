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
       Schema::create('funding_events', function (Blueprint $table) {
        $table->uuid('funding_event_id')->primary();

        // 2. Type tells us exactly what this event represents
        // 'ALLOCATION' = Adding regular budget
        // 'DISBURSEMENT' = Spending budget
        // 'ADJUSTMENT' = Correction / Manual fix
        $table->enum('event_type', ['ALLOCATION', 'DISBURSEMENT', 'ADJUSTMENT'])->nullable(false);

        $table->decimal('amount', 15, 2)->nullable(false); 
        $table->longText('remarks')->nullable();

        $table->foreignUuid('processed_by')->constrained('users', 'user_id');
        $table->timestamp('processed_at')->useCurrent();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funding_events');
    }
};
