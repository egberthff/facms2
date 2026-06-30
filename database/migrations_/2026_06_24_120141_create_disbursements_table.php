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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->uuid('disbursement_id')->primary();
            $table->foreignUuid('claim_id')->nullable(false)->constrained('claims', 'claim_id');
            $table->string('disbursement_reference', 255)->nullable(false);
            $table->decimal('amount_disbursed', 15, 2)->nullable(false);
            $table->date('disbursement_date')->nullable(false);
            $table->foreignUuid('released_by')->nullable()->constrained('users', 'user_id');
            $table->longText('remarks')->nullable();
            $table->timestamps();

            $table->index(['disbursement_id']);
            $table->index(['disbursement_id', 'disbursement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
