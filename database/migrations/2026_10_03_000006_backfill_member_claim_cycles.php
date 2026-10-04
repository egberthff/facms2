<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reconstruct member cycles chronologically from existing claims.
        Artisan::call('claims:rebuild-member-cycles');
    }

    public function down(): void
    {
        DB::table('claims')->update(['member_claim_cycle_id' => null]);
        DB::table('member_claim_cycles')->delete();
    }
};
