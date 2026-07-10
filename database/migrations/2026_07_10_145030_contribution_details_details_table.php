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
        Schema::create('contribution_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contribution_batch_id')->constrained('contribution_batches', 'id');
            $table->foreignUuid('member_id')->constrained('members', 'id');
            $table->decimal('amount', 10, 2);
            $table->string('status')->nullable();
            $table->date('paid_at')->nullable();
            $table->timestamps();

            $table->index(['member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribution_details');
    }
};
