<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Services\MemberClaimCycleService;
use Illuminate\Console\Command;

class RebuildMemberClaimCycles extends Command
{
    protected $signature = 'claims:rebuild-member-cycles
                            {--member=* : Limit the rebuild to these member ids}';

    protected $description = 'Reconstruct per-member claim cycles chronologically from existing claims';

    public function handle(MemberClaimCycleService $cycles): int
    {
        $memberIds = $this->option('member');
        $rebuilt = 0;
        $created = 0;

        Member::withoutGlobalScopes()
            ->withTrashed()
            ->when($memberIds, fn ($query) => $query->whereIn('id', $memberIds))
            ->whereHas('claims')
            ->orderBy('id')
            ->each(function (Member $member) use ($cycles, &$rebuilt, &$created) {
                $created += $cycles->rebuildForMember($member)->count();
                $rebuilt++;
            });

        $this->info("Rebuilt claim cycles for {$rebuilt} member(s); {$created} cycle(s) created.");

        return self::SUCCESS;
    }
}
