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
        Schema::create('members', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->unique()
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->enum('gender', [
                'Male',
                'Female',
            ]);
            $table->date('birth_date')->nullable();
            $table->string('department');
            $table->string('position');
            $table->enum('employment_status', [
                'Permanent',
                'Probationary',
                'Contractual',
                'Part-time',
            ]);
            $table->enum('membership_status', [
                'Active',
                'Inactive',
            ])->default('Active');
            $table->string('contact_number', 20);
            $table->text('address');
            $table->string('gcash_number', 20)->nullable();
            $table->string('gcash_account_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('employee_number');
            $table->index('membership_status');
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};