<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mark every pre-existing (officer-created) account as verified so that
     * enforcing the `verified` middleware does not lock them out.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)')]);
    }

    /**
     * Irreversible data backfill: we cannot tell which accounts were unverified before.
     */
    public function down(): void
    {
        //
    }
};
