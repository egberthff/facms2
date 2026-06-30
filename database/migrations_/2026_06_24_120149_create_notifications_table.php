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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('notification_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'user_id');
            $table->string('title', 255)->nullable(false);
            $table->text('message')->nullable(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable()->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
