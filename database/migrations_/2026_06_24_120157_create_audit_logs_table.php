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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('audit_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'user_id');
            $table->string('module_name', 100);
            $table->string('action_type', 50);
            $table->string('table_name', 255);
            $table->bigInteger('record_id')->unsigned();
            $table->json('old_values');
            $table->json('new_values');
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
