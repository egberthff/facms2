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
        Schema::create('claim_payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims', 'claim_id');
            $table->foreignUuid('payment_method_id')->constrained('payment_methods','payment_method_id');
            $table->text('reference_number')->nullable(false);
            // Dynamic payload for GCash, Bank, Check, or any future provider
            $table->json('payment_details'); 
            $table->string('remarks', 255)->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_payments');
    }
};
