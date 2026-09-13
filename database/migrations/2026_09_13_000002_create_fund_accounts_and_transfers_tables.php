<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('fund_accounts')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'FFA Global Fund',
            'code' => 'FFA',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fund_account_id')->constrained('fund_accounts');
            $table->enum('type', ['campus_remittance', 'ffa_disbursement']);
            $table->foreignUuid('source_campus_id')->nullable()->constrained('campuses');
            $table->foreignUuid('destination_campus_id')->nullable()->constrained('campuses');
            $table->string('reference_number')->unique();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('created_by')->constrained('users', 'user_id');
            $table->foreignUuid('approved_by')->nullable()->references('user_id')->on('users');
            $table->timestamp('approved_at')->nullable();
            $table->uuid('source_ledger_id')->nullable();
            $table->uuid('destination_ledger_id')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['destination_campus_id', 'created_at']);
        });

        Schema::table('treasury_ledger', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->foreignUuid('campus_id')->nullable()->change();
            $table->foreignUuid('fund_account_id')->nullable()->after('campus_id')->constrained('fund_accounts');
            $table->foreignUuid('transfer_id')->nullable()->after('sourceable_id')->constrained('fund_transfers');
            $table->index(['fund_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('treasury_ledger', function (Blueprint $table) {
            $table->dropForeign(['transfer_id']);
            $table->dropForeign(['fund_account_id']);
            $table->dropColumn(['transfer_id', 'fund_account_id']);
            $table->foreignUuid('campus_id')->nullable(false)->change();
            $table->foreign('campus_id')->references('id')->on('campuses')->cascadeOnDelete();
        });

        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('fund_accounts');
    }
};
