<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claim_status_histories', function (Blueprint $table) {
            $table->uuid('history_id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims', 'claim_id');
            $table->enum('status', ['PENDING', 'PROCESSING', 'APPROVED', 'DISBURSED', 'REJECTED']);
            $table->longText('remarks')->nullable();
            $table->foreignUuid('changed_by')->nullable()->constrained('users', 'user_id');
            $table->timestamp('changed_at')->useCurrent();
            // $table->timestamps();

            $table->index(['claim_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_status_histories');
    }
};
