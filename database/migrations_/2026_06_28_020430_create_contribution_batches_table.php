<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

return new class extends Migration
{
    use HasUuids;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contribution_batches', function (Blueprint $table) {
            $table->uuid('contribution_batch_id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims', 'claim_id');
            $table->decimal('amount_per_member', 10, 2)->default(0.00);
            $table->integer('total_members');
            $table->decimal('totol_ammount', 10, 2);
            $table->timestamp('generated_at')->useCurrent();
            $table->foreignUuid('generated_by')->constrained('users', 'user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribution_batches');
    }
};
