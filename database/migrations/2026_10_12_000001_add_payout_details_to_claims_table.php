<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Where the member wants the claim sent (GCash number or bank account)
            $table->string('payout_bank_name')->nullable()->after('payment_method_id');
            $table->string('payout_account_name')->nullable()->after('payout_bank_name');
            $table->string('payout_account_number', 30)->nullable()->after('payout_account_name');
            $table->string('payout_branch')->nullable()->after('payout_account_number');
            // Uploaded GCash / QR Ph code admins can scan to send the payout
            $table->string('payout_qr_path')->nullable()->after('payout_branch');
            $table->text('payout_qr_payload')->nullable()->after('payout_qr_path');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn([
                'payout_bank_name',
                'payout_account_name',
                'payout_account_number',
                'payout_branch',
                'payout_qr_path',
                'payout_qr_payload',
            ]);
        });
    }
};
