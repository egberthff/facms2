<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repurposes the dormant collection summary tables as saved Campus Cash Flow periods:
 * a period carries its forwarded balance and totals, items are its "Add Funds" / "Less" lines.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_summary_periods', function (Blueprint $table) {
            $table->string('report_type')->default('cash_flow')->after('campus_id');
            $table->date('period_from')->nullable()->after('period_label');
            $table->date('period_to')->nullable()->after('period_from');
            $table->string('forwarded_label')->nullable()->after('period_to');
            $table->decimal('forwarded_balance', 12, 2)->default(0.00)->after('forwarded_label');
            $table->decimal('total_funds', 12, 2)->default(0.00)->after('forwarded_balance');
            $table->decimal('total_disbursements', 12, 2)->default(0.00)->after('total_funds');
            $table->decimal('cash_on_hand', 12, 2)->default(0.00)->after('total_disbursements');
            // Signatory names are frozen when the period is finalized; drafts print the current settings
            $table->string('prepared_by_name')->nullable()->after('approved_by');
            $table->string('checked_by_name')->nullable()->after('prepared_by_name');
            $table->timestamp('finalized_at')->nullable()->after('status');

            $table->index(['campus_id', 'report_type', 'period_to']);
        });

        Schema::table('collection_summary_items', function (Blueprint $table) {
            $table->date('entry_date')->nullable()->after('amount');
            $table->string('group_label')->nullable()->after('entry_date');
        });
    }

    public function down(): void
    {
        Schema::table('collection_summary_items', function (Blueprint $table) {
            $table->dropColumn(['entry_date', 'group_label']);
        });

        Schema::table('collection_summary_periods', function (Blueprint $table) {
            $table->dropIndex(['campus_id', 'report_type', 'period_to']);
            $table->dropColumn([
                'report_type',
                'period_from',
                'period_to',
                'forwarded_label',
                'forwarded_balance',
                'total_funds',
                'total_disbursements',
                'cash_on_hand',
                'prepared_by_name',
                'checked_by_name',
                'finalized_at',
            ]);
        });
    }
};
