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
        Schema::create('claim_types', function (Blueprint $table) {
            $table->uuid('claim_type_id')->primary();
            $table->string('claim_name', 250)->nullable(false);
            $table->string('description', 250)->nullable(false);
            $table->decimal('maximum_amount', 10, 2)->default('0.00');
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('requires_hospital_name')->default(false);
            $table->boolean('requires_documents')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_types');
    }
};
