<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

return new class extends Migration
{
    use SoftDeletes;
    use HasUuids;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('member_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'user_id');
            $table->string('employee_number', 50)->unique();
            $table->string('first_name', 100)->nullable(false);
            $table->string('middle_name', 100)->nullable(true);
            $table->string('last_name', 100)->nullable(false);
            $table->string('suffix',10)->nullable();
            $table->enum('gender', ['MALE', 'FEMALE']);
            $table->date('birthdate');
            $table->string('department', 150);
            $table->string('position_title', 150);
            $table->dateTime('date_hired')->nullable(false);
            $table->enum('employment_status', ['ACTIVE', 'INACTIVE', 'SUSPENDED', 'RETIRED'])->default('ACTIVE');
            $table->enum('membership_status', ['ACTIVE', 'INACTIVE', 'SUSPENDED']);
            $table->string('email',255)->nullable();
            $table->string('contact_number', 15)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('gcash_number', 15)->nullable();
            $table->string('gcash_account_name', 255)->nullable();
            $table->date('membership_start');
            $table->date('membership_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_number']);
            $table->index(['member_id', 'benefit_year']);
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
