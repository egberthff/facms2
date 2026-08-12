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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('setting_key');
            $table->text('setting_value');
            $table->string('cycle_label')->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('campus_id')
                ->constrained('campuses', 'id')
                ->cascadeOnDelete(); 
            $table->timestamps();


            $table->unique(['setting_key', 'campus_id', 'cycle_label'], 'system_settings_key_campus_cycle_unique');
            // Index for fast lookups when querying settings by campus
            $table->index('campus_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};